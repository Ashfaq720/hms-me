<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneActivityLog extends Command
{
    protected $signature = 'hms:prune-activity-log {--days=90 : Keep this many days of activity_log entries}';

    protected $description = 'Prune old activity_log entries beyond the retention window (default 90 days).';

    public function handle(): int
    {
        $days   = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $before = DB::table('activity_log')->count();
        $deleted = DB::table('activity_log')->where('created_at', '<', $cutoff)->delete();
        $after  = DB::table('activity_log')->count();

        $this->info("Activity log pruned: removed {$deleted} entries older than {$days} days.");
        $this->info("  before: {$before}  after: {$after}");
        return self::SUCCESS;
    }
}
