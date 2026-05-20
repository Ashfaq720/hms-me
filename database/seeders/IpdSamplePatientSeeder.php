<?php
namespace Database\Seeders;

use App\Models\Bed;
use App\Models\CaseReference;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\IpdPatient;
use App\Models\IpdPatientBed;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class IpdSamplePatientSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            [
                'patient_name'    => 'Rahim Uddin',
                'mobileno'        => '01711000001',
                'dob'             => '1985-03-12',
                'gender'          => 'Male',
                'blood_group'     => 'O+',
                'address'         => '12/A, Dhanmondi, Dhaka',
                'guardian_name'   => 'Karim Uddin',
                'patient_history' => 'High fever with chest pain, admitted for observation.',
                'admission_type'  => 'EMERGENCY',
                'days'            => 5,
            ],
            [
                'patient_name'    => 'Ayesha Siddiqua',
                'mobileno'        => '01711000002',
                'dob'             => '1992-07-24',
                'gender'          => 'Female',
                'blood_group'     => 'A+',
                'address'         => 'House 23, Gulshan-2, Dhaka',
                'guardian_name'   => 'Jamal Siddiqui',
                'patient_history' => 'Scheduled delivery, expected discharge within a week.',
                'admission_type'  => 'PLANNED',
                'days'            => 7,
            ],
            [
                'patient_name'    => 'Mohammad Hasan',
                'mobileno'        => '01711000003',
                'dob'             => '1978-11-05',
                'gender'          => 'Male',
                'blood_group'     => 'B+',
                'address'         => 'Sector 7, Uttara, Dhaka',
                'guardian_name'   => 'Nasir Hasan',
                'patient_history' => 'Type-2 diabetes complications, admitted for stabilization.',
                'admission_type'  => 'PLANNED',
                'days'            => 4,
            ],
            [
                'patient_name'    => 'Nasrin Akter',
                'mobileno'        => '01711000004',
                'dob'             => '1995-01-18',
                'gender'          => 'Female',
                'blood_group'     => 'AB+',
                'address'         => 'Mirpur-10, Dhaka',
                'guardian_name'   => 'Shahin Akter',
                'patient_history' => 'Appendicitis, pre-operative admission.',
                'admission_type'  => 'EMERGENCY',
                'days'            => 3,
            ],
            [
                'patient_name'    => 'Abdul Karim',
                'mobileno'        => '01711000005',
                'dob'             => '1960-09-30',
                'gender'          => 'Male',
                'blood_group'     => 'O-',
                'address'         => 'Banani, Dhaka',
                'guardian_name'   => 'Rafiq Karim',
                'patient_history' => 'Cardiac follow-up after angioplasty.',
                'admission_type'  => 'PLANNED',
                'days'            => 6,
            ],
        ];

        $doctorIds     = Doctor::pluck('id')->all();
        $departmentIds = Department::pluck('id')->all();

        if (empty($doctorIds) || empty($departmentIds)) {
            $this->command->warn('IpdSamplePatientSeeder: no doctors/departments found — skipping.');
            return;
        }

        $beds = $this->reserveBeds(count($samples));

        DB::transaction(function () use ($samples, $doctorIds, $departmentIds, $beds) {
            foreach ($samples as $i => $s) {
                $patient = Patient::create([
                    'patient_name'  => $s['patient_name'],
                    'mobileno'      => $s['mobileno'],
                    'dob'           => $s['dob'],
                    'gender'        => $s['gender'],
                    'blood_group'   => $s['blood_group'],
                    'address'       => $s['address'],
                    'guardian_name' => $s['guardian_name'],
                    'discount_type' => 'SELF',
                    'patient_type'  => 'Normal',
                    'is_ipd'        => 1,
                    'is_active'     => 1,
                ]);

                $case = CaseReference::create();

                $admission = Carbon::now()->subDays(2 - $i);
                $discharge = (clone $admission)->addDays($s['days']);

                $ipd = IpdPatient::create([
                    'case_id'                 => $case->id,
                    'patient_id'              => $patient->id,
                    'doctor_id'               => $doctorIds[$i % count($doctorIds)],
                    'department_id'           => $departmentIds[$i % count($departmentIds)],
                    'admission_date'          => $admission,
                    'possible_discharge_date' => $discharge,
                    'admission_type'          => $s['admission_type'],
                    'status'                  => 'Admitted',
                    'patient_history'         => $s['patient_history'],
                    'remarks'                 => 'Seeded test record',
                ]);

                $bed = $beds[$i];

                IpdPatientBed::create([
                    'case_id'        => $case->id,
                    'ipd_patient_id' => $ipd->id,
                    'bed_id'         => $bed->id,
                    'from'           => $admission,
                    'to'             => $discharge,
                    'remarks'        => 'Bed allocated (seeded)',
                    'status'         => 'Admitted',
                ]);

                $bed->update(['is_reserved' => true]);
            }
        });

        $this->command->info('IpdSamplePatientSeeder: inserted ' . count($samples) . ' Ipd patients.');
    }

    private function reserveBeds(int $count): array
    {
        $available = Bed::where(function ($q) {
            $q->where('is_reserved', false)->orWhereNull('is_reserved');
        })->orderBy('id')->take($count)->get()->all();

        $missing = $count - count($available);
        if ($missing > 0) {
            $bedTypeId  = optional(\App\Models\BedType::first())->id;
            $bedGroupId = optional(\App\Models\BedGroup::first())->id;

            for ($i = 1; $i <= $missing; $i++) {
                $available[] = Bed::create([
                    'name'         => 'Sample Bed ' . $i,
                    'rent'         => 1000,
                    'bed_type_id'  => $bedTypeId,
                    'bed_group_id' => $bedGroupId,
                    'is_reserved'  => false,
                    'is_active'    => 1,
                ]);
            }
        }

        return $available;
    }
}
