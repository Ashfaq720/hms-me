<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Specialist;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo hospital doctors — one per specialty.
 *
 * Each doctor row is backed by a User account (so they can log in / be
 * referenced as the prescriber on orders), tied to a Department + a
 * Specialist, and stamped with the required `doctor_code` and
 * `marital_status` enum.
 *
 * Idempotent — re-runs match by email and skip existing rows.
 *
 * Run:  php artisan db:seed --class=DoctorSeeder
 */
class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('▶ Seeding departments + specialists…');
        $depts = $this->seedDepartments();
        $specs = $this->seedSpecialists();

        $this->command->info('▶ Seeding doctors…');
        $count = $this->seedDoctors($depts, $specs);
        $this->command->info("✓ {$count} doctors seeded.");
    }

    protected function seedDepartments()
    {
        $names = [
            'Medicine', 'Cardiology', 'Pediatrics', 'Gynecology', 'General Surgery',
            'Neurology', 'Orthopedics', 'Ophthalmology', 'Nephrology', 'Critical Care',
        ];
        $out = collect();
        foreach ($names as $n) {
            $out->put($n, Department::firstOrCreate(['name' => $n]));
        }
        return $out;
    }

    protected function seedSpecialists()
    {
        $names = [
            'General Physician', 'Cardiologist', 'Pediatrician', 'OB-GYN', 'General Surgeon',
            'Neurologist', 'Orthopedic Surgeon', 'Ophthalmologist', 'Nephrologist', 'Intensivist',
            'Neonatologist',
        ];
        $out = collect();
        foreach ($names as $n) {
            $out->put($n, Specialist::firstOrCreate(['name' => $n]));
        }
        return $out;
    }

    protected function seedDoctors($depts, $specs): int
    {
        // [name, email, gender, marital, department, specialist, phone]
        $defs = [
            ['Dr. Aisha Rahman',       'aisha@demo-hms.local',    'Female','Married','Medicine',       'General Physician',  '01700000101'],
            ['Dr. Kamal Hossain',      'kamal@demo-hms.local',    'Male',  'Married','Cardiology',     'Cardiologist',       '01700000102'],
            ['Dr. Salma Khatun',       'salma@demo-hms.local',    'Female','Married','Pediatrics',     'Pediatrician',       '01700000103'],
            ['Dr. Tanvir Ahmed',       'tanvir@demo-hms.local',   'Male',  'Single', 'Gynecology',     'OB-GYN',             '01700000104'],
            ['Dr. Mahmud Hasan',       'mahmud@demo-hms.local',   'Male',  'Married','General Surgery','General Surgeon',    '01700000105'],
            ['Dr. Nadia Sultana',      'nadia@demo-hms.local',    'Female','Single', 'Neurology',      'Neurologist',        '01700000106'],
            ['Dr. Imran Khan',         'imran@demo-hms.local',    'Male',  'Married','Orthopedics',    'Orthopedic Surgeon', '01700000107'],
            ['Dr. Roksana Begum',      'roksana@demo-hms.local',  'Female','Married','Ophthalmology',  'Ophthalmologist',    '01700000108'],
            ['Dr. Faisal Karim',       'faisal@demo-hms.local',   'Male',  'Married','Nephrology',     'Nephrologist',       '01700000109'],
            ['Dr. Mira Akhter',        'mira@demo-hms.local',     'Female','Single', 'Critical Care',  'Intensivist',        '01700000110'],
            ['Dr. Shahin Reza',        'shahin@demo-hms.local',   'Male',  'Married','Pediatrics',     'Neonatologist',      '01700000111'],
        ];

        $total = 0;
        foreach ($defs as [$name, $email, $gender, $marital, $deptName, $specName, $phone]) {
            // 1) User account (Doctor role + login)
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'              => $name,
                    'password'          => Hash::make('password'),
                    'type'              => config('constants.user_types.doctor', 'doctor'),
                    'email_verified_at' => now(),
                ]
            );
            // Make sure the user has the Doctor role
            if (! $user->hasRole('Doctor')) {
                $user->assignRole('Doctor');
            }

            // 2) Doctor row
            Doctor::firstOrCreate(
                ['email' => $email],
                [
                    'user_id'        => $user->id,
                    'doctor_code'    => 'DOC-' . strtoupper(substr(md5($email), 0, 6)),
                    'name'           => $name,
                    'phone'          => $phone,
                    'gender'         => $gender,
                    'marital_status' => $marital,
                    'department_id'  => $depts[$deptName]?->id,
                    'specialist_id'  => $specs[$specName]?->id,
                    'is_active'      => 1,
                ]
            );
            $total++;
        }
        return $total;
    }
}
