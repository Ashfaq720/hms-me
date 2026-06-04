<?php

namespace App\Console\Commands;

use App\Models\Patient;
use Illuminate\Console\Command;

class BackfillHealthCardNumbers extends Command
{
    protected $signature   = 'patients:backfill-health-cards';
    protected $description = 'Generate health_card_no for patients who do not have one';

    public function handle(): void
    {
        $patients = Patient::whereNull('health_card_no')->orderBy('id')->get();

        if ($patients->isEmpty()) {
            $this->info('All patients already have a health card number.');
            return;
        }

        $bar = $this->output->createProgressBar($patients->count());
        $bar->start();

        foreach ($patients as $patient) {
            $year = $patient->created_at ? $patient->created_at->format('Y') : date('Y');
            $patient->forceFill([
                'health_card_no' => 'HC-' . $year . '-' . str_pad((string) $patient->id, 5, '0', STR_PAD_LEFT),
            ])->saveQuietly();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done — {$patients->count()} health card numbers generated.");
    }
}
