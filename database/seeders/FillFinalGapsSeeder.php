<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FillFinalGapsSeeder extends Seeder
{
    public function run(): void
    {
        $now    = now();
        $today  = today();
        $userId = DB::table('users')->value('id');

        // ────────────────────────────────────────
        // 1. AMBULANCE FLEET — drivers, paramedics, more ambulances
        // ────────────────────────────────────────
        if (DB::table('amb_drivers')->count() === 0) {
            $drivers = [
                ['Md. Rafiq Hossain',  '8512345678901', '01711-234567', 'DL-A-12345', 'HEAVY', '2028-12-31'],
                ['Mizanur Rahman',     '8512345678902', '01712-345678', 'DL-A-23456', 'HEAVY', '2027-06-30'],
                ['Selim Ahmed',        '8512345678903', '01713-456789', 'DL-A-34567', 'LIGHT', '2028-03-15'],
            ];
            foreach ($drivers as [$name, $nid, $phone, $lic, $type, $exp]) {
                DB::table('amb_drivers')->insert([
                    'name'             => $name,  'nid' => $nid,
                    'phone'            => $phone, 'license_number' => $lic,
                    'license_type'     => $type,  'license_expiry' => $exp,
                    'status'           => 'ACTIVE',
                    'created_at'       => $now,   'updated_at' => $now,
                ]);
            }
        }

        if (DB::table('amb_paramedics')->count() === 0) {
            $paramedics = [
                ['Nasrin Begum',   '8612345678901', '01811-234567', 'BLS',  '2028-06-30'],
                ['Karim Sheikh',   '8612345678902', '01812-345678', 'ACLS', '2027-12-31'],
                ['Farzana Akter',  '8612345678903', '01813-456789', 'BLS',  '2029-04-15'],
            ];
            foreach ($paramedics as [$name, $nid, $phone, $cert, $exp]) {
                DB::table('amb_paramedics')->insert([
                    'name'        => $name, 'nid' => $nid, 'phone' => $phone,
                    'certification' => $cert, 'cert_expiry' => $exp,
                    'status'      => 'ACTIVE',
                    'created_at'  => $now, 'updated_at' => $now,
                ]);
            }
        }

        // Extra ambulances if only 1
        if (DB::table('amb_ambulances')->count() < 3) {
            $newAmbs = [
                ['DHK-AMB-002', 'ALS',  'HOSPITAL', 2, 3, 2, 'AVAILABLE'],
                ['DHK-AMB-003', 'ICU',  'HOSPITAL', 1, 4, 3, 'ON_TRIP'],
            ];
            foreach ($newAmbs as [$reg, $type, $own, $st, $att, $ox, $status]) {
                if (! DB::table('amb_ambulances')->where('reg_no', $reg)->exists()) {
                    DB::table('amb_ambulances')->insert([
                        'reg_no'              => $reg,
                        'type'                => $type,
                        'ownership'           => $own,
                        'stretcher_capacity'  => $st,
                        'attendants_capacity' => $att,
                        'oxygen_capacity'     => $ox,
                        'status'              => $status,
                        'created_at'          => $now,
                        'updated_at'          => $now,
                    ]);
                }
            }
        }

        // ────────────────────────────────────────
        // 2. AMBULANCE REQUESTS (real today + recent)
        // ────────────────────────────────────────
        if (DB::table('amb_requests')->count() < 5) {
            $patients   = DB::table('patients')->inRandomOrder()->limit(10)->pluck('id')->all();
            $ambulances = DB::table('amb_ambulances')->pluck('id')->all();
            $drivers    = DB::table('amb_drivers')->pluck('id')->all();

            $requests = [
                ['EMERGENCY', 'CRITICAL', 'Mohakhali, Dhaka',     'Hospital ER',     'CRITICAL', 'Cardiac arrest — needs ALS',       'COMPLETED'],
                ['EMERGENCY', 'CRITICAL', 'Bashundhara R/A',      'Hospital ER',     'CRITICAL', 'RTA victim, multiple injuries',     'ASSIGNED'],
                ['TRANSFER',  'HIGH',     'Hospital IPD-205',     'Square Hospital', 'STABLE',   'IPD → outside hospital for MRI',    'ASSIGNED'],
                ['NORMAL',    'NORMAL',   'Mirpur-10',            'Hospital OPD',    'STABLE',   'Elderly patient routine transport', 'NEW'],
                ['EMERGENCY', 'CRITICAL', 'Uttara Sector-7',      'Hospital ER',     'CRITICAL', 'Severe asthma attack',              'COMPLETED'],
                ['TRANSFER',  'HIGH',     'Hospital ICU-2',       'BIRDEM',          'CRITICAL', 'ICU → tertiary care',               'COMPLETED'],
            ];
            $statusToTime = ['COMPLETED' => 24, 'ASSIGNED' => 1, 'NEW' => 0];
            foreach ($requests as $i => [$rtype, $pri, $pickup, $drop, $cond, $notes, $status]) {
                $hoursAgo = ($statusToTime[$status] ?? 0) * ($i + 1);
                DB::table('amb_requests')->insert([
                    'source'             => 'ER_DESK',
                    'request_type'       => $rtype,
                    'priority'           => $pri,
                    'pick_up_location'   => $pickup,
                    'contact_no'         => '01' . rand(7, 9) . rand(10, 99) . '-' . rand(100000, 999999),
                    'date'               => $today,
                    'time'               => $now->copy()->subHours($hoursAgo)->format('H:i:s'),
                    'is_unknown_patient' => 0,
                    'drop_location'      => $drop,
                    'patient_condition'  => $cond,
                    'patient_id'         => $patients[$i % count($patients)],
                    'status'             => $status,
                    'created_by'         => $userId,
                    'ambulance_id'       => in_array($status, ['ASSIGNED','COMPLETED']) ? $ambulances[array_rand($ambulances)] : null,
                    'driver_id'          => in_array($status, ['ASSIGNED','COMPLETED']) && $drivers ? $drivers[array_rand($drivers)] : null,
                    'created_at'         => $now->copy()->subHours($hoursAgo + 1),
                    'updated_at'         => $now,
                ]);
            }
        }

        // ────────────────────────────────────────
        // 3. PATIENT AVATARS (DiceBear seed URLs)
        // ────────────────────────────────────────
        // DiceBear is a free deterministic-avatar service; no API key needed.
        // We store the URL in patients.image. UIs already check for asset('storage/'.$image)
        // OR full URL — so a https:// URL passes through.
        $patientsWithoutImage = DB::table('patients')->whereNull('image')->limit(250)->get(['id', 'patient_name', 'gender']);
        foreach ($patientsWithoutImage as $p) {
            $style = $p->gender === 'Female' ? 'micah' : 'avataaars';
            $seed  = urlencode($p->patient_name ?: 'patient-' . $p->id);
            $url   = "https://api.dicebear.com/7.x/{$style}/svg?seed={$seed}&backgroundColor=b6e3f4,c0aede,d1d4f9,ffd5dc,ffdfbf";
            DB::table('patients')->where('id', $p->id)->update(['image' => $url, 'updated_at' => $now]);
        }
    }
}
