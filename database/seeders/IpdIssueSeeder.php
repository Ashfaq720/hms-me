<?php
namespace Database\Seeders;

use App\Models\IpdPatient;
use App\Models\Pharmacy\IpdIssue;
use App\Models\User;
use Illuminate\Database\Seeder;

class IpdIssueSeeder extends Seeder
{
    public function run(): void
    {
        $users   = User::take(6)->pluck('name', 'id');
        $userIds = $users->keys()->toArray();

        $statuses = ['approved', 'approved', 'approved', 'pending', 'returned', 'resumed'];
        $sources  = ['Nurse Mitu', 'Dr. Azad', 'Nurse Joy', 'Dr. Karim', 'Nurse Sadia'];
        $wards    = ['Ward-A', 'Ward-B', 'ICU-01', 'ICU-02', 'Cabin-105', 'Cabin-203', 'Ward-C'];

        $ipdPatients = IpdPatient::with(['patient', 'bedAllocations.bed'])->get();

        $counter    = 0;
        $reqCounter = 0;

        // Create multiple issues per Ipd patient
        foreach ($ipdPatients as $ipd) {
            $issueCount = rand(2, 4);

            for ($i = 0; $i < $issueCount; $i++) {
                $counter++;
                $reqCounter++;
                $statusIdx = array_rand($statuses);
                $bed       = $ipd->bedAllocations->last();
                $wardBed   = $bed?->bed?->name ?? $wards[array_rand($wards)];

                IpdIssue::create([
                    'issue_no'       => 'IpdISU-' . str_pad($counter, 5, '0', STR_PAD_LEFT),
                    'ipd_patient_id' => $ipd->id,
                    'patient_id'     => $ipd->patient_id,
                    'requisition_no' => 'Req-' . str_pad($reqCounter, 3, '0', STR_PAD_LEFT),
                    'ward_bed'       => $wardBed,
                    'request_source' => $sources[array_rand($sources)],
                    'issued_by'      => $userIds[array_rand($userIds)],
                    'drug_count'     => rand(1, 5),
                    'total_amount'   => rand(200, 8000) + (rand(0, 99) / 100),
                    'status'         => $statuses[$statusIdx],
                    'created_at'     => now()->subDays(rand(0, 10))->setTime(rand(8, 17), rand(0, 59)),
                ]);
            }
        }
    }
}
