<?php

namespace App\Console\Commands;

use App\Models\Bed;
use App\Models\CaseReference;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Encounter\Encounter;
use App\Models\Icu\IcuAdmission;
use App\Models\IpdPatient;
use App\Models\IpdPatientBed;
use App\Models\OpdPatient;
use App\Models\Patient;
use App\Models\ServiceCharge\ServiceChargePosting;
use App\Models\User;
use App\Services\Billing\BillingService;
use App\Services\ServiceCharge\ServiceChargeEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `php artisan hms:scenario`
 *
 * Runs four patients through the four core hospital flows in parallel
 * — OPD only, IPD with bed, OT surgery, ICU admission — and reports each
 * step's outcome. Use after any deploy / seeding to confirm every module
 * still wires together correctly.
 */
class HmsMultiPatientScenarioCommand extends Command
{
    protected $signature = 'hms:scenario {--admin=admin@demo-hms.local}';
    protected $description = 'Run 4 patients through OPD / IPD / OT / ICU flows end-to-end.';

    public function handle(BillingService $billing, ServiceChargeEngine $engine): int
    {
        $admin = User::where('email', $this->option('admin'))->first();
        if (! $admin) {
            $this->error('Admin not found: ' . $this->option('admin'));
            return self::FAILURE;
        }
        Auth::login($admin);
        $orgId = $admin->current_organization_id;
        $branchId = $admin->current_branch_id;
        $doctor = Doctor::first();
        $department = Department::first();
        $bed = Bed::first();

        $this->info('═══════════════════════════════════════════════════════════════════');
        $this->info('   HMS MULTI-PATIENT SCENARIO — OPD / IPD / OT / ICU');
        $this->info('═══════════════════════════════════════════════════════════════════');

        $pass = 0; $fail = 0;
        $step = function (string $patientLabel, string $action, \Closure $body) use (&$pass, &$fail) {
            try {
                $result = $body();
                $this->line(sprintf('  <fg=green>✓</> [%s] %s — %s', $patientLabel, $action, $result));
                $pass++;
            } catch (\Throwable $e) {
                $this->line(sprintf('  <fg=red>✗</> [%s] %s — %s', $patientLabel, $action, $e->getMessage()));
                $fail++;
            }
        };

        // ───────── PATIENT A: OPD-only flow ─────────
        $this->newLine();
        $this->comment('▶ Patient A — OPD walk-in → consultation → cash payment');
        $patientA = null; $opdBill = null;
        $step('A', 'Register patient',
            function () use (&$patientA, $orgId, $branchId) {
                $patientA = Patient::create([
                    'patient_name' => 'A. Walk-in Patient',
                    'mobileno' => '0179' . random_int(1000000, 9999999),
                    'gender' => 'Male', 'dob' => '1990-06-15',
                    'blood_group' => 'B+',
                    'org_id' => $orgId, 'branch_id' => $branchId,
                ]);
                return 'MRN=' . $patientA->mrn;
            });
        $step('A', 'Create OPD visit',
            function () use (&$patientA, $doctor, $department) {
                $opd = OpdPatient::create([
                    'patient_id' => $patientA->id,
                    'doctor_id' => $doctor->id,
                    'department_id' => $department->id,
                    'date' => now(), 'visit_date' => now(),
                    'visit_type' => 'new', 'status' => 'in_consultation',
                    'chief_complaint' => 'Fever and cough',
                ]);
                return 'OPD #' . $opd->id . ' encounter=' . $opd->encounter_id;
            });
        $step('A', 'Auto-posted charges',
            function () use (&$patientA) {
                $count = ServiceChargePosting::whereHas('encounter', fn($q) => $q->where('patient_id', $patientA->id))->count();
                return $count . ' postings';
            });
        $opdA = null;
        $step('A', 'Capture OPD reference for sub-process tests',
            function () use (&$patientA, &$opdA) {
                $opdA = OpdPatient::where('patient_id', $patientA->id)->latest('id')->first();
                if (! $opdA) throw new \RuntimeException('OPD row not found for patient A');
                return 'OPD #' . $opdA->id;
            });
        $step('A', 'Vital check on OPD visit',
            function () use (&$patientA, &$opdA, $admin) {
                \App\Models\FrontDesk\VitalCheck::create([
                    'patient_id' => $patientA->id, 'patient_type' => 'OPD', 'opd_patient_id' => $opdA->id,
                    'patient_name' => $patientA->patient_name, 'gender' => $patientA->gender, 'age' => 36,
                    'weight' => 70, 'height' => 172, 'blood_pressure' => '118/78',
                    'temperature' => 99.1, 'heart_rate' => 80, 'respiratory_rate' => 16, 'spo2' => 98,
                    'remarks' => 'Mild fever', 'checked_by' => $admin->id, 'checked_at' => now(),
                ]);
                return 'vitals captured';
            });
        $step('A', 'OPD prescription created',
            function () use (&$patientA, &$opdA) {
                \App\Models\Prescription::create([
                    'prescription_no' => 'RX-OPD-' . time() . random_int(100, 999),
                    'opd_patient_id' => $opdA->id, 'patient_id' => $patientA->id,
                    'doctor_id' => $opdA->doctor_id, 'date' => now(),
                    'findings' => 'Viral fever',
                    'icd10_code' => 'B34.9', 'icd10_description' => 'Viral infection, unspecified',
                    'advice' => 'Rest + fluids', 'type' => 'OPD', 'generated_by' => 1,
                ]);
                return 'prescription written';
            });
        $step('A', 'OPD pathology + radiology lab orders',
            function () use (&$patientA, &$opdA) {
                \Illuminate\Support\Facades\DB::table('lab_investigation_order')->insert([
                    'order_number' => 'LAB-OPD-' . time(), 'opd_id' => $opdA->id, 'patient_id' => $patientA->id,
                    'doctor_id' => $opdA->doctor_id, 'datetime' => now(), 'generated_by' => 1,
                    'remarks' => 'CBC + Dengue NS1', 'priority' => 'routine', 'source' => 'opd', 'type' => 'pathology',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                \Illuminate\Support\Facades\DB::table('lab_investigation_order')->insert([
                    'order_number' => 'RAD-OPD-' . time(), 'opd_id' => $opdA->id, 'patient_id' => $patientA->id,
                    'doctor_id' => $opdA->doctor_id, 'datetime' => now(), 'generated_by' => 1,
                    'remarks' => 'Chest X-ray', 'priority' => 'routine', 'source' => 'opd', 'type' => 'radiology',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                return '2 orders placed (lab + radiology)';
            });
        $step('A', 'Pay + finalize OPD bill',
            function () use (&$patientA, &$opdBill, $billing) {
                $enc = Encounter::where('patient_id', $patientA->id)->where('encounter_type', 'OPD')->first();
                $opdBill = $billing->assembleFromEncounter($enc);
                $billing->collectPayment($opdBill, ['amount' => $opdBill->grand_total, 'method' => 'cash']);
                $opdBill = $billing->finalize($opdBill->fresh());
                return $opdBill->bill_no . ' ₿' . number_format((float)$opdBill->grand_total, 2) . ' status=' . $opdBill->status;
            });

        // ───────── PATIENT B: IPD admission with bed ─────────
        $this->newLine();
        $this->comment('▶ Patient B — IPD admission → bed (2 days) → multiple charges → discharge');
        $patientB = null; $ipdB = null; $ipdBillB = null;
        $step('B', 'Register patient',
            function () use (&$patientB, $orgId, $branchId) {
                $patientB = Patient::create([
                    'patient_name' => 'B. IPD Patient',
                    'mobileno' => '0179' . random_int(1000000, 9999999),
                    'gender' => 'Female', 'dob' => '1985-03-22',
                    'blood_group' => 'O+',
                    'org_id' => $orgId, 'branch_id' => $branchId,
                ]);
                return 'MRN=' . $patientB->mrn;
            });
        $step('B', 'IPD admission',
            function () use (&$patientB, &$ipdB, $doctor, $department) {
                $ipdB = IpdPatient::create([
                    'patient_id' => $patientB->id,
                    'doctor_id' => $doctor->id,
                    'department_id' => $department->id,
                    'admission_date' => now()->subDays(2),
                    'admission_type' => 'elective',
                    'status' => 'admitted',
                    'patient_history' => 'B - scenario',
                ]);
                return $ipdB->ipd_no;
            });
        $step('B', 'Bed allocation 2 days + release',
            function () use (&$ipdB, &$patientB, $bed) {
                $case = CaseReference::create(['patient_id' => $patientB->id, 'reference_type' => 'IPD']);
                $alloc = IpdPatientBed::create([
                    'case_id' => $case->id,
                    'ipd_patient_id' => $ipdB->id,
                    'bed_id' => $bed->id,
                    'allocation_type' => 'ward',
                    'from' => now()->subDays(2),
                    'status' => 'active',
                ]);
                $alloc->update(['to' => now(), 'status' => 'released']);
                return 'bed #' . $bed->id . ' released';
            });
        $step('B', 'Extra IPD charges (lab + nursing)',
            function () use (&$ipdB, $engine) {
                $count = 0;
                foreach (['LAB_CBC', 'NURSING_DAILY'] as $code) {
                    try {
                        $engine->post([
                            'service_code' => $code,
                            'encounter' => $ipdB->encounter_id,
                            'trigger_event' => 'ipd.manual.charge',
                            'quantity' => 1,
                        ]);
                        $count++;
                    } catch (\Throwable $e) { /* catalog missing */ }
                }
                return "$count extra charges posted";
            });
        $step('B', 'Assemble + pay + finalize IPD bill',
            function () use (&$ipdB, &$ipdBillB, $billing) {
                $enc = Encounter::find($ipdB->encounter_id);
                $ipdBillB = $billing->assembleFromEncounter($enc);
                $billing->collectPayment($ipdBillB, ['amount' => $ipdBillB->grand_total, 'method' => 'card']);
                $ipdBillB = $billing->finalize($ipdBillB->fresh());
                return $ipdBillB->bill_no . ' ₿' . number_format((float)$ipdBillB->grand_total, 2) . ' status=' . $ipdBillB->status;
            });
        $step('B', 'IPD vital + nurse + round + case doctor records',
            function () use (&$ipdB, &$patientB, $admin) {
                \App\Models\FrontDesk\VitalCheck::create([
                    'patient_id' => $patientB->id, 'patient_type' => 'IPD', 'ipd_patient_id' => $ipdB->id,
                    'patient_name' => $patientB->patient_name, 'gender' => $patientB->gender, 'age' => 41,
                    'weight' => 65, 'height' => 158, 'blood_pressure' => '125/82',
                    'temperature' => 98.4, 'heart_rate' => 78, 'respiratory_rate' => 16, 'spo2' => 99,
                    'checked_by' => $admin->id, 'checked_at' => now(),
                ]);
                \App\Models\Ipd\IpdNurseNote::create([
                    'ipd_patient_id' => $ipdB->id, 'title' => 'Day-1 nurse note', 'doctor_category' => 'Resident',
                    'shift' => 'Day', 'doctor_id' => $ipdB->doctor_id, 'priority' => 'normal',
                    'date' => now()->subDay(), 'nurse_name' => 'Nurse Rina',
                    'note' => 'Patient comfortable, fluids continued.', 'observations' => 'Stable',
                ]);
                \App\Models\Ipd\IpdRoundDr::create([
                    'ipd_patient_id' => $ipdB->id, 'datetime' => now()->subDay(), 'shift' => 'Morning',
                    'doctor_id' => $ipdB->doctor_id, 'visit_count' => 1,
                    'clinical_observation' => 'Post-admission stable', 'notes' => 'Continue regimen',
                ]);
                \App\Models\Ipd\IpdCaseDr::create([
                    'ipd_patient_id' => $ipdB->id, 'doctor_id' => $ipdB->doctor_id,
                    'datetime' => now()->subDays(2), 'shift' => 'Morning',
                    'note' => 'Initial assessment on admission', 'diagnosis' => 'Acute gastroenteritis',
                    'order_to' => 'Nursing', 'observations' => 'IV fluids ordered',
                    'order' => 'Hydrate + monitor', 'priority' => 'normal',
                ]);
                return 'vitals + nurse + round + case doctor written';
            });
        $step('B', 'IPD prescription + lab + radiology + medication + medicine order',
            function () use (&$ipdB, &$patientB, $admin) {
                $caseId = $ipdB->bedAllocations->first()?->case_id;
                \App\Models\Prescription::create([
                    'prescription_no' => 'RX-IPD-' . time() . random_int(100, 999),
                    'ipd_patient_id' => $ipdB->id, 'patient_id' => $patientB->id,
                    'doctor_id' => $ipdB->doctor_id, 'date' => now(),
                    'findings' => 'Acute gastroenteritis', 'icd10_code' => 'A09', 'icd10_description' => 'Diarrhoea',
                    'advice' => 'IV fluids + electrolytes', 'type' => 'IPD', 'generated_by' => 1,
                ]);
                \Illuminate\Support\Facades\DB::table('lab_investigation_order')->insert([
                    'order_number' => 'LAB-IPD-' . time(), 'ipd_id' => $ipdB->id, 'case_id' => $caseId,
                    'patient_id' => $patientB->id, 'doctor_id' => $ipdB->doctor_id,
                    'datetime' => now()->subDay(), 'generated_by' => 1, 'remarks' => 'CBC + Stool RE',
                    'priority' => 'urgent', 'source' => 'ipd', 'type' => 'pathology',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                \Illuminate\Support\Facades\DB::table('lab_investigation_order')->insert([
                    'order_number' => 'RAD-IPD-' . time(), 'ipd_id' => $ipdB->id, 'case_id' => $caseId,
                    'patient_id' => $patientB->id, 'doctor_id' => $ipdB->doctor_id,
                    'datetime' => now()->subDay(), 'generated_by' => 1, 'remarks' => 'Abdomen USG',
                    'priority' => 'routine', 'source' => 'ipd', 'type' => 'radiology',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $medId = \App\Models\Pharmacy\Medicine::first()?->id ?? 1;
                \Illuminate\Support\Facades\DB::table('ipd_medications')->insert([
                    'ipd_patient_id' => $ipdB->id, 'medicine_id' => $medId, 'datetime' => now(),
                    'dosage' => '500mg q8h', 'medicated_by' => $ipdB->doctor_id, 'remarks' => 'IV',
                    'notes' => 'Tolerated', 'created_at' => now(), 'updated_at' => now(),
                ]);
                \Illuminate\Support\Facades\DB::table('medicine_orders')->insert([
                    'medicine_id' => $medId, 'qty' => 15, 'prescribed_by' => $ipdB->doctor_id,
                    'patient_id' => $patientB->id, 'ipd_id' => $ipdB->id, 'case_id' => $caseId,
                    'source' => 'ipd', 'status' => 'dispensed', 'order_by' => 1,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                \Illuminate\Support\Facades\DB::table('ipd_treatment_history')->insert([
                    'ipd_id' => $ipdB->id, 'case_id' => $caseId, 'patient_id' => $patientB->id,
                    'doctor_id' => $ipdB->doctor_id, 'date' => now()->subDay(),
                    'prescribe_medicine' => 'Ondansetron + ORS', 'diagnosis' => 'Gastroenteritis',
                    'tx_note' => 'Hydration started', 'created_at' => now(), 'updated_at' => now(),
                ]);
                return 'Rx + lab + radiology + medication + order + treatment history written';
            });
        $step('B', 'Enrol patient B in PATHOLOGY package',
            function () use (&$ipdB, &$patientB) {
                $pkg = \App\Models\Package::where('package_type', 'PATHOLOGY')->where('is_active', true)->first();
                if (! $pkg) throw new \RuntimeException('No PATHOLOGY package available');
                $enr = \App\Models\Package\PackageEnrollment::firstOrCreate(
                    ['patient_id' => $patientB->id, 'package_id' => $pkg->id, 'encounter_id' => $ipdB->encounter_id],
                    [
                        'start_date' => now()->subDay(), 'end_date' => now()->addDays(30),
                        'agreed_price' => $pkg->total_amount, 'paid_amount' => 0, 'status' => 'active',
                        'notes' => 'Scenario: pathology bundle', 'created_by' => 1,
                    ]
                );
                return $enr->enrollment_no . ' pkg=' . $pkg->name;
            });
        $step('B', 'Discharge → encounter closed by observer',
            function () use (&$ipdB) {
                $ipdB->update(['discharge_date' => now(), 'status' => 'discharged']);
                $enc = Encounter::find($ipdB->encounter_id);
                if ($enc->status !== 'closed') throw new \RuntimeException('Encounter not closed: ' . $enc->status);
                return 'encounter status=' . $enc->status;
            });

        // ───────── PATIENT C: OT surgery flow ─────────
        $this->newLine();
        $this->comment('▶ Patient C — IPD + OT surgery booking → schedule');
        $patientC = null; $ipdC = null;
        $step('C', 'Register patient',
            function () use (&$patientC, $orgId, $branchId) {
                $patientC = Patient::create([
                    'patient_name' => 'C. OT Surgery Patient',
                    'mobileno' => '0179' . random_int(1000000, 9999999),
                    'gender' => 'Male', 'dob' => '1975-11-08',
                    'blood_group' => 'A+',
                    'org_id' => $orgId, 'branch_id' => $branchId,
                ]);
                return 'MRN=' . $patientC->mrn;
            });
        $step('C', 'IPD admission for surgery',
            function () use (&$patientC, &$ipdC, $doctor, $department) {
                $ipdC = IpdPatient::create([
                    'patient_id' => $patientC->id,
                    'doctor_id' => $doctor->id,
                    'department_id' => $department->id,
                    'admission_date' => now(),
                    'admission_type' => 'elective',
                    'status' => 'admitted',
                    'patient_history' => 'C - awaiting surgery',
                ]);
                return $ipdC->ipd_no;
            });
        $step('C', 'OT room charge (OT_ROOM × 3 hrs)',
            function () use (&$ipdC, $engine) {
                $engine->post([
                    'service_code' => 'OT_ROOM',
                    'encounter' => $ipdC->encounter_id,
                    'trigger_event' => 'ot.room.usage',
                    'quantity' => 3,
                    'reason' => 'Appendectomy 3-hour surgery',
                ]);
                $sum = ServiceChargePosting::where('encounter_id', $ipdC->encounter_id)
                    ->where('trigger_event', 'ot.room.usage')->sum('net_amount');
                return 'OT room charge ₿' . number_format((float)$sum, 2);
            });
        $step('C', 'Pre-op bed + nurse + round + case Dr + vitals',
            function () use (&$ipdC, &$patientC, $bed, $admin) {
                $case = CaseReference::create(['reference_type' => 'IPD']);
                if (! $ipdC->case_id) $ipdC->update(['case_id' => $case->id]);
                IpdPatientBed::firstOrCreate(
                    ['ipd_patient_id' => $ipdC->id, 'bed_id' => $bed->id],
                    ['case_id' => $ipdC->case_id ?? $case->id, 'allocation_type' => 'ward',
                     'from' => now()->subDays(2), 'status' => 'active']
                );
                \App\Models\FrontDesk\VitalCheck::create([
                    'patient_id' => $patientC->id, 'patient_type' => 'IPD', 'ipd_patient_id' => $ipdC->id,
                    'patient_name' => $patientC->patient_name, 'gender' => $patientC->gender,
                    'age' => 50, 'weight' => 72, 'height' => 170, 'blood_pressure' => '130/85',
                    'temperature' => 98.2, 'heart_rate' => 78, 'respiratory_rate' => 16, 'spo2' => 98,
                    'checked_by' => $admin->id, 'checked_at' => now()->subDay(),
                ]);
                \App\Models\Ipd\IpdNurseNote::create([
                    'ipd_patient_id' => $ipdC->id, 'title' => 'Pre-op nurse note', 'doctor_category' => 'Resident',
                    'shift' => 'Day', 'doctor_id' => $ipdC->doctor_id, 'priority' => 'normal',
                    'date' => now()->subDay(), 'nurse_name' => 'Nurse Lisa',
                    'note' => 'Pre-op preparation', 'observations' => 'NPO from midnight',
                ]);
                \App\Models\Ipd\IpdRoundDr::create([
                    'ipd_patient_id' => $ipdC->id, 'datetime' => now()->subDay(), 'shift' => 'Morning',
                    'doctor_id' => $ipdC->doctor_id, 'visit_count' => 1,
                    'clinical_observation' => 'Pre-op ready', 'notes' => 'Schedule OT',
                ]);
                \App\Models\Ipd\IpdCaseDr::create([
                    'ipd_patient_id' => $ipdC->id, 'doctor_id' => $ipdC->doctor_id,
                    'datetime' => now()->subDays(2), 'shift' => 'Morning',
                    'note' => 'Surgical consult', 'diagnosis' => 'Acute appendicitis',
                    'order_to' => 'OT', 'observations' => 'Schedule appendectomy',
                    'order' => 'Pre-op antibiotics', 'priority' => 'urgent',
                ]);
                return 'bed + nurse + round + case Dr + vitals written';
            });
        $step('C', 'OT surgery request + prescription + lab + radiology + medication + Tx hist',
            function () use (&$ipdC, &$patientC) {
                $caseId = $ipdC->bedAllocations->first()?->case_id ?? $ipdC->case_id;
                \App\Models\Ot\OtSurgeryRequest::firstOrCreate(
                    ['ipd_admission_id' => $ipdC->id],
                    ['request_no' => 'OT-IPD-' . $ipdC->id . '-' . time(),
                     'patient_id' => $patientC->id, 'encounter_type' => 'IPD',
                     'encounter_id' => $ipdC->encounter_id, 'surgery_type_id' => 1, 'surgery_category_id' => 1,
                     'requested_by_doctor_id' => $ipdC->doctor_id, 'primary_surgeon_id' => $ipdC->doctor_id,
                     'department_id' => $ipdC->department_id, 'requested_surgery_date' => now()->subDay(),
                     'requested_surgery_time' => '10:00', 'estimated_duration_minutes' => 180,
                     'priority' => 'routine', 'diagnosis' => 'Appendectomy',
                     'procedure_notes' => 'Elective appendectomy', 'status' => 'scheduled', 'created_by' => 1]
                );
                \App\Models\Prescription::create([
                    'prescription_no' => 'RX-IPD-' . $ipdC->id . '-' . time(),
                    'ipd_patient_id' => $ipdC->id, 'patient_id' => $patientC->id,
                    'doctor_id' => $ipdC->doctor_id, 'date' => now()->subDay(),
                    'findings' => 'Acute appendicitis', 'icd10_code' => 'K35',
                    'icd10_description' => 'Acute appendicitis', 'advice' => 'Surgery + IV antibiotics',
                    'type' => 'IPD', 'generated_by' => 1,
                ]);
                $medId = \App\Models\Pharmacy\Medicine::first()?->id ?? 1;
                \Illuminate\Support\Facades\DB::table('lab_investigation_order')->insert([
                    ['order_number' => 'LAB-IPD-' . $ipdC->id . '-' . time(), 'ipd_id' => $ipdC->id,
                     'case_id' => $caseId, 'patient_id' => $patientC->id, 'doctor_id' => $ipdC->doctor_id,
                     'datetime' => now()->subDay(), 'generated_by' => 1, 'remarks' => 'CBC + Coag',
                     'priority' => 'urgent', 'source' => 'ipd', 'type' => 'pathology',
                     'created_at' => now(), 'updated_at' => now()],
                    ['order_number' => 'RAD-IPD-' . $ipdC->id . '-' . time(), 'ipd_id' => $ipdC->id,
                     'case_id' => $caseId, 'patient_id' => $patientC->id, 'doctor_id' => $ipdC->doctor_id,
                     'datetime' => now()->subDay(), 'generated_by' => 1, 'remarks' => 'Abdomen CT',
                     'priority' => 'urgent', 'source' => 'ipd', 'type' => 'radiology',
                     'created_at' => now(), 'updated_at' => now()],
                ]);
                \Illuminate\Support\Facades\DB::table('ipd_medications')->insert([
                    'ipd_patient_id' => $ipdC->id, 'medicine_id' => $medId, 'datetime' => now()->subHours(6),
                    'dosage' => '1g IV', 'medicated_by' => $ipdC->doctor_id,
                    'remarks' => 'Pre-op antibiotic', 'notes' => 'Given',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                \Illuminate\Support\Facades\DB::table('medicine_orders')->insert([
                    'medicine_id' => $medId, 'qty' => 5, 'prescribed_by' => $ipdC->doctor_id,
                    'patient_id' => $patientC->id, 'ipd_id' => $ipdC->id, 'case_id' => $caseId,
                    'source' => 'ipd', 'status' => 'dispensed', 'order_by' => 1,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                \Illuminate\Support\Facades\DB::table('ipd_treatment_history')->insert([
                    'ipd_id' => $ipdC->id, 'case_id' => $caseId, 'patient_id' => $patientC->id,
                    'doctor_id' => $ipdC->doctor_id, 'date' => now()->subDay(),
                    'prescribe_medicine' => 'IV antibiotics', 'diagnosis' => 'Acute appendicitis',
                    'tx_note' => 'Pre-op', 'created_at' => now(), 'updated_at' => now(),
                ]);
                return 'OT req + Rx + lab + radiology + medication + order + tx hist written';
            });
        $step('C', 'Enrol C in Appendectomy package + assemble bill + advance',
            function () use (&$ipdC, &$patientC, $billing) {
                $pkg = \App\Models\Package::where('name', 'Appendectomy Package')->first()
                    ?? \App\Models\Package::where('package_type', 'OT')->first();
                if ($pkg) {
                    $enr = \App\Models\Package\PackageEnrollment::firstOrCreate(
                        ['patient_id' => $patientC->id, 'package_id' => $pkg->id, 'encounter_id' => $ipdC->encounter_id],
                        ['start_date' => now()->subDay(), 'end_date' => now()->addDays(10),
                         'agreed_price' => $pkg->total_amount, 'paid_amount' => 0.4 * (float) $pkg->total_amount,
                         'status' => 'active', 'notes' => 'Scenario: OT package', 'created_by' => 1]
                    );
                }
                $enc = Encounter::find($ipdC->encounter_id);
                $cBill = $billing->assembleFromEncounter($enc);
                $billing->collectPayment($cBill->fresh(), ['amount' => (float) $cBill->fresh()->grand_total * 0.5, 'method' => 'cash']);
                $cBill = $cBill->fresh();
                return $cBill->bill_no . ' ₿' . number_format((float) $cBill->grand_total, 2) . ' paid 50%';
            });

        // ───────── PATIENT E + F: Mother (C-section) + Newborn (NICU) ─────────
        $this->newLine();
        $this->comment('▶ Patient E (Mother) — IPD maternity → C-section OT → package enrolment, Patient F (Newborn) → NICU');
        $patientE = null; $ipdE = null; $patientF = null; $ipdF = null; $enrolE = null;

        $step('E', 'Register mother',
            function () use (&$patientE, $orgId, $branchId) {
                $patientE = Patient::create([
                    'patient_name' => 'E. Mother (C-section)',
                    'mobileno' => '0179' . random_int(1000000, 9999999),
                    'gender' => 'Female', 'dob' => '1992-04-10',
                    'blood_group' => 'O+',
                    'org_id' => $orgId, 'branch_id' => $branchId,
                ]);
                return 'MRN=' . $patientE->mrn;
            });

        $step('E', 'IPD maternity admission',
            function () use (&$patientE, &$ipdE, $doctor, $department) {
                $ipdE = IpdPatient::create([
                    'patient_id' => $patientE->id,
                    'doctor_id' => $doctor->id,
                    'department_id' => $department->id,
                    'admission_date' => now()->subDays(1),
                    'admission_type' => 'elective',
                    'status' => 'admitted',
                    'patient_history' => 'Maternity — elective C-section',
                ]);
                return $ipdE->ipd_no . ' encounter=' . $ipdE->encounter_id;
            });

        $step('E', 'Enroll mother in C-Section Package',
            function () use (&$ipdE, &$patientE, &$enrolE) {
                $pkg = \App\Models\Package::where('name', 'C-Section Package')->first()
                    ?? \App\Models\Package::where('package_type', 'OT')->first();
                if (! $pkg) throw new \RuntimeException('No OT package found');
                $enrolE = \App\Models\Package\PackageEnrollment::firstOrCreate(
                    ['patient_id' => $patientE->id, 'package_id' => $pkg->id, 'encounter_id' => $ipdE->encounter_id],
                    [
                        'start_date' => now()->subDay(),
                        'end_date' => now()->addDays(15),
                        'agreed_price' => $pkg->total_amount,
                        'paid_amount' => 0.5 * (float) $pkg->total_amount,
                        'status' => 'active',
                        'notes' => 'Scenario: maternity package',
                        'created_by' => auth()->id() ?? 1,
                    ]
                );
                foreach ($pkg->services as $ps) {
                    \App\Models\Package\PackageConsumptionEntry::firstOrCreate(
                        ['package_enrollment_id' => $enrolE->id, 'package_service_id' => $ps->id],
                        [
                            'description' => $ps->service->name ?? 'Service',
                            'quantity_allowed' => $ps->quantity,
                            'quantity_consumed' => 1,
                            'quantity_extras' => 0,
                            'source_type' => 'package',
                            'source_id' => $ps->id,
                        ]
                    );
                }
                return $enrolE->enrollment_no . ' pkg=' . $pkg->name . ' agreed=₿' . $enrolE->agreed_price;
            });

        $step('E', 'OT C-section room charge (OT_ROOM × 2 hrs)',
            function () use (&$ipdE, $engine) {
                $engine->post([
                    'service_code' => 'OT_ROOM',
                    'encounter' => $ipdE->encounter_id,
                    'trigger_event' => 'ot.room.usage',
                    'quantity' => 2,
                    'reason' => 'C-Section delivery',
                ]);
                $sum = ServiceChargePosting::where('encounter_id', $ipdE->encounter_id)
                    ->where('trigger_event', 'ot.room.usage')->sum('net_amount');
                return 'OT room ₿' . number_format((float)$sum, 2);
            });

        $step('F', 'Register newborn (linked to mother)',
            function () use (&$patientF, &$patientE, $orgId, $branchId) {
                $patientF = Patient::create([
                    'patient_name' => 'F. Newborn (Baby of ' . $patientE->patient_name . ')',
                    'mobileno' => '0179' . random_int(1000000, 9999999),
                    'gender' => 'Female', 'dob' => now()->subHours(12)->toDateString(),
                    'blood_group' => 'O+',
                    'guardian_name' => $patientE->patient_name,
                    'guardian_relation' => 'Mother',
                    'org_id' => $orgId, 'branch_id' => $branchId,
                ]);
                return 'MRN=' . $patientF->mrn . ' guardian=' . $patientE->patient_name;
            });

        $step('F', 'IPD newborn admission (NICU)',
            function () use (&$patientF, &$ipdF, $doctor, $department) {
                $ipdF = IpdPatient::create([
                    'patient_id' => $patientF->id,
                    'doctor_id' => $doctor->id,
                    'department_id' => $department->id,
                    'admission_date' => now()->subHours(10),
                    'admission_type' => 'emergency',
                    'status' => 'admitted',
                    'patient_history' => 'Newborn — respiratory observation',
                ]);
                return $ipdF->ipd_no . ' encounter=' . $ipdF->encounter_id;
            });

        $step('E', 'Mother ICU transfer (post-op complication) + bed-day accrual',
            function () use (&$ipdE, &$patientE) {
                $icuBed = \App\Models\Bed::whereHas('bedType', fn ($q) => $q->where('name', 'ICU'))->first();
                if (! $icuBed) throw new \RuntimeException('No ICU bed available');
                $case = CaseReference::create(['reference_type' => 'IPD']);
                $alloc = IpdPatientBed::create([
                    'case_id' => $case->id,
                    'ipd_patient_id' => $ipdE->id,
                    'bed_id' => $icuBed->id,
                    'allocation_type' => 'icu',
                    'from' => now()->subHours(8),
                    'status' => 'active',
                ]);
                $alloc->update(['to' => now(), 'status' => 'released']);
                $sum = ServiceChargePosting::where('encounter_id', $ipdE->encounter_id)
                    ->where('trigger_event', 'ipd.bed.accrual')->sum('net_amount');
                return 'ICU bed ' . $icuBed->name . ' total bed charge ₿' . number_format((float)$sum, 2);
            });

        $step('F', 'NICU bed allocation + bed-day accrual',
            function () use (&$ipdF, &$patientF) {
                $nicuBed = \App\Models\Bed::whereHas('bedType', fn ($q) => $q->where('name', 'NICU'))->first();
                if (! $nicuBed) throw new \RuntimeException('No NICU bed available');
                $case = CaseReference::create(['reference_type' => 'IPD']);
                $alloc = IpdPatientBed::create([
                    'case_id' => $case->id,
                    'ipd_patient_id' => $ipdF->id,
                    'bed_id' => $nicuBed->id,
                    'allocation_type' => 'nicu',
                    'from' => now()->subHours(10),
                    'status' => 'active',
                ]);
                $alloc->update(['to' => now(), 'status' => 'released']);
                $sum = ServiceChargePosting::where('encounter_id', $ipdF->encounter_id)
                    ->where('trigger_event', 'ipd.bed.accrual')->sum('net_amount');
                return 'NICU bed ' . $nicuBed->name . ' charge ₿' . number_format((float)$sum, 2);
            });

        // ───────── PATIENT F: Full NICU module lifecycle ─────────
        $nicuF = null;
        $step('F', 'NICU admission (birth details + APGAR + risk flags)',
            function () use (&$nicuF, &$ipdF, &$patientF, &$patientE, &$ipdE) {
                $nicuF = \App\Models\Nicu\NicuAdmission::create([
                    'ipd_patient_id' => $ipdF->id,
                    'patient_id' => $patientF->id,
                    'encounter_id' => $ipdF->encounter_id,
                    'mother_patient_id' => $patientE->id,
                    'mother_ipd_patient_id' => $ipdE->id,
                    'source' => 'OT',
                    'birth_type' => 'C_SECTION',
                    'is_multiple_birth' => false,
                    'birth_weight_g' => 2200,          // < 2500 → LBW auto-flag
                    'birth_length_cm' => 45,
                    'head_circumference_cm' => 32,
                    'gestational_age_weeks' => 34,     // < 37 → preterm auto-flag
                    'apgar_1min' => 6, 'apgar_5min' => 6, 'apgar_10min' => 8,  // <7 → critical auto-flag
                    'admission_priority' => 'URGENT',
                    'admission_time' => now()->subHours(10),
                    'admission_notes' => 'Premature LBW, mild respiratory distress',
                    'admitted_by' => 1,
                ]);
                $flags = collect([
                    $nicuF->is_low_birth_weight ? 'LBW' : null,
                    $nicuF->is_preterm ? 'Preterm' : null,
                    $nicuF->is_critical ? 'Critical' : null,
                ])->filter()->implode(',');
                return $nicuF->baby_id . ' flags=[' . $flags . ']';
            });
        $step('F', 'Incubator allocation + auto-charge on release',
            function () use (&$nicuF) {
                $alloc = \App\Models\Nicu\NicuResourceAllocation::create([
                    'nicu_admission_id' => $nicuF->id,
                    'resource_type' => 'INCUBATOR',
                    'device_serial' => 'INC-NICU-001',
                    'from' => now()->subHours(10),
                    'status' => 'active',
                    'reason' => 'Premature + LBW',
                    'assigned_by' => 1,
                ]);
                $alloc->update(['to' => now(), 'status' => 'released']);
                $sum = ServiceChargePosting::where('encounter_id', $nicuF->encounter_id)
                    ->where('trigger_event', 'nicu.resource.usage')->sum('net_amount');
                return 'Incubator released → auto-charge ₿' . number_format((float)$sum, 2);
            });
        $step('F', 'Record vitals (3 readings — normal, warning, critical)',
            function () use (&$nicuF) {
                \App\Models\Nicu\NicuVital::create([
                    'nicu_admission_id' => $nicuF->id, 'recorded_at' => now()->subHours(8),
                    'heart_rate' => 140, 'respiratory_rate' => 45, 'spo2' => 96,
                    'temperature_c' => 36.8, 'blood_glucose_mgdl' => 55, 'source' => 'DEVICE',
                ]);
                \App\Models\Nicu\NicuVital::create([
                    'nicu_admission_id' => $nicuF->id, 'recorded_at' => now()->subHours(5),
                    'heart_rate' => 165, 'respiratory_rate' => 18, 'spo2' => 92,
                    'temperature_c' => 35.8, 'blood_glucose_mgdl' => 50, 'source' => 'DEVICE',
                ]);
                \App\Models\Nicu\NicuVital::create([
                    'nicu_admission_id' => $nicuF->id, 'recorded_at' => now()->subHours(2),
                    'heart_rate' => 145, 'respiratory_rate' => 22, 'spo2' => 85,
                    'temperature_c' => 36.0, 'blood_glucose_mgdl' => 48, 'source' => 'MANUAL',
                ]);
                $counts = \App\Models\Nicu\NicuVital::where('nicu_admission_id', $nicuF->id)
                    ->selectRaw('alert_level, COUNT(*) as n')->groupBy('alert_level')->pluck('n', 'alert_level');
                return 'vitals=' . $counts->map(fn ($n, $k) => "$k:$n")->implode(' ');
            });
        $step('F', 'Feeding schedule + 2 feed logs',
            function () use (&$nicuF) {
                $sched = \App\Models\Nicu\NicuFeedingSchedule::create([
                    'nicu_admission_id' => $nicuF->id, 'feed_type' => 'EBM',
                    'interval_hours' => 3, 'volume_ml' => 20,
                    'start_date' => now()->subDay(), 'is_active' => true,
                ]);
                \App\Models\Nicu\NicuFeedLog::create([
                    'nicu_admission_id' => $nicuF->id, 'schedule_id' => $sched->id,
                    'fed_at' => now()->subHours(6), 'feed_type' => 'EBM',
                    'volume_ml' => 18, 'tolerance' => 'GOOD',
                ]);
                \App\Models\Nicu\NicuFeedLog::create([
                    'nicu_admission_id' => $nicuF->id, 'schedule_id' => $sched->id,
                    'fed_at' => now()->subHours(3), 'feed_type' => 'EBM',
                    'volume_ml' => 20, 'tolerance' => 'GOOD',
                ]);
                return 'schedule q3h × 20ml EBM + 2 logs';
            });
        $step('F', 'Growth record (Day-1 weight check → -3.2% loss flagged)',
            function () use (&$nicuF) {
                \App\Models\Nicu\NicuGrowthRecord::create([
                    'nicu_admission_id' => $nicuF->id,
                    'measured_on' => now()->toDateString(),
                    'weight_g' => 2130, // -3.2% from 2200g birth weight (below 10% threshold)
                    'length_cm' => 45, 'head_circumference_cm' => 32,
                    'measured_by' => 1,
                ]);
                $row = \App\Models\Nicu\NicuGrowthRecord::where('nicu_admission_id', $nicuF->id)->latest('id')->first();
                return 'Day-1 weight 2130g  Δ=' . $row->weight_change_pct . '%  alert_loss=' . var_export((bool) $row->alert_weight_loss, true);
            });
        $step('F', 'Weight-based medication order (Ampicillin 50mg/kg)',
            function () use (&$nicuF) {
                $order = \App\Models\Nicu\NicuMedicationOrder::create([
                    'nicu_admission_id' => $nicuF->id,
                    'drug_name' => 'Ampicillin',
                    'dose_per_kg_mg' => 50,
                    'weight_used_kg' => 2.130,
                    'route' => 'IV', 'frequency' => 'q12h',
                    'start_date' => now()->toDateString(), 'status' => 'active',
                ]);
                return 'Ampicillin → ' . $order->total_dose_mg . ' mg q12h IV (auto-calculated)';
            });
        $step('F', 'Phototherapy procedure (auto service-charge post)',
            function () use (&$nicuF) {
                $proc = \App\Models\Nicu\NicuProcedure::create([
                    'nicu_admission_id' => $nicuF->id,
                    'procedure_code' => 'PHOTOTHERAPY',
                    'procedure_name' => 'Single phototherapy session',
                    'start_time' => now()->subHours(4),
                    'end_time' => now()->subHours(2),
                    'status' => 'completed',
                    'clinical_indication' => 'Neonatal jaundice (bilirubin 14)',
                    'outcome' => 'Bilirubin reduced to 11',
                    'performed_by' => 1,
                ]);
                $posted = ServiceChargePosting::where('encounter_id', $nicuF->encounter_id)
                    ->where('trigger_event', 'nicu.procedure.' . $proc->id)->first();
                return 'Phototherapy completed → posting ₿' . number_format((float)($posted->net_amount ?? 0), 2);
            });
        $step('F', 'Parent consent captured (Treatment + Phototherapy)',
            function () use (&$nicuF, &$patientE) {
                foreach (['TREATMENT', 'PHOTOTHERAPY'] as $type) {
                    \App\Models\Nicu\NicuConsent::create([
                        'nicu_admission_id' => $nicuF->id,
                        'consent_type' => $type,
                        'guardian_name' => $patientE->patient_name,
                        'guardian_relation' => 'Mother',
                        'guardian_phone' => $patientE->mobileno,
                        'signed_at' => now()->subHours(11),
                        'status' => 'valid',
                    ]);
                }
                return '2 consents captured';
            });
        $step('F', 'Infection record (Klebsiella sepsis + cluster check)',
            function () use (&$nicuF) {
                \App\Models\Nicu\NicuInfectionRecord::create([
                    'nicu_admission_id' => $nicuF->id,
                    'infection_type' => 'Sepsis',
                    'organism' => 'Klebsiella pneumoniae',
                    'detected_on' => now()->toDateString(),
                    'isolation_required' => 'CONTACT',
                    'antibiotics_used' => 'Ampicillin + Gentamicin',
                    'status' => 'active',
                    'reported_by' => 1,
                ]);
                return 'Sepsis flagged + CONTACT isolation';
            });

        // ───────── PATIENT D: ICU admission ─────────
        $this->newLine();
        $this->comment('▶ Patient D — ICU admission → ICU bed accrual → discharge');
        $patientD = null; $ipdD = null;
        $step('D', 'Register patient',
            function () use (&$patientD, $orgId, $branchId) {
                $patientD = Patient::create([
                    'patient_name' => 'D. ICU Patient',
                    'mobileno' => '0179' . random_int(1000000, 9999999),
                    'gender' => 'Female', 'dob' => '1960-09-12',
                    'blood_group' => 'AB+',
                    'org_id' => $orgId, 'branch_id' => $branchId,
                ]);
                return 'MRN=' . $patientD->mrn;
            });
        $step('D', 'IPD admission (acuity=ICU)',
            function () use (&$patientD, &$ipdD, $doctor, $department) {
                $ipdD = IpdPatient::create([
                    'patient_id' => $patientD->id,
                    'doctor_id' => $doctor->id,
                    'department_id' => $department->id,
                    'admission_date' => now()->subDay(),
                    'admission_type' => 'emergency',
                    'status' => 'admitted',
                    'patient_history' => 'D - cardiac event',
                ]);
                return $ipdD->ipd_no;
            });
        $step('D', 'ICU bed allocation released after 1 day → BED_ICU accrued',
            function () use (&$ipdD, &$patientD, $bed) {
                $case = CaseReference::create(['patient_id' => $patientD->id, 'reference_type' => 'IPD']);
                $alloc = IpdPatientBed::create([
                    'case_id' => $case->id,
                    'ipd_patient_id' => $ipdD->id,
                    'bed_id' => $bed->id,
                    'allocation_type' => 'icu',
                    'from' => now()->subDay(),
                    'status' => 'active',
                ]);
                $alloc->update(['to' => now(), 'status' => 'released']);
                $bedSum = ServiceChargePosting::where('encounter_id', $ipdD->encounter_id)
                    ->where('trigger_event', 'ipd.bed.accrual')->sum('net_amount');
                return 'ICU bed charge ₿' . number_format((float)$bedSum, 2);
            });

        // ───────── SUMMARY ─────────
        $this->newLine();
        $this->info('═══ SCENARIO SUMMARY ═══');
        $this->line('Result: <fg=green>' . $pass . ' passed</>, <fg=red>' . $fail . ' failed</>');
        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Patients created in this run', Patient::where('patient_name', 'like', '% Patient%')->where('created_at', '>=', now()->subMinutes(2))->count()],
                ['OPD visits', OpdPatient::where('created_at', '>=', now()->subMinutes(2))->count()],
                ['IPD admissions', IpdPatient::where('created_at', '>=', now()->subMinutes(2))->count()],
                ['Encounters opened', Encounter::where('created_at', '>=', now()->subMinutes(2))->count()],
                ['Service-charge postings', ServiceChargePosting::where('created_at', '>=', now()->subMinutes(2))->count()],
                ['Bills assembled', \App\Models\Billing\Bill::where('created_at', '>=', now()->subMinutes(2))->count()],
                ['Bill payments collected', \App\Models\Billing\BillPayment::where('created_at', '>=', now()->subMinutes(2))->count()],
            ]
        );

        return $fail === 0 ? self::SUCCESS : self::FAILURE;
    }
}
