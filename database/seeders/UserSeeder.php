<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'type' => config('constants.user_types.super_admin'),
                'password' => Hash::make('password'),
                'phone' => '+1234567890'
            ]
        );
        $superAdmin->assignRole('Super Admin');

        // Create sample users for different roles
        $sampleUsers = [
            [
                'email' => 'sarah.johnson@example.com',
                'name' => 'Alice Smith',
                'type' => config('constants.user_types.user'),
            ],
            [
                'email' => 'michael.chen@example.com',
                'name' => 'Michael Chen',
                'type' => config('constants.user_types.user'),
            ],
            [
                'email' => 'emily.rodriguez@example.com',
                'name' => 'Emily Rodriguez',
                'type' => config('constants.user_types.user'),
            ],
            [
                'email' => 'james.williams@example.com',
                'name' => 'James Williams',
                'type' => config('constants.user_types.user'),
            ],
            [
                'email' => 'lisa.anderson@example.com',
                'name' => 'Lisa Anderson',
                'type' => config('constants.user_types.user'),
            ],
            [
                'email' => 'robert.davis@example.com',
                'name' => 'Robert Davis',
                'type' => config('constants.user_types.user'),
            ],
            [
                'email' => 'maria.garcia@example.com',
                'name' => 'Maria Garcia',
                'type' => config('constants.user_types.user'),
            ],
            [
                'email' => 'david.miller@example.com',
                'name' => 'David Miller',
                'type' => config('constants.user_types.user'),
            ],
            [
                'email' => 'mary.johnson@example.com',
                'name' => 'Mary Johnson',
                'type' => config('constants.user_types.user'),
            ],
            [
                'email' => 'receptionist@example.com',
                'name' => 'Sarah Wilson',
                'type' => config('constants.user_types.user'),
            ],
            [
                'email' => 'michael.brown@example.com',
                'name' => 'Michael Brown',
                'type' => config('constants.user_types.user'),
            ],
            [
                'email' => 'emily.davis@example.com',
                'name' => 'Emily Davis',
                'type' => config('constants.user_types.user'),
            ],
            [
                'email' => 'accountant@example.com',
                'name' => 'Robert Taylor',
                'type' => config('constants.user_types.user'),
            ]
        ];

        foreach ($sampleUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'phone' => '+1234567890',
                    'type' => $userData['type'],
                ]
            );
            $user->assignRole('User');
        }

        $this->command->info('Users seeded successfully!');
    }
}