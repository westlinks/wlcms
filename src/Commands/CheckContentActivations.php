<?php

namespace Westlinks\Wlcms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Westlinks\Wlcms\Models\ContentItem;
use Carbon\Carbon;

class CheckContentActivations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wlcms:check-activations {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and process automatic content activation/deactivation based on scheduled dates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $now = Carbon::now();
        
        $this->info('🔍 Checking content activations...');
        $this->info('Current time: ' . $now->format('Y-m-d H:i:s'));
        
        if ($isDryRun) {
            $this->warn('⚠️  DRY RUN MODE - No changes will be made');
        }

        $activated = 0;
        $deactivated = 0;

        // Find content to activate
        $toActivate = ContentItem::where('auto_activate', true)
            ->where('status', '!=', 'published')
            ->whereNotNull('activation_date')
            ->where('activation_date', '<=', $now)
            ->get();

        foreach ($toActivate as $content) {
            if (!$isDryRun) {
                $content->update([
                    'status' => 'published',
                    'published_at' => $now,
                ]);
                
                Log::info("WLCMS Auto-Activation: Content '{$content->title}' (ID: {$content->id}) activated at {$now}");
            }
            
            $this->line("  ✅ Activated: {$content->title} (ID: {$content->id})");
            $activated++;
        }

        // Find content to deactivate
        $toDeactivate = ContentItem::where('auto_deactivate', true)
            ->where('status', 'published')
            ->whereNotNull('deactivation_date')
            ->where('deactivation_date', '<=', $now)
            ->get();

        foreach ($toDeactivate as $content) {
            if (!$isDryRun) {
                $content->update([
                    'status' => 'archived',
                ]);
                
                Log::info("WLCMS Auto-Deactivation: Content '{$content->title}' (ID: {$content->id}) deactivated at {$now}");
            }
            
            $this->line("  🔽 Deactivated: {$content->title} (ID: {$content->id})");
            $deactivated++;
        }

        // Summary
        $this->newLine();
        $this->info('📊 Summary:');
        $this->table(
            ['Action', 'Count'],
            [
                ['Activated', $activated],
                ['Deactivated', $deactivated],
                ['Total Processed', $activated + $deactivated],
            ]
        );

        if ($activated + $deactivated === 0) {
            $this->info('✅ No content ready for activation/deactivation');
        } else {
            if ($isDryRun) {
                $this->warn('⚠️  Dry run complete - no changes were made');
            } else {
                $this->info('✅ Content activation check complete');
            }
        }

        return Command::SUCCESS;
    }
}
