<?php

namespace App\Console\Commands;

use App\Events\DashboardDataUpdated;
use App\Services\DashboardRealtimeService;
use Illuminate\Console\Command;

class TriggerDashboardUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:update {--filters=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger a real-time dashboard update';

    protected $realtimeService;

    public function __construct(DashboardRealtimeService $realtimeService)
    {
        parent::__construct();
        $this->realtimeService = $realtimeService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Triggering dashboard update...');

        try {
            // Parse filters if provided
            $filters = [];
            if ($this->option('filters')) {
                $filters = json_decode($this->option('filters'), true) ?? [];
            }

            // Get real-time data
            $data = $this->realtimeService->getRealtimeData($filters);

            // Broadcast the update
            broadcast(new DashboardDataUpdated($data, $filters));

            $this->info('Dashboard update triggered successfully!');
            $this->line('Data timestamp: ' . $data['lastUpdated']);

        } catch (\Exception $e) {
            $this->error('Failed to trigger dashboard update: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
