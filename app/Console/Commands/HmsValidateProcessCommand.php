<?php

namespace App\Console\Commands;

use App\Models\Bed;
use App\Models\CaseReference;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Encounter\Encounter;
use App\Models\IpdPatient;
use App\Models\IpdPatientBed;
use App\Models\OpdPatient;
use App\Models\Patient;
use App\Models\ServiceCharge\ServiceChargePosting;
use App\Models\User;
use App\Services\Billing\BillingService;
use App\Services\Insurance\ClaimBuilderService;
use App\Services\ServiceCharge\ServiceChargeEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

/**
 * `php artisan hms:validate-process`
 *
 * Runs the full patient journey end-to-end and reports every step's status.
 * Use this after migrations / seed runs / config changes to confirm the
 * service-charge engine, billing engine, observers and bed-day accrual all
 * still wire together correctly.
 */
class HmsValidateProcessCommand extends Command
{
    protected $signature = 'hms:validate-process {--admin=admin@demo-hms.local}';
    protected $description = 'Run an end-to-end HMS process validation (patient → OPD → bill → IPD → bed → claim).';

    public function handle(BillingService $billing, ServiceChargeEngine $engine, ClaimBuilderService $claimBuilder): int
    {
        $admin = User::where('email', $this->option('admin'))->first();
        if (! $admin) {
            $this->error('Admin user not found: ' . $this->option('admin'));
            return self::FAILURE;
        }
        Auth::login($admin);

        $this->info('=== HMS E2E PROCESS VALIDATION ===');

        $pass = 0;
        $fail = 0;

        $step = function (string $label, \Closure $body) use (&$pass, &$fail) {
            try {
                $result = $body();
                $this->info('  ✓ ' . $label . ($result ? ' — ' . $result : ''));
                $pass++;
            } catch (\Throwable $e) {
                $this->error('  ✗ ' . $label . ' — ' . $e->getMessage());
                $fail++;
            }
        };

        $context = [];

        $step('Step 1: Register patient', function () use (&$context, $admin) {
            $context['patient'] = Patient::create([
                'patient_name' => 'E2E Validator Patient',
                'mobileno' => '0179' . random_int(1000000, 9999999),
                'gender' => 'Male',
                'dob' => '1985-01-01',
                'blood_group' => 'A+',
                'org_id' => $admin->current_organization_id,
                'branch_id' => $admin->current_branch_id,
            ]);
            return 'MRN=' . $context['patient']->mrn;
        });

        $step('Step 2: Create OPD visit', function () use (&$context) {
            $context['opd'] = OpdPatient::create([
                'patient_id' => $context['patient']->id,
                'doctor_id' => Doctor::first()->id,
                'department_id' => Department::first()->id,
                'date' => now(),
                'visit_date' => now(),
                'visit_type' => 'new',
                'status' => 'in_consultation',
                'chief_complaint' => 'Fever',
            ]);
            return 'OPD id=' . $context['opd']->id;
        });

        $step('Step 3: Observer auto-created encounter', function () use (&$context) {
            $context['encounter'] = Encounter::find($context['opd']->encounter_id);
            if (! $context['encounter']) {
                throw new \RuntimeException('Encounter not created');
            }
            return 'Encounter ' . $context['encounter']->encounter_no;
        });

        $step('Step 4: Observer auto-posted consultation service charge', function () use (&$context) {
            $postings = ServiceChargePosting::where('encounter_id', $context['encounter']->id)->get();
            if ($postings->isEmpty()) {
                throw new \RuntimeException('No service charges posted');
            }
            return $postings->count() . ' postings, total ' . number_format($postings->sum('net_amount'), 2);
        });

        $step('Step 5: Assemble bill from encounter', function () use (&$context, $billing) {
            $context['bill'] = $billing->assembleFromEncounter($context['encounter']);
            return $context['bill']->bill_no . ' grand=' . $context['bill']->grand_total;
        });

        $step('Step 6: Collect payment', function () use (&$context, $billing) {
            $payment = $billing->collectPayment($context['bill'], [
                'amount' => $context['bill']->grand_total,
                'method' => 'cash',
            ]);
            return 'Receipt ' . $payment->receipt_no;
        });

        $step('Step 7: Finalize bill', function () use (&$context, $billing) {
            $context['bill'] = $billing->finalize($context['bill']->fresh());
            if ($context['bill']->status !== 'paid' && $context['bill']->status !== 'final') {
                throw new \RuntimeException('Unexpected status: ' . $context['bill']->status);
            }
            return 'status=' . $context['bill']->status;
        });

        $step('Step 8: Create IPD admission (observer creates IPD encounter)', function () use (&$context) {
            $context['ipd'] = IpdPatient::create([
                'patient_id' => $context['patient']->id,
                'doctor_id' => Doctor::first()->id,
                'department_id' => Department::first()->id,
                'admission_date' => now(),
                'admission_type' => 'elective',
                'status' => 'admitted',
                'patient_history' => 'E2E validator',
            ]);
            if (! $context['ipd']->encounter_id) {
                throw new \RuntimeException('IPD encounter not created');
            }
            return $context['ipd']->ipd_no . ' encounter=' . $context['ipd']->encounter_id;
        });

        $step('Step 9: Bed allocation + observer accrues bed-day charge on release', function () use (&$context) {
            $case = CaseReference::create([
                'patient_id' => $context['ipd']->patient_id,
                'reference_type' => 'IPD',
            ]);
            $alloc = IpdPatientBed::create([
                'case_id' => $case->id,
                'ipd_patient_id' => $context['ipd']->id,
                'bed_id' => Bed::first()->id,
                'allocation_type' => 'ward',
                'from' => now()->subDays(2),
                'status' => 'active',
            ]);
            $alloc->update(['to' => now(), 'status' => 'released']);
            $bedPostings = ServiceChargePosting::where('encounter_id', $context['ipd']->encounter_id)
                ->where('trigger_event', 'ipd.bed.accrual')
                ->get();
            if ($bedPostings->isEmpty()) {
                throw new \RuntimeException('Bed accrual not posted');
            }
            return $bedPostings->count() . ' bed-day postings, total ' . number_format($bedPostings->sum('net_amount'), 2);
        });

        $step('Step 10: Build claim from finalized OPD bill against patient policy', function () use (&$context, $claimBuilder) {
            // Patient must have an insurance policy; create a stub for the test.
            $policy = \App\Models\Insurance\InsurancePolicy::firstOrCreate(
                ['patient_id' => $context['patient']->id, 'policy_no' => 'POL-E2E-' . $context['patient']->id],
                [
                    'payer_id' => \App\Models\Insurance\Payer::where('code', 'INS1')->value('id'),
                    'plan_name' => 'E2E Test Plan',
                    'valid_from' => now()->subMonth(),
                    'valid_to' => now()->addYear(),
                    'coverage_limit' => 100000,
                    'copay_percent' => 10,
                    'status' => 'active',
                ]
            );
            $claim = $claimBuilder->buildFromBill($context['bill'], $policy);
            return $claim->claim_no . ' amount=' . $claim->claim_amount . ' copay=' . $claim->patient_copay;
        });

        // ─── EXTENDED IPD DISCHARGE FLOW ───

        $step('Step 11: Post extra IPD charges (specialist consult + lab + nursing)', function () use (&$context, $engine) {
            $posted = 0;
            foreach (['CONSULT_SPECIALIST', 'LAB_CBC', 'NURSING_DAILY'] as $code) {
                if (\App\Models\ServiceCharge\ServiceCatalog::where('code', $code)->where('is_active', true)->exists()) {
                    $engine->post([
                        'service_code' => $code,
                        'encounter' => $context['ipd']->encounter_id,
                        'trigger_event' => 'ipd.manual.charge',
                        'quantity' => 1,
                        'reason' => 'E2E extended IPD charge',
                    ]);
                    $posted++;
                }
            }
            $total = \App\Models\ServiceCharge\ServiceChargePosting::where('encounter_id', $context['ipd']->encounter_id)->count();
            return "$posted new charges, $total total postings on IPD encounter";
        });

        $step('Step 12: Assemble IPD bill (consolidates all postings)', function () use (&$context, $billing) {
            $ipdEnc = \App\Models\Encounter\Encounter::find($context['ipd']->encounter_id);
            $context['ipdBill'] = $billing->assembleFromEncounter($ipdEnc);
            return $context['ipdBill']->bill_no . ' grand=' . $context['ipdBill']->grand_total
                . ' items=' . $context['ipdBill']->items->count();
        });

        $step('Step 13: Collect 50% advance, then settle remainder', function () use (&$context, $billing) {
            $advance = round((float) $context['ipdBill']->grand_total * 0.5, 2);
            $billing->collectPayment($context['ipdBill'], ['amount' => $advance, 'method' => 'cash']);
            $remaining = $context['ipdBill']->fresh()->balance_due;
            if ($remaining > 0.01) {
                $billing->collectPayment($context['ipdBill']->fresh(), ['amount' => $remaining, 'method' => 'card']);
            }
            return 'advance=' . $advance . ' + settle=' . $remaining;
        });

        $step('Step 14: Finalize IPD bill', function () use (&$context, $billing) {
            $bill = $billing->finalize($context['ipdBill']->fresh());
            if (! in_array($bill->status, ['paid', 'final'], true)) {
                throw new \RuntimeException('Unexpected status: ' . $bill->status);
            }
            return 'status=' . $bill->status . ' paid=' . $bill->paid_total . ' balance=' . $bill->balance_due;
        });

        $step('Step 15: Discharge patient — IpdPatientObserver closes the encounter', function () use (&$context) {
            $context['ipd']->update([
                'discharge_date' => now(),
                'status' => 'discharged',
            ]);
            $enc = \App\Models\Encounter\Encounter::find($context['ipd']->encounter_id);
            if ($enc->status !== 'closed') {
                throw new \RuntimeException('Encounter not closed. Current: ' . $enc->status);
            }
            return 'discharge_date=' . $context['ipd']->discharge_date->toDateString()
                . ' encounter status=' . $enc->status
                . ' closed_at=' . $enc->closed_at?->toDateTimeString();
        });

        $this->newLine();
        $this->info("Result: {$pass} passed, {$fail} failed");
        return $fail === 0 ? self::SUCCESS : self::FAILURE;
    }
}
