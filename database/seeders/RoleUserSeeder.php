<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Creates one demo user per role that currently has zero users. Enables
 * role-based-access QA (manual + Dusk) without seeding the full company.
 *
 * Login: <role-slug>@hms.test / password
 * Idempotent — skips roles that already have users.
 */
class RoleUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('▶ Seeding one demo user per empty role…');

        $created = 0; $skipped = 0;
        foreach (Role::withCount('users')->get() as $role) {
            if ($role->users_count > 0) { $skipped++; continue; }

            $slug = Str::slug($role->name);
            $email = "{$slug}@hms.test";
            // double-check email isn't somehow already taken
            if (User::where('email', $email)->exists()) {
                $email = "{$slug}-".Str::random(4)."@hms.test";
            }

            $user = User::create([
                'name'              => "Demo {$role->name}",
                'email'             => $email,
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole($role);
            $created++;
            $this->command->line("  · {$role->name} → {$email} / password");
        }

        $this->command->info("✓ Created {$created} demo users (skipped {$skipped} roles that already had users).");
    }
}
