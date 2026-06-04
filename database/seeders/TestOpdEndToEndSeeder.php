<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Department;
use App\Models\OpdPatient;
use App\Models\Patient;
use App\Models\PatientCharge;
use App\Models\CaseReference;
use App\Models\LabInvestigation;
use App\Models\LabInvestigationCategory;
use App\Models\LabInvestigationType;
use App\Models\Ipd\LabInvestigationOrder;
use App\Models\Ipd\LabInvestigationOrderRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * One-shot test seeder that exercises the full Front-Desk → OPD → detail
 * tabs chain, then reports which relations are populated and which aren't.
 *
 * Idempotent — uses the existing token_no pattern so re-running creates new
 * OPD rows for today rather than colliding.
 */
class TestOpdEndToEndSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('▶ Front-Desk → OPD end-to-end test');

        // 0. Pick a real doctor + department + patient (or create one)
        $doctor = Doctor::with('department')->first();
        $dept   = $doctor?->department ?? Department::first();

        if (! $doctor || ! $dept) {
            $this->command->error('  ✗ No doctor or department exists. Run DoctorSeeder first.');
            return;
        }

        // 1. New patient (front-desk NEW_PATIENT path)
        $patient = Patient::create([
            'patient_name'          => 'Test Patient — ' . now()->format('Y-m-d H:i:s'),
            'mobileno'              => '+880-1700-' . rand(100000, 999999),
            'gender'                => 'Male',
            'dob'                   => '1990-01-15',
            'blood_group'           => 'O+',
            'identification_number' => 'NID-' . rand(10000000, 99999999),
            'address'               => '123 Test Street, Dhaka',
            'guardian_name'         => 'Mr Test Guardian',
            'emergency_contact'     => '+880-1700-' . rand(100000, 999999),
            'created_by'            => 1,
        ]);
        $this->command->line("  • Patient #{$patient->id} {$patient->patient_name} (MRN {$patient->mrn})");

        // 2. Create a case reference (mirrors CaseReferenceService->createCase)
        //    case_references is a thin parent/child linkage table — no patient_id.
        $case = CaseReference::create([]);
        $this->command->line("  • CaseReference #{$case->id}");

        // 3. Generate token_no (matches FrontDeskRegistrationController logic)
        $deptCode = $dept->code ?? strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $dept->name ?: 'GEN'), 0, 3));
        $deptCode = str_pad($deptCode ?: 'GEN', 3, 'X');
        $seq      = OpdPatient::whereDate('date', now()->format('Y-m-d'))
                        ->where('department_id', $dept->id)->count() + 1;
        $tokenNo  = now()->format('Ymd') . '-' . $deptCode . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT);

        $opd = OpdPatient::create([
            'case_id'        => $case->id,
            'patient_id'     => $patient->id,
            'doctor_id'      => $doctor->id,
            'department_id'  => $dept->id,
            'date'           => now()->toDateString(),
            'visit_type'     => 'new',
            'token_no'       => $tokenNo,
            'remarks'        => 'End-to-end test registration',
            'status'         => 'Registered',
            'priority'       => 'Normal',
        ]);
        $this->command->line("  • OpdPatient #{$opd->id} token {$opd->token_no}");

        // 4. Consultation charge
        PatientCharge::create([
            'case_id'       => $case->id,
            'opd_id'        => $opd->id,
            'charge_module' => 'opd',
            'doctor_id'     => $doctor->id,
            'department_id' => $dept->id,
            'charge_item'   => 'Consultant Doctor Fee',
            'unit_price'    => 800,
            'quantity'      => 1,
            'amount'        => 800,
            'tax'           => 0,
            'net_amount'    => 800,
            'date'          => now()->toDateString(),
            'status'        => 'pending',
            'is_paid'       => false,
            'created_by'    => 1,
        ]);
        $this->command->line("  • PatientCharge: BDT 800 consultation fee");

        // 5. Vital check — fd_vital_checks
        if (DB::getSchemaBuilder()->hasTable('fd_vital_checks')) {
            try {
                DB::table('fd_vital_checks')->insert([
                    'patient_id'       => $patient->id,
                    'patient_type'     => 'OPD',
                    'opd_patient_id'   => $opd->id,
                    'patient_token'    => $opd->token_no,
                    'patient_name'     => $patient->patient_name,
                    'gender'           => $patient->gender,
                    'age'              => 36,
                    'weight'           => 72.5,
                    'height'           => 175,
                    'blood_pressure'   => '120/80',
                    'temperature'      => 98.6,
                    'heart_rate'       => 76,
                    'respiratory_rate' => 16,
                    'spo2'             => 98,
                    'checked_by'       => 1,
                    'checked_at'       => now(),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
                $this->command->line("  • Vital check recorded");
            } catch (\Throwable $e) {
                $this->command->warn("  ⚠ vital check skipped: " . $e->getMessage());
            }
        }

        // 6a. Consultation note (SOAP)
        try {
            DB::table('consultation_notes')->insert([
                'opd_patient_id'    => $opd->id,
                'subjective'        => 'Fever for 3 days, body ache, headache. No vomiting.',
                'objective'         => 'Temp 99.8°F. BP 120/80. Throat congested. Chest clear.',
                'assessment'        => 'Suspected viral fever, awaiting CBC + dengue NS1.',
                'plan'              => 'Paracetamol 500mg TDS x 5 days. Plenty of fluids. Review in 48h.',
                'icd10_code'        => 'R50.9',
                'icd10_description' => 'Fever, unspecified',
                'status'            => 'draft',
                'created_by'        => 1,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
            $this->command->line('  • Consultation note (SOAP) recorded');
        } catch (\Throwable $e) {
            $this->command->warn('  ⚠ SOAP skipped: ' . $e->getMessage());
        }

        // 6b. Prescription with symptoms + medicines + lab tests
        // Seed a couple of symptoms if none exist yet so the prescription has links.
        if (DB::table('symptoms')->count() === 0) {
            foreach (['Fever', 'Headache', 'Body Ache', 'Sore Throat', 'Cough'] as $s) {
                DB::table('symptoms')->insertOrIgnore([
                    'name' => $s, 'status' => 1, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        $rxNo = 'RX-' . now()->format('Ymd') . '-' . $opd->id;
        $rxId = DB::table('prescriptions')->insertGetId([
            'prescription_no'     => $rxNo,
            'opd_patient_id'      => $opd->id,
            'patient_id'          => $patient->id,
            'doctor_id'           => $doctor->id,
            'date'                => now()->toDateString(),
            'findings'            => 'Acute viral syndrome — fever, body ache, headache.',
            'icd10_code'          => 'R50.9',
            'icd10_description'   => 'Fever, unspecified',
            'advice'              => 'Plenty of fluids, paracetamol PRN, rest. Avoid NSAIDs until dengue ruled out.',
            'next_visit'          => now()->addDays(2)->toDateString(),
            'follow_up_note'      => 'Review with CBC report.',
            'generated_by'        => 1,
            'type'                => 'opd',
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        // Link 2 symptoms
        $sympIds = DB::table('symptoms')->limit(2)->pluck('id');
        foreach ($sympIds as $sid) {
            DB::table('presciption_symptoms')->insert([
                'prescription_id' => $rxId, 'symptom_id' => $sid,
                'note' => 'Reported by patient',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // Link 2 medicines
        $medIds = DB::table('medicines')->limit(2)->pluck('id');
        foreach ($medIds as $i => $mid) {
            DB::table('presciption_medicines')->insert([
                'prescription_id' => $rxId,
                'medicine_id'     => $mid,
                'dosage'          => $i === 0 ? '500 mg' : '250 mg',
                'frequency'       => $i === 0 ? 'TDS (1-1-1)' : 'BD (1-0-1)',
                'duration'        => '5 days',
                'note'            => 'After meal',
                'created_at'      => now(), 'updated_at' => now(),
            ]);
        }

        // Link 1 lab investigation
        $labId = DB::table('lab_investigations')->limit(1)->value('id');
        if ($labId) {
            DB::table('presciption_lab_investigations')->insert([
                'prescription_id'      => $rxId,
                'lab_investigation_id' => $labId,
                'note'                 => 'Send for fasting sample',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->command->line("  • Prescription {$rxNo} with 2 symptoms, 2 medicines, 1 lab test");

        // 6c. Pharmacy dispense linked to the prescription
        try {
            $dispenseNo = 'DSP-' . now()->format('Ymd') . '-' . $opd->id;
            $dispId = DB::table('opd_dispenses')->insertGetId([
                'dispense_no'    => $dispenseNo,
                'opd_patient_id' => $opd->id,
                'prescription_id'=> $rxId,
                'patient_id'     => $patient->id,
                'pharmacist_id'  => 1,
                'drug_count'     => 2,
                'total_amount'   => 350,
                'payment_status' => 'paid',
                'status'         => 'dispensed',
                'note'           => 'All items dispensed from main pharmacy',
                'created_at'     => now(), 'updated_at' => now(),
            ]);
            foreach ($medIds as $i => $mid) {
                DB::table('opd_dispense_items')->insert([
                    'opd_dispense_id' => $dispId,
                    'medicine_id'     => $mid,
                    'dosage'          => $i === 0 ? '500 mg' : '250 mg',
                    'qty_required'    => $i === 0 ? 15 : 10,
                    'available_qty'   => $i === 0 ? 15 : 10,
                    'unit_price'      => $i === 0 ? 12.5 : 16.5,
                    'store'           => 'Main Pharmacy',
                    'created_at'      => now(), 'updated_at' => now(),
                ]);
            }
            $this->command->line("  • Pharmacy dispense {$dispenseNo} (2 items)");
        } catch (\Throwable $e) {
            $this->command->warn('  ⚠ dispense skipped: ' . $e->getMessage());
        }

        // 6d. OpdMedication (one-off administration record on top of the dispense)
        try {
            DB::table('opd_medications')->insert([
                'opd_patient_id' => $opd->id,
                'medicine_id'    => $medIds->first(),
                'datetime'       => now(),
                'dosage'         => '500 mg PO',
                'medicated_by'   => 1,
                'remarks'        => 'Loading dose in clinic',
                'created_at'     => now(), 'updated_at' => now(),
            ]);
            $this->command->line('  • OpdMedication administered in-clinic');
        } catch (\Throwable $e) {
            $this->command->warn('  ⚠ opd_medication skipped: ' . $e->getMessage());
        }

        // 6e. Recheckup — child OPD visit pointing back to this one
        try {
            $rechSeq   = $seq + 1;
            $rechToken = now()->format('Ymd') . '-' . $deptCode . '-' . str_pad((string) $rechSeq, 3, '0', STR_PAD_LEFT);
            OpdPatient::create([
                'parent_visit_id' => $opd->id,
                'case_id'         => $case->id,
                'patient_id'      => $patient->id,
                'doctor_id'       => $doctor->id,
                'department_id'   => $dept->id,
                'date'            => now()->addDays(2)->toDateString(),
                'visit_type'      => 'follow_up',
                'token_no'        => $rechToken . '-R',
                'remarks'         => 'Scheduled follow-up for CBC review',
                'status'          => 'Scheduled',
                'priority'        => 'Normal',
            ]);
            $this->command->line('  • Recheckup visit scheduled (' . $rechToken . '-R)');
        } catch (\Throwable $e) {
            $this->command->warn('  ⚠ recheckup skipped: ' . $e->getMessage());
        }

        // 6f. Patient document
        try {
            DB::table('opd_patient_documents')->insert([
                'opd_patient_id' => $opd->id,
                'title'          => 'Lab Requisition Slip',
                'file'           => 'docs/test-' . $opd->id . '.pdf',
                'remarks'        => 'Auto-generated test document',
                'created_at'     => now(), 'updated_at' => now(),
            ]);
            $this->command->line('  • Patient document attached');
        } catch (\Throwable $e) {
            $this->command->warn('  ⚠ document skipped: ' . $e->getMessage());
        }

        // 6g. Patient history (medical history tab)
        try {
            DB::table('patient_histories')->insert([
                'patient_id'   => $patient->id,
                'history_type' => 'medical',
                'description'  => 'Hypertension x 5 years. On Amlodipine 5mg OD, well-controlled.',
                'recorded_by'  => 1,
                'created_at'   => now(), 'updated_at' => now(),
            ]);
            $this->command->line('  • Patient history (Hypertension) recorded');
        } catch (\Throwable $e) {
            $this->command->warn('  ⚠ patient_history skipped: ' . $e->getMessage());
        }

        // 6h. Transaction (payment for consultation fee)
        try {
            DB::table('transactions')->insert([
                'patient_id'      => $patient->id,
                'opd_patient_id'  => $opd->id,
                'case_id'         => $case->id,
                'invoice_no'      => 'INV-OPD-' . now()->format('Ymd') . '-' . $opd->id,
                'amount'          => 800,
                'net_amount'      => 800,
                'vat'             => 0,
                'tax'             => 0,
                'discount'        => 0,
                'type'            => 'opd_consultation',
                'section'         => 'OPD',
                'payment_via'     => 'cash',
                'payment_date'    => now(),
                'status'          => 'completed',
                'received_by'     => 'Front Desk',
                'notes'           => 'Consultation fee paid at front desk',
                'created_at'      => now(), 'updated_at' => now(),
            ]);
            $this->command->line('  • Payment transaction recorded (BDT 800 cash)');
        } catch (\Throwable $e) {
            $this->command->warn('  ⚠ transaction skipped: ' . $e->getMessage());
        }

        // 7. Pathology lab order tied to this OPD encounter
        $pathType = LabInvestigationType::where('name', 'Pathology')->first();
        if ($pathType) {
            $inv = LabInvestigation::whereHas('category', fn($q) => $q->where('type_id', $pathType->id))->first();
            if ($inv) {
                $orderNo = sprintf('LAB-PAT-TEST-%d', $opd->id);
                $order = LabInvestigationOrder::create([
                    'order_number' => $orderNo,
                    'opd_id'       => $opd->id,
                    'patient_id'   => $patient->id,
                    'doctor_id'    => $doctor->id,
                    'case_id'      => $case->id,
                    'datetime'     => now(),
                    'priority'     => 'Regular',
                    'source'       => 'OPD',
                    'type'         => 'pathology',
                ]);
                LabInvestigationOrderRequest::create([
                    'lab_inv_order_id'    => $order->id,
                    'lab_inv_id'          => $inv->id,
                    'lab_inv_type_id'     => $pathType->id,
                    'lab_inv_category_id' => $inv->category_id,
                    'status'              => 'Pending',
                ]);
                $this->command->line("  • Pathology order {$orderNo} ({$inv->name})");
            }
        }

        // 7. Confirm the controller's show() builds its data set without errors.
        //    We don't render the Blade view here — that requires the full
        //    web-middleware stack (ShareErrorsFromSession, etc.). Data assembly
        //    is what actually matters; the view itself is exercised in browser.
        $this->command->info('▶ Loading OPD show page data to verify all relations…');
        try {
            // Share an empty errors bag in case any helper reaches for it.
            view()->share('errors', new \Illuminate\Support\MessageBag());

            $controller = app(\App\Http\Controllers\OPD\OpdPatientController::class);
            $response   = $controller->show($opd);
            if ($response instanceof \Illuminate\Contracts\View\View) {
                $data = $response->getData();
                $keys = implode(', ', array_keys($data));
                $this->command->line("  ✓ show() built view data — keys: {$keys}");
            } else {
                $this->command->warn('  ⚠ show() did not return a View');
            }
        } catch (\Throwable $e) {
            $this->command->error('  ✗ show() blew up: ' . $e->getMessage());
            $this->command->line('    ' . $e->getFile() . ':' . $e->getLine());
        }

        $this->report($opd);
    }

    protected function report(OpdPatient $opd): void
    {
        $this->command->info(PHP_EOL . '=== RELATION VERIFICATION ===');
        $opd->refresh()->load([
            'patient','doctor','department','vitalChecks','medications','charges',
            'transactions','prescriptions','consultationNote','recheckups','documents',
        ]);
        $rows = [
            'patient'          => $opd->patient ? "OK ({$opd->patient->patient_name})" : 'MISSING',
            'doctor'           => $opd->doctor ? "OK ({$opd->doctor->name})" : 'MISSING',
            'department'       => $opd->department ? "OK ({$opd->department->name})" : 'MISSING',
            'vitalChecks'      => $opd->vitalChecks->count() . ' row(s)',
            'medications'      => $opd->medications->count() . ' row(s)',
            'charges'          => $opd->charges->count() . ' row(s)',
            'transactions'     => $opd->transactions->count() . ' row(s)',
            'prescriptions'    => $opd->prescriptions->count() . ' row(s)',
            'consultationNote' => $opd->consultationNote ? 'OK' : 'none',
            'recheckups'       => $opd->recheckups->count() . ' row(s)',
            'documents'        => $opd->documents->count() . ' row(s)',
        ];
        foreach ($rows as $k => $v) {
            $this->command->line('  ' . str_pad($k, 18) . ': ' . $v);
        }

        $labOrders = LabInvestigationOrder::where('opd_id', $opd->id)->with('requests')->get();
        $this->command->line('  ' . str_pad('lab_orders', 18) . ': ' . $labOrders->count() . ' order(s), '
            . $labOrders->sum(fn($o) => $o->requests->count()) . ' test request(s)');
    }
}
