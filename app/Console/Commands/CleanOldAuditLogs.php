<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanOldAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:clean {--days=90 : Number of days to retain audit logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean audit logs older than the specified retention period (default: 90 days)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $retentionDays = (int) $this->option('days');
        
        if ($retentionDays < 1) {
            $this->error('Retention days must be at least 1.');
            return Command::FAILURE;
        }

        $cutoffDate = Carbon::now()->subDays($retentionDays);
        
        $this->info("Cleaning audit logs older than {$retentionDays} days (before {$cutoffDate->toDateString()})...");
        
        try {
            $deletedCount = AuditLog::where('created_at', '<', $cutoffDate)->delete();
            
            $this->info("Successfully deleted {$deletedCount} old audit log(s).");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to clean audit logs: {$e->getMessage()}");
            
            return Command::FAILURE;
        }
    }
}
