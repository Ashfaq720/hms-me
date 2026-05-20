<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [

            // System Settings
            [
                'key' => 'system_timezone',
                'value' => 'America/Chicago',
                'type' => 'string',
                'group' => 'system',
                'description' => 'System timezone setting',
                'is_public' => false,
                'is_active' => true,
            ],
            [
                'key' => 'system_currency',
                'value' => 'USD',
                'type' => 'string',
                'group' => 'system',
                'description' => 'System currency setting',
                'is_public' => false,
                'is_active' => true,
            ],
            [
                'key' => 'system_date_format',
                'value' => 'm/d/Y',
                'type' => 'string',
                'group' => 'system',
                'description' => 'System date format',
                'is_public' => false,
                'is_active' => true,
            ],
            [
                'key' => 'system_time_format',
                'value' => 'h:i A',
                'type' => 'string',
                'group' => 'system',
                'description' => 'System time format',
                'is_public' => false,
                'is_active' => true,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        $this->command->info('Settings seeded successfully!');
    }
}
