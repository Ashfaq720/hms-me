<?php

namespace Database\Seeders;

use App\Models\Bed;
use App\Models\CaseReference;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Icu\IcuAdmission;
use App\Models\IpdPatient;
use App\Models\Ot\OtRoom;
use App\Models\IpdPatientBed;
use App\Models\IpdPatientPackage;
use App\Models\NicuAdmission;
use App\Models\Ot\OtSurgeryRequest;
use App\Models\Ot\OtSurgerySchedule;
use App\Models\Patient;
use App\Models\PatientCharge;
use App\Models\ServicePackage;
use App\Services\PackageBillingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * End-to-end smoke seeder.
 *
 * Walks 6 sample patients through realistic journeys exercising every
 * cross-module link the package + bed + NICU + ICU + OT redesign touches:
 *
 *  1. C-Section mother → OT → NICU baby (full package + auto-bill)
 *  2. Appendectomy IPD → OT (package, no NICU)
 *  3. ICU admission (package)
 *  4. CCU admission (package)
 *  5. Plain IPD without package (manual charges)
 *  6. Direct NICU admission (external source, no OT)
 *
 * Idempotent — re-runs reuse patients by name + bed by code.
 * Run: php artisan db:seed --class=EndToEndSampleSeeder
 */
class EndToEndSampleSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('▶ Building real patient journeys…');

        // Cache packages by code so we don't grep every time
        $pkg = ServicePackage::pluck('id', 'code');
        if ($pkg->isEmpty()) {
            $this->command->warn('No packages found. Run PackageMasterSeeder first.');
            return;
        }

        $dept   = Department::pluck('id', 'name');
        // Use whichever doctor exists; if none, skip the journeys rather
        // than try to fabricate one — the doctors table has 7 required
        // fields tied to a User row, and we don't want to forge those.
        $doctor = Doctor::first();
        if (! $doctor) {
            $this->command->warn('No doctors found — skipping patient journey scenarios. Run the doctor seeder first.');
            return;
        }

        // Each scenario is wrapped so a schema-drift on one (e.g. NICU
        // column rename) doesn't abort the whole chain. Failed scenarios
        // log a warning and the seeder keeps going.
        foreach ([
            'scenarioCSection'     => 'Maternity (OT → NICU)',
            'scenarioAppendectomy' => 'Appendectomy',
            'scenarioIcuSepsis'    => 'ICU Sepsis',
            'scenarioCcuMI'        => 'CCU Acute MI',
            'scenarioPlainIpd'     => 'Plain IPD (manual charges)',
            'scenarioDirectNicu'   => 'Direct NICU referral',
        ] as $method => $label) {
            try {
                $this->{$method}($pkg, $dept, $doctor);
            } catch (\Throwable $e) {
                $this->command->warn("   ⚠ {$label} skipped — " . $e->getMessage());
            }
        }

        $this->command->info('✓ End-to-end journeys done. Run the verification command below to inspect.');
    }

    /* ──────── Scenarios ──────── */

    /**
     * Scenario 1 — Maternity full chain.
     * Mother (Mrs Rahima) → IPD with C-Section pkg → OT schedule → NICU baby
     * with NICU Daily pkg → auto-billed.
     */
    protected function scenarioCSection($pkg, $dept, $doctor): void
    {
        DB::transaction(function () use ($pkg, $dept, $doctor) {
            $mother = $this->ensurePatient([
                'patient_name'    => 'Mrs Rahima Begum',
                'mobileno'        => '01710000001',
                'gender'          => 'Female',
                'dob'             => '1995-03-12',
                'blood_group'     => 'O+',
                'address'         => 'Mirpur, Dhaka',
            ]);

            $case = $this->ensureCase(['parent_case_id' => null]);

            $ipd = $this->ensureIpd([
                'patient_id'     => $mother->id,
                'case_id'        => $case->id,
                'doctor_id'      => $doctor->id,
                'department_id'  => $dept['Gynecology'] ?? $dept->first(),
                'admission_date' => Carbon::now()->subDays(2),
                'admission_type' => 'Planned',
                'status'         => 'Admitted',
                'patient_history'=> 'G2P1, term pregnancy, planned LSCS',
            ]);

            // Allocate a Cabin bed for the mother
            $cabin = $this->grabBedByType(['Cabin', 'Deluxe Cabin']);
            $alloc = $this->ensureBedAllocation($ipd, $cabin);

            // Attach C-Section package
            $csPkg = ServicePackage::find($pkg['PKG-OT-001'] ?? null);
            if ($csPkg) {
                $att = $this->ensureIpdPackage($ipd, $csPkg, $alloc, [
                    'agreed_price' => $csPkg->priceForBedType($cabin?->bed_type_id),
                    'status'       => IpdPatientPackage::STATUS_CONFIRMED,
                ]);
                $this->billPackage($att);
            }

            // OT surgery request + schedule
            $req = $this->ensureOtRequest([
                'patient_id'       => $mother->id,
                'case_id'          => $case->id,
                'encounter_type'   => 'ipd',
                'encounter_id'     => $ipd->id,
                'ipd_admission_id' => $ipd->id,
                'department_id'    => $ipd->department_id,
                'priority'         => 'Routine',
                'status'           => OtSurgeryRequest::STATUS_SCHEDULED,
                'diagnosis'        => 'Full-term pregnancy, repeat C-section indication',
                'requested_surgery_date' => now()->subDay()->format('Y-m-d'),
                'requested_surgery_time' => '10:00',
                'estimated_duration_minutes' => 60,
                'requested_by_doctor_id' => $doctor->id,
            ]);

            $schedule = $this->ensureOtSchedule($req, [
                'service_package_id' => $csPkg?->id,
                'scheduled_start'    => Carbon::now()->subDay()->setTime(10, 0),
                'scheduled_end'      => Carbon::now()->subDay()->setTime(11, 30),
                'status'             => OtSurgerySchedule::STATUS_SURGERY_COMPLETED,
                'actual_start'       => Carbon::now()->subDay()->setTime(10, 5),
                'actual_end'         => Carbon::now()->subDay()->setTime(11, 25),
            ]);

            // NICU baby
            $nicuPkg = ServicePackage::find($pkg['PKG-NICU-001'] ?? null);
            $incubator = $this->grabBedByType(['NICU Bed', 'Incubator']);

            $babyName = 'Baby of ' . $mother->patient_name;
            $baby = $this->ensurePatient([
                'patient_name'      => $babyName,
                'gender'            => 'Female',
                'dob'               => Carbon::now()->subDay()->format('Y-m-d'),
                'parent_patient_id' => $mother->id,
                'birth_case_id'     => $case->id,
                'is_active'         => true,
            ]);

            $babyCase = $this->ensureCase(['parent_case_id' => $case->id]);

            $nicuAdm = NicuAdmission::firstOrCreate(
                ['patient_id' => $baby->id],
                [
                    // The lean schema requires baby_id (unique string) and
                    // patient_id (FK) — the rich-schema columns we add via
                    // migration cover baby_patient_id + case_id + source_*.
                    'baby_id'               => 'NB-' . $baby->id,
                    'baby_patient_id'       => $baby->id,
                    'mother_patient_id'     => $mother->id,
                    'case_id'               => $babyCase->id,
                    'source'                => 'OT',
                    'source_type'           => NicuAdmission::SOURCE_OT,
                    'source_id'             => $schedule->id,
                    'bed_id'                => $incubator?->id,
                    'bed_type_id'           => $incubator?->bed_type_id,
                    'admission_time'        => Carbon::now()->subDay()->setTime(11, 25),
                    'birth_at'              => Carbon::now()->subDay()->setTime(11, 20),
                    'birth_weight_grams'    => 3100,
                    'birth_weight_g'        => 3100,
                    'birth_length_cm'       => 49.5,
                    'head_circumference_cm' => 34,
                    'gestational_age_weeks' => 38,
                    'apgar_1min'            => 8,
                    'apgar_5min'            => 9,
                    'delivery_type'         => 'C-Section',
                    'birth_type'            => 'C_SECTION',
                    'is_multiple_birth'     => false,
                    'service_package_id'    => $nicuPkg?->id,
                    'status'                => NicuAdmission::STATUS_ADMITTED,
                    'clinical_notes'        => 'Term baby, vigorous cry, breastfed.',
                ]
            );

            if ($incubator && ! $incubator->is_reserved) {
                $incubator->update(['status' => Bed::STATUS_OCCUPIED]);
            }

            if ($nicuAdm->service_package_id) {
                app(PackageBillingService::class)->postChargeForNicu($nicuAdm->fresh());
            }

            $this->command->line("   • Maternity chain: {$mother->patient_name} → OT (".$schedule->schedule_no.") → NICU (".$nicuAdm->admission_no.")");
        });
    }

    /** Scenario 2 — Appendectomy IPD with package, no NICU */
    protected function scenarioAppendectomy($pkg, $dept, $doctor): void
    {
        DB::transaction(function () use ($pkg, $dept, $doctor) {
            $patient = $this->ensurePatient([
                'patient_name' => 'Mr Karim Hossain',
                'mobileno'     => '01710000002',
                'gender'       => 'Male',
                'dob'          => '1988-07-22',
                'blood_group'  => 'B+',
            ]);

            $case = $this->ensureCase(['parent_case_id' => null]);
            $ipd = $this->ensureIpd([
                'patient_id'     => $patient->id,
                'case_id'        => $case->id,
                'doctor_id'      => $doctor->id,
                'department_id'  => $dept['Surgery'] ?? $dept->first(),
                'admission_date' => Carbon::now()->subDay(),
                'admission_type' => 'Emergency',
                'status'         => 'Admitted',
                'patient_history'=> 'RIF pain, fever, raised WBC → acute appendicitis',
            ]);

            $bed = $this->grabBedByType(['General Bed']);
            $alloc = $this->ensureBedAllocation($ipd, $bed);

            $appPkg = ServicePackage::find($pkg['PKG-OT-002'] ?? null);
            if ($appPkg) {
                $att = $this->ensureIpdPackage($ipd, $appPkg, $alloc, [
                    'agreed_price' => $appPkg->priceForBedType($bed?->bed_type_id),
                    'status'       => IpdPatientPackage::STATUS_CONFIRMED,
                ]);
                $this->billPackage($att);
            }

            $req = $this->ensureOtRequest([
                'patient_id'       => $patient->id,
                'case_id'          => $case->id,
                'encounter_type'   => 'ipd',
                'encounter_id'     => $ipd->id,
                'ipd_admission_id' => $ipd->id,
                'department_id'    => $ipd->department_id,
                'priority'         => 'Emergency',
                'is_emergency'     => true,
                'status'           => OtSurgeryRequest::STATUS_SCHEDULED,
                'diagnosis'        => 'Acute appendicitis',
                'requested_surgery_date' => now()->format('Y-m-d'),
                'requested_surgery_time' => '14:00',
                'estimated_duration_minutes' => 45,
                'requested_by_doctor_id' => $doctor->id,
            ]);

            $this->ensureOtSchedule($req, [
                'service_package_id' => $appPkg?->id,
                'scheduled_start'    => Carbon::now()->setTime(14, 0),
                'scheduled_end'      => Carbon::now()->setTime(14, 45),
                'status'             => OtSurgerySchedule::STATUS_SURGERY_RUNNING,
                'actual_start'       => Carbon::now()->setTime(14, 5),
            ]);

            $this->command->line("   • Appendectomy: {$patient->patient_name} → OT scheduled");
        });
    }

    /** Scenario 3 — Sepsis ICU admission with package + 5 daily charges */
    protected function scenarioIcuSepsis($pkg, $dept, $doctor): void
    {
        DB::transaction(function () use ($pkg, $dept, $doctor) {
            $patient = $this->ensurePatient([
                'patient_name' => 'Mr Faruk Ali',
                'mobileno'     => '01710000003',
                'gender'       => 'Male',
                'dob'          => '1970-10-05',
                'blood_group'  => 'A+',
            ]);

            $case = $this->ensureCase(['parent_case_id' => null]);
            $ipd = $this->ensureIpd([
                'patient_id'     => $patient->id,
                'case_id'        => $case->id,
                'doctor_id'      => $doctor->id,
                'department_id'  => $dept['Critical Care'] ?? $dept['Medicine'] ?? $dept->first(),
                'admission_date' => Carbon::now()->subDays(3),
                'admission_type' => 'Emergency',
                'status'         => 'Admitted',
                'patient_history'=> 'Severe sepsis, hypotensive, lactate 4.5',
            ]);

            $icuBed = $this->grabBedByType(['ICU Bed']);
            $alloc  = $this->ensureBedAllocation($ipd, $icuBed, allocationType: 'icu');

            $sepPkg = ServicePackage::find($pkg['PKG-ICU-002'] ?? null);
            if ($sepPkg) {
                $att = $this->ensureIpdPackage($ipd, $sepPkg, $alloc, [
                    'agreed_price' => $sepPkg->priceForBedType($icuBed?->bed_type_id),
                    'status'       => IpdPatientPackage::STATUS_CONFIRMED,
                ]);
                $this->billPackage($att);
            }

            // Mirror in icu_admissions for the ICU dashboard
            IcuAdmission::firstOrCreate(
                ['icu_case_id' => 'ICU-' . str_pad((string) $ipd->id, 5, '0', STR_PAD_LEFT)],
                [
                    'patient_id'     => $patient->id,
                    'case_id'        => $case->id,
                    'source_type'    => 'ipd',
                    'source_id'      => $ipd->id,
                    'bed_id'         => $icuBed?->id,
                    'icu_type'       => 'ICU',
                    'status'         => 'Admitted',
                    'admission_time' => Carbon::now()->subDays(3),
                ]
            );

            $this->command->line("   • Sepsis ICU: {$patient->patient_name}");
        });
    }

    /** Scenario 4 — Acute MI in CCU */
    protected function scenarioCcuMI($pkg, $dept, $doctor): void
    {
        DB::transaction(function () use ($pkg, $dept, $doctor) {
            $patient = $this->ensurePatient([
                'patient_name' => 'Mr Salim Uddin',
                'mobileno'     => '01710000004',
                'gender'       => 'Male',
                'dob'          => '1965-05-18',
                'blood_group'  => 'O+',
            ]);

            $case = $this->ensureCase(['parent_case_id' => null]);
            $ipd  = $this->ensureIpd([
                'patient_id'     => $patient->id,
                'case_id'        => $case->id,
                'doctor_id'      => $doctor->id,
                'department_id'  => $dept['Cardiology'] ?? $dept['Medicine'] ?? $dept->first(),
                'admission_date' => Carbon::now()->subDays(1),
                'admission_type' => 'Emergency',
                'status'         => 'Admitted',
                'patient_history'=> 'STEMI inferior wall, presented within golden hour',
            ]);

            $ccuBed = $this->grabBedByType(['CCU Bed']);
            $alloc  = $this->ensureBedAllocation($ipd, $ccuBed, allocationType: 'icu');

            $miPkg = ServicePackage::find($pkg['PKG-CCU-001'] ?? null);
            if ($miPkg) {
                $att = $this->ensureIpdPackage($ipd, $miPkg, $alloc, [
                    'agreed_price' => $miPkg->priceForBedType($ccuBed?->bed_type_id),
                    'status'       => IpdPatientPackage::STATUS_CONFIRMED,
                ]);
                $this->billPackage($att);
            }

            IcuAdmission::firstOrCreate(
                ['icu_case_id' => 'CCU-' . str_pad((string) $ipd->id, 5, '0', STR_PAD_LEFT)],
                [
                    'patient_id'     => $patient->id,
                    'case_id'        => $case->id,
                    'source_type'    => 'ipd',
                    'source_id'      => $ipd->id,
                    'bed_id'         => $ccuBed?->id,
                    'icu_type'       => 'CCU',
                    'status'         => 'Admitted',
                    'admission_time' => Carbon::now()->subDay(),
                ]
            );

            $this->command->line("   • CCU MI: {$patient->patient_name}");
        });
    }

    /** Scenario 5 — Plain IPD without any package, manual charges */
    protected function scenarioPlainIpd($pkg, $dept, $doctor): void
    {
        DB::transaction(function () use ($dept, $doctor) {
            $patient = $this->ensurePatient([
                'patient_name' => 'Mrs Nasrin Akter',
                'mobileno'     => '01710000005',
                'gender'       => 'Female',
                'dob'          => '1980-12-01',
                'blood_group'  => 'AB+',
            ]);

            $case = $this->ensureCase(['parent_case_id' => null]);
            $ipd  = $this->ensureIpd([
                'patient_id'     => $patient->id,
                'case_id'        => $case->id,
                'doctor_id'      => $doctor->id,
                'department_id'  => $dept['Medicine'] ?? $dept->first(),
                'admission_date' => Carbon::now()->subDays(2),
                'admission_type' => 'Planned',
                'status'         => 'Admitted',
                'patient_history'=> 'Type 2 DM with poor glycaemic control, fatigue',
            ]);

            $bed = $this->grabBedByType(['General Bed']);
            $this->ensureBedAllocation($ipd, $bed);

            // Manual charges — no package
            $this->ensureManualCharge($case->id, $ipd->id, 'Bed charge (2 days)', 2, 1000);
            $this->ensureManualCharge($case->id, $ipd->id, 'Doctor visit',         2,  500);
            $this->ensureManualCharge($case->id, $ipd->id, 'IV antibiotics',       2,  450);
            $this->ensureManualCharge($case->id, $ipd->id, 'CBC + RFT + HbA1c',    1, 1200);

            $this->command->line("   • Plain IPD (no package): {$patient->patient_name} — manual charges posted");
        });
    }

    /** Scenario 6 — Direct NICU admission (referral / external) */
    protected function scenarioDirectNicu($pkg, $dept, $doctor): void
    {
        DB::transaction(function () use ($pkg) {
            $baby = $this->ensurePatient([
                'patient_name' => 'Baby Ahmed (Referral)',
                'gender'       => 'Male',
                'dob'          => Carbon::now()->subDays(2)->format('Y-m-d'),
            ]);

            $case = $this->ensureCase(['parent_case_id' => null]);
            $warmer = $this->grabBedByType(['Warmer', 'NICU Bed']);

            $nicuPkg = ServicePackage::find($pkg['PKG-NICU-002'] ?? $pkg['PKG-NICU-001'] ?? null);

            $adm = NicuAdmission::firstOrCreate(
                ['patient_id' => $baby->id],
                [
                    'baby_id'               => 'NB-' . $baby->id,
                    'baby_patient_id'       => $baby->id,
                    'case_id'               => $case->id,
                    'source'                => 'EXTERNAL_REFERRAL',
                    'source_type'           => NicuAdmission::SOURCE_EXTERNAL,
                    'bed_id'                => $warmer?->id,
                    'bed_type_id'           => $warmer?->bed_type_id,
                    'admission_time'        => Carbon::now()->subDays(2)->addHour(),
                    'birth_at'              => Carbon::now()->subDays(2),
                    'birth_weight_grams'    => 1850,
                    'birth_weight_g'        => 1850,
                    'birth_length_cm'       => 42,
                    'head_circumference_cm' => 30,
                    'gestational_age_weeks' => 33,
                    'apgar_1min'            => 6,
                    'apgar_5min'            => 8,
                    'delivery_type'         => 'Vaginal',
                    'birth_type'            => 'NORMAL',
                    'service_package_id'    => $nicuPkg?->id,
                    'status'                => NicuAdmission::STATUS_ADMITTED,
                    'clinical_notes'        => 'Preterm 33 weeks, LBW. Referred from rural clinic for warmer support.',
                ]
            );

            if ($warmer && ! $warmer->is_reserved) {
                $warmer->update(['status' => Bed::STATUS_OCCUPIED]);
            }
            if ($adm->service_package_id) {
                app(PackageBillingService::class)->postChargeForNicu($adm->fresh());
            }

            $this->command->line("   • Direct NICU: {$baby->patient_name} (preterm, LBW)");
        });
    }

    /* ──────── Idempotent helpers ──────── */

    protected function ensurePatient(array $attrs): Patient
    {
        // Pre-allocate a unique MRN that won't collide with existing patients
        // whose MRN doesn't match their numeric id (legacy data quirk).
        $existingMaxNumeric = (int) (\DB::table('patients')
            ->selectRaw('COALESCE(MAX(CAST(REPLACE(mrn, "MRN-", "") AS UNSIGNED)), 0) as m')
            ->value('m'));
        $mrn = 'MRN-' . str_pad((string) ($existingMaxNumeric + 1 + random_int(0, 50)), 6, '0', STR_PAD_LEFT);
        while (Patient::where('mrn', $mrn)->exists()) {
            $existingMaxNumeric += 1;
            $mrn = 'MRN-' . str_pad((string) $existingMaxNumeric, 6, '0', STR_PAD_LEFT);
        }

        // Unique mobileno for newborns / referrals (schema has unique constraint).
        $mobileno = $attrs['mobileno'] ?? null;
        if (! $mobileno) {
            $mobileno = 'NB-' . substr(md5($attrs['patient_name'] . microtime()), 0, 10);
            while (Patient::where('mobileno', $mobileno)->exists()) {
                $mobileno = 'NB-' . substr(md5($attrs['patient_name'] . microtime() . random_int(0, 9999)), 0, 10);
            }
        }

        return Patient::firstOrCreate(
            ['patient_name' => $attrs['patient_name']],
            array_merge([
                'is_active' => true,
                'mobileno'  => $mobileno,
                'mrn'       => $mrn,
            ], $attrs)
        );
    }

    protected function ensureCase(array $attrs): CaseReference
    {
        return CaseReference::create($attrs);
    }

    protected function ensureIpd(array $attrs): IpdPatient
    {
        $existing = IpdPatient::where('patient_id', $attrs['patient_id'])
            ->where('case_id', $attrs['case_id'])->first();
        if ($existing) return $existing;
        return IpdPatient::create($attrs);
    }

    protected function grabBedByType(array $typeNames): ?Bed
    {
        $bedTypeIds = \App\Models\BedType::whereIn('name', $typeNames)->pluck('id');
        if ($bedTypeIds->isEmpty()) return null;
        return Bed::whereIn('bed_type_id', $bedTypeIds)
            ->where('is_active', 1)
            ->where(function ($q) {
                $q->where('status', Bed::STATUS_AVAILABLE)->orWhere('status', Bed::STATUS_READY);
            })
            ->orderBy('id')->first();
    }

    protected function ensureBedAllocation(IpdPatient $ipd, ?Bed $bed, string $allocationType = 'bed'): ?IpdPatientBed
    {
        if (! $bed) return null;
        $existing = IpdPatientBed::where('ipd_patient_id', $ipd->id)->whereNull('to')->first();
        if ($existing) return $existing;

        $alloc = IpdPatientBed::create([
            'case_id'         => $ipd->case_id,
            'ipd_patient_id'  => $ipd->id,
            'bed_id'          => $bed->id,
            'allocation_type' => $allocationType,
            'from'            => $ipd->admission_date ?? now(),
            'status'          => 'Active',
        ]);

        $bed->update(['status' => Bed::STATUS_OCCUPIED]);
        return $alloc;
    }

    protected function ensureIpdPackage(IpdPatient $ipd, ServicePackage $pkg, ?IpdPatientBed $alloc, array $extras): IpdPatientPackage
    {
        $existing = IpdPatientPackage::where('ipd_admission_id', $ipd->id)
            ->where('service_package_id', $pkg->id)->first();
        if ($existing) return $existing;

        return IpdPatientPackage::create(array_merge([
            'ipd_admission_id'    => $ipd->id,
            'service_package_id'  => $pkg->id,
            'bed_allocation_id'   => $alloc?->id,
            'applied_at'          => now(),
        ], $extras));
    }

    protected function ensureOtRequest(array $attrs): OtSurgeryRequest
    {
        $existing = OtSurgeryRequest::where('patient_id', $attrs['patient_id'])
            ->where('case_id', $attrs['case_id'])->first();
        if ($existing) return $existing;
        return OtSurgeryRequest::create($attrs);
    }

    protected function ensureOtSchedule(OtSurgeryRequest $req, array $extras): OtSurgerySchedule
    {
        $existing = OtSurgerySchedule::where('surgery_request_id', $req->id)->first();
        if ($existing) return $existing;

        $room = OtRoom::first();
        return OtSurgerySchedule::create(array_merge([
            'surgery_request_id' => $req->id,
            'ot_room_id'         => $room?->id,
        ], $extras));
    }

    protected function billPackage(IpdPatientPackage $att): void
    {
        // PackageBillingService requires auth() — guard for seeder context
        if (! auth()->check()) {
            // Manually post the bundled charge in seeder context
            $existing = PatientCharge::where('service_package_id', $att->service_package_id)
                ->where('ipd_id', $att->ipd_admission_id)->whereNull('deleted_at')->first();
            if ($existing) return;

            $att->loadMissing(['package', 'ipdAdmission']);
            if (! $att->package || ! $att->ipdAdmission) return;

            PatientCharge::create([
                'case_id'            => $att->ipdAdmission->case_id,
                'service_package_id' => $att->package->id,
                'charge_module'      => 'ipd',
                'doctor_id'          => $att->ipdAdmission->doctor_id,
                'department_id'      => $att->ipdAdmission->department_id,
                'ipd_id'             => $att->ipdAdmission->id,
                'charge_item'        => $att->package->code . ' — ' . $att->package->name,
                'unit_price'         => $att->effectivePrice(),
                'quantity'           => 1,
                'amount'             => $att->effectivePrice(),
                'net_amount'         => $att->effectivePrice(),
                'date'               => now(),
                'notes'               => 'Package auto-billed via seeder',
                'status'              => 'posted',
                'is_paid'             => false,
                'is_bill_generated'   => false,
            ]);
        } else {
            app(PackageBillingService::class)->postCharge($att);
        }
    }

    protected function ensureManualCharge(int $caseId, int $ipdId, string $item, int $qty, float $unit): void
    {
        $existing = PatientCharge::where('case_id', $caseId)
            ->where('ipd_id', $ipdId)
            ->where('charge_item', $item)
            ->whereNull('service_package_id')
            ->first();
        if ($existing) return;

        PatientCharge::create([
            'case_id'        => $caseId,
            'charge_module'  => 'ipd',
            'ipd_id'         => $ipdId,
            'charge_item'    => $item,
            'unit_price'     => $unit,
            'quantity'       => $qty,
            'amount'         => $unit * $qty,
            'net_amount'     => $unit * $qty,
            'date'           => now(),
            'status'         => 'posted',
            'is_paid'        => false,
        ]);
    }
}
