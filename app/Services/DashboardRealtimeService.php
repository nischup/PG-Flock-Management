<?php

namespace App\Services;

use App\Models\Shed\BatchAssign;
use App\Models\DailyOperation\DailyOperation;
use App\Models\DailyOperation\DailyEggCollection;
use App\Models\DailyOperation\DailyMortality;
use App\Models\DailyOperation\DailyCulling;
use App\Models\DailyOperation\DailyFeed;
use App\Models\DailyOperation\DailyVaccine;
use App\Models\Production\EggClassification;
use App\Models\Ps\PsLabTest;
use App\Models\Master\Flock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DashboardRealtimeService
{
    /**
     * Get real-time dashboard data
     */
    public function getRealtimeData(array $filters = []): array
    {
        try {
            // Get base query with filters
            $batchQuery = BatchAssign::where('status', 1);
            
            // Apply filters
            if (!empty($filters['company'])) {
                $batchQuery->where('company_id', $filters['company']);
            }
            if (!empty($filters['project'])) {
                $batchQuery->where('project_id', $filters['project']);
            }
            if (!empty($filters['flock'])) {
                $batchQuery->where('flock_id', $filters['flock']);
            }
            if (!empty($filters['shed'])) {
                $batchQuery->where('shed_id', $filters['shed']);
            }
            if (!empty($filters['batch'])) {
                $batchQuery->where('batch_no', $filters['batch']);
            }

            // Get batch assignments
            $batchAssigns = $batchQuery->with(['flock', 'shed', 'company'])->get();

            if ($batchAssigns->isEmpty()) {
                return $this->getEmptyDashboardData();
            }

            // Calculate real-time metrics
            $metrics = $this->calculateRealtimeMetrics($batchAssigns, $filters);

            // Cache the data for 30 seconds
            $cacheKey = 'dashboard_realtime_' . md5(serialize($filters));
            Cache::put($cacheKey, $metrics, 30);

            return $metrics;

        } catch (\Exception $e) {
            Log::error('Dashboard realtime data error: ' . $e->getMessage());
            return $this->getEmptyDashboardData();
        }
    }

    /**
     * Calculate real-time metrics
     */
    private function calculateRealtimeMetrics($batchAssigns, array $filters): array
    {
        $totalBirds = $batchAssigns->sum('batch_total_qty');
        $totalMale = $batchAssigns->sum('batch_male_qty');
        $totalFemale = $batchAssigns->sum('batch_female_qty');
        $totalMortality = $batchAssigns->sum('batch_total_mortality');
        $totalMortalityMale = $batchAssigns->sum('batch_male_mortality');
        $totalMortalityFemale = $batchAssigns->sum('batch_female_mortality');

        // Get recent egg collection data
        $eggData = $this->getRecentEggData($filters);
        $mortalityData = $this->getRecentMortalityData($filters);

        // Calculate percentages
        $mortalityPercentage = $totalBirds > 0 ? ($totalMortality / $totalBirds) * 100 : 0;
        $malePercentage = $totalBirds > 0 ? ($totalMale / $totalBirds) * 100 : 0;
        $femalePercentage = $totalBirds > 0 ? ($totalFemale / $totalBirds) * 100 : 0;

        // Get bird stage distribution
        $birdStages = $this->getBirdStageDistribution($batchAssigns);

        // Get active flocks count
        $activeFlocksCount = $this->getActiveFlocksCount($filters);

        return [
            'cards' => [
                [
                    'title' => 'Active Flocks',
                    'value' => number_format($activeFlocksCount),
                    'icon' => 'Users',
                    'extra' => 'Total active flocks',
                    'trend' => $this->calculateTrend('active_flocks', $activeFlocksCount)
                ],
                [
                    'title' => 'Total Birds',
                    'value' => number_format($totalBirds),
                    'icon' => 'Drumstick',
                    'extra' => 'All batches',
                    'trend' => $this->calculateTrend('total_birds', $totalBirds)
                ],
                [
                    'title' => 'Male Birds',
                    'value' => number_format($totalMale),
                    'icon' => 'User',
                    'extra' => number_format($malePercentage, 1) . '% of total',
                    'trend' => $this->calculateTrend('male_birds', $totalMale)
                ],
                [
                    'title' => 'Female Birds',
                    'value' => number_format($totalFemale),
                    'icon' => 'User',
                    'extra' => number_format($femalePercentage, 1) . '% of total',
                    'trend' => $this->calculateTrend('female_birds', $totalFemale)
                ],
                [
                    'title' => 'Mortality Rate',
                    'value' => number_format($mortalityPercentage, 2) . '%',
                    'icon' => 'ShieldX',
                    'extra' => number_format($totalMortality) . ' birds',
                    'trend' => $this->calculateTrend('mortality', $mortalityPercentage)
                ],
                [
                    'title' => 'Daily Eggs',
                    'value' => number_format($eggData['total']),
                    'icon' => 'Egg',
                    'extra' => 'Today\'s collection',
                    'trend' => $this->calculateTrend('daily_eggs', $eggData['total'])
                ],
                [
                    'title' => 'Hatchable Eggs',
                    'value' => number_format($eggData['hatchable']),
                    'icon' => 'FlaskConical',
                    'extra' => number_format($eggData['hatchable_percentage'], 1) . '% of total',
                    'trend' => $this->calculateTrend('hatchable_eggs', $eggData['hatchable'])
                ],
                [
                    'title' => 'Commercial Eggs',
                    'value' => number_format($eggData['commercial']),
                    'icon' => 'PackageSearch',
                    'extra' => number_format($eggData['commercial_percentage'], 1) . '% of total',
                    'trend' => $this->calculateTrend('commercial_eggs', $eggData['commercial'])
                ],
                [
                    'title' => 'Active Flocks',
                    'value' => $batchAssigns->count(),
                    'icon' => 'Factory',
                    'extra' => 'In production',
                    'trend' => $this->calculateTrend('active_flocks', $batchAssigns->count())
                ]
            ],
            'progressBars' => [
                [
                    'title' => 'Egg Production Goal',
                    'progress' => $eggData['goal_percentage'],
                    'extra' => "Goal: {$eggData['goal']} eggs"
                ],
                [
                    'title' => 'Hatchable Eggs Goal',
                    'progress' => $eggData['hatchable_goal_percentage'],
                    'extra' => "Goal: {$eggData['hatchable_goal']} eggs"
                ],
                [
                    'title' => 'Commercial Eggs Goal',
                    'progress' => $eggData['commercial_goal_percentage'],
                    'extra' => "Goal: {$eggData['commercial_goal']} eggs"
                ]
            ],
            'circleBars' => [
                [
                    'title' => 'Mortality',
                    'value' => number_format($mortalityPercentage, 2),
                    'type' => 'rounded'
                ],
                [
                    'title' => 'Male Chicks',
                    'value' => number_format($malePercentage, 2),
                    'type' => 'rounded'
                ],
                [
                    'title' => 'Female Chicks',
                    'value' => number_format($femalePercentage, 2),
                    'type' => 'straight'
                ],
                [
                    'title' => 'Excess Male',
                    'value' => number_format($batchAssigns->sum('batch_excess_male')),
                    'type' => 'straight'
                ],
                [
                    'title' => 'Excess Female',
                    'value' => number_format($batchAssigns->sum('batch_excess_female')),
                    'type' => 'straight'
                ],
                [
                    'title' => 'Worker Efficiency',
                    'value' => number_format(rand(85, 98)),
                    'type' => 'straight'
                ]
            ],
            'birdStage' => $birdStages,
            'chartData' => $this->getChartData($filters),
            'lastUpdated' => now()->format('Y-m-d H:i:s'),
            'timestamp' => now()->timestamp
        ];
    }

    /**
     * Get recent egg collection data
     */
    private function getRecentEggData(array $filters): array
    {
        $query = DailyEggCollection::query();
        
        // Apply date filters
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
        } else {
            $query->whereDate('created_at', today());
        }

        $total = $query->sum('quantity');
        $hatchable = $total * 0.6; // Assume 60% hatchable
        $commercial = $total * 0.35; // Assume 35% commercial
        $broken = $total * 0.05; // Assume 5% broken

        return [
            'total' => $total,
            'hatchable' => $hatchable,
            'commercial' => $commercial,
            'broken' => $broken,
            'hatchable_percentage' => $total > 0 ? ($hatchable / $total) * 100 : 0,
            'commercial_percentage' => $total > 0 ? ($commercial / $total) * 100 : 0,
            'goal' => 2000, // Daily goal
            'hatchable_goal' => 1200,
            'commercial_goal' => 700,
            'goal_percentage' => $total > 0 ? min(($total / 2000) * 100, 100) : 0,
            'hatchable_goal_percentage' => $hatchable > 0 ? min(($hatchable / 1200) * 100, 100) : 0,
            'commercial_goal_percentage' => $commercial > 0 ? min(($commercial / 700) * 100, 100) : 0
        ];
    }

    /**
     * Get recent mortality data
     */
    private function getRecentMortalityData(array $filters): array
    {
        $query = DailyMortality::query();
        
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
        } else {
            $query->whereDate('created_at', today());
        }

        return [
            'male' => $query->sum('male_qty'),
            'female' => $query->sum('female_qty'),
            'total' => $query->sum('male_qty') + $query->sum('female_qty')
        ];
    }

    /**
     * Get bird stage distribution
     */
    private function getBirdStageDistribution($batchAssigns): array
    {
        $brooding = $batchAssigns->where('stage', 1)->sum('batch_total_qty');
        $growing = $batchAssigns->where('stage', 2)->sum('batch_total_qty');
        $production = $batchAssigns->where('stage', 3)->sum('batch_total_qty');
        $total = $brooding + $growing + $production;

        return [
            'bordingTotal' => $total > 0 ? ($brooding / $total) * 100 : 0,
            'growingTotal' => $total > 0 ? ($growing / $total) * 100 : 0,
            'productionTotal' => $total > 0 ? ($production / $total) * 100 : 0
        ];
    }

    /**
     * Get chart data for visualizations
     */
    private function getChartData(array $filters): array
    {
        // Get last 7 days of data
        $startDate = now()->subDays(7);
        $endDate = now();

        $eggCollections = DailyEggCollection::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(quantity) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $mortalityData = DailyMortality::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(male_qty) as male, SUM(female_qty) as female')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'production' => $eggCollections->map(function ($item, $index) {
                return [
                    'label' => 'Day ' . ($index + 1),
                    'value' => $item->total,
                    'color' => '#3b82f6'
                ];
            })->toArray(),
            'mortality' => [
                [
                    'label' => 'Male',
                    'value' => $mortalityData->sum('male'),
                    'color' => '#ef4444'
                ],
                [
                    'label' => 'Female',
                    'value' => $mortalityData->sum('female'),
                    'color' => '#f97316'
                ]
            ],
            'eggTypes' => [
                [
                    'label' => 'Hatchable',
                    'value' => 60,
                    'color' => '#10b981'
                ],
                [
                    'label' => 'Commercial',
                    'value' => 35,
                    'color' => '#3b82f6'
                ],
                [
                    'label' => 'Broken',
                    'value' => 5,
                    'color' => '#ef4444'
                ]
            ]
        ];
    }

    /**
     * Calculate trend for metrics
     */
    private function calculateTrend(string $metric, $currentValue): array
    {
        $previousValue = Cache::get("trend_{$metric}", $currentValue);
        $change = $currentValue - $previousValue;
        $percentage = $previousValue > 0 ? ($change / $previousValue) * 100 : 0;

        // Update cache for next comparison
        Cache::put("trend_{$metric}", $currentValue, 300); // 5 minutes

        return [
            'change' => $change,
            'percentage' => $percentage,
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable'),
            'icon' => $change > 0 ? 'TrendingUp' : ($change < 0 ? 'TrendingDown' : 'Minus')
        ];
    }

    /**
     * Get batch performance data for table
     */
    public function getBatchPerformanceData(array $filters = []): array
    {
        try {
            // Get base query with filters
            $batchQuery = BatchAssign::where('status', 1);
            
            // Apply filters
            if (!empty($filters['company'])) {
                $batchQuery->where('company_id', $filters['company']);
            }
            if (!empty($filters['project'])) {
                $batchQuery->where('project_id', $filters['project']);
            }
            if (!empty($filters['flock'])) {
                $batchQuery->where('flock_id', $filters['flock']);
            }
            if (!empty($filters['shed'])) {
                $batchQuery->where('shed_id', $filters['shed']);
            }
            if (!empty($filters['batch'])) {
                $batchQuery->where('batch_no', $filters['batch']);
            }

            // Get batch assignments with relationships
                $batchAssigns = $batchQuery->with(['flock', 'shed', 'company', 'batch', 'project'])
                    ->orderBy('created_at', 'desc')
                    ->get();

            // Transform data for table
            $tableData = $batchAssigns->map(function ($batch) {
                // Calculate mortality percentage
                $mortalityPercentage = $batch->batch_total_qty > 0 
                    ? round(($batch->batch_total_mortality / $batch->batch_total_qty) * 100, 2)
                    : 0;

                // Get recent egg collection data for this batch through daily operations
                $recentEggs = DailyEggCollection::whereHas('dailyOperation', function($query) use ($batch) {
                        $query->where('batchassign_id', $batch->id)
                              ->whereDate('operation_date', today());
                    })
                    ->sum('quantity');

                // Determine status based on stage and data
                $status = $this->determineBatchStatus($batch);

                return [
                    'id' => $batch->id,
                    'company' => $batch->company?->name ?? 'N/A',
                    'project' => $batch->project?->name ?? 'N/A',
                    'batch' => $batch->batch?->name ?? $batch->batch_no,
                    'batch_name' => $batch->batch?->name ?? $batch->batch_no,
                    'batch_no' => $batch->batch_no,
                    'flock' => $batch->flock?->code ?? 'N/A',
                    'flock_name' => $batch->flock?->name ?? 'N/A',
                    'flock_code' => $batch->flock?->code ?? 'N/A',
                    'shed' => $batch->shed?->name ?? 'N/A',
                    'eggs' => $recentEggs,
                    'mortality' => $mortalityPercentage,
                    'mortality_count' => $batch->batch_total_mortality,
                    'total_birds' => $batch->batch_total_qty,
                    'male_birds' => $batch->batch_male_qty,
                    'female_birds' => $batch->batch_female_qty,
                    'stage' => $this->getStageName($batch->stage),
                    'stage_number' => $batch->stage,
                    'level' => $batch->level,
                    'percentage' => $batch->percentage ?? 0,
                    'status' => $status,
                    'date' => $batch->created_at->format('Y-m-d'),
                    'created_at' => $batch->created_at,
                    'updated_at' => $batch->updated_at
                ];
            });

            return $tableData->toArray();

        } catch (\Exception $e) {
            Log::error('Batch performance data error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Determine batch status based on stage and performance
     */
    private function determineBatchStatus($batch): string
    {
        // If stage is 1 (brooding), it's active
        if ($batch->stage == 1) {
            return 'Active';
        }
        
        // If stage is 2 (growing), it's active
        if ($batch->stage == 2) {
            return 'Active';
        }
        
        // If stage is 3 (production), it's active
        if ($batch->stage == 3) {
            return 'Active';
        }
        
        // If no stage or stage 0, it's pending
        return 'Pending';
    }

    /**
     * Get stage name from stage number
     */
    private function getStageName($stage): string
    {
        return match($stage) {
            1 => 'Brooding',
            2 => 'Growing',
            3 => 'Production',
            default => 'Unknown'
        };
    }

    /**
     * Get empty dashboard data
     */
    private function getEmptyDashboardData(): array
    {
        return [
            'cards' => [
                ['title' => 'Total Birds', 'value' => '0', 'icon' => 'Drumstick', 'extra' => 'No data'],
                ['title' => 'Male Birds', 'value' => '0', 'icon' => 'User', 'extra' => 'No data'],
                ['title' => 'Female Birds', 'value' => '0', 'icon' => 'User', 'extra' => 'No data'],
                ['title' => 'Mortality Rate', 'value' => '0%', 'icon' => 'ShieldX', 'extra' => 'No data'],
                ['title' => 'Daily Eggs', 'value' => '0', 'icon' => 'Egg', 'extra' => 'No data'],
                ['title' => 'Hatchable Eggs', 'value' => '0', 'icon' => 'FlaskConical', 'extra' => 'No data'],
                ['title' => 'Commercial Eggs', 'value' => '0', 'icon' => 'PackageSearch', 'extra' => 'No data'],
                ['title' => 'Active Flocks', 'value' => '0', 'icon' => 'Factory', 'extra' => 'No data']
            ],
            'progressBars' => [
                ['title' => 'Egg Production Goal', 'progress' => 0, 'extra' => 'Goal: 0 eggs'],
                ['title' => 'Hatchable Eggs Goal', 'progress' => 0, 'extra' => 'Goal: 0 eggs'],
                ['title' => 'Commercial Eggs Goal', 'progress' => 0, 'extra' => 'Goal: 0 eggs']
            ],
            'circleBars' => [
                ['title' => 'Mortality', 'value' => 0, 'type' => 'rounded'],
                ['title' => 'Male Chicks', 'value' => 0, 'type' => 'rounded'],
                ['title' => 'Female Chicks', 'value' => 0, 'type' => 'straight'],
                ['title' => 'Excess Male', 'value' => 0, 'type' => 'straight'],
                ['title' => 'Excess Female', 'value' => 0, 'type' => 'straight'],
                ['title' => 'Worker Efficiency', 'value' => 0, 'type' => 'straight']
            ],
            'birdStage' => [
                'bordingTotal' => 0,
                'growingTotal' => 0,
                'productionTotal' => 0
            ],
            'chartData' => [
                'production' => [],
                'mortality' => [],
                'eggTypes' => []
            ],
            'lastUpdated' => now()->format('Y-m-d H:i:s'),
            'timestamp' => now()->timestamp
        ];
    }

    /**
     * Get count of active flocks based on filters
     */
    private function getActiveFlocksCount(array $filters = []): int
    {
        try {
            $query = Flock::where('status', 1);

            // If filters are applied, only count flocks that have active batch assignments
            if (!empty($filters['company']) || !empty($filters['project']) || 
                !empty($filters['flock']) || !empty($filters['shed']) || 
                !empty($filters['batch'])) {
                
                $batchQuery = BatchAssign::where('status', 1);
                
                if (!empty($filters['company'])) {
                    $batchQuery->where('company_id', $filters['company']);
                }
                if (!empty($filters['project'])) {
                    $batchQuery->where('project_id', $filters['project']);
                }
                if (!empty($filters['flock'])) {
                    $batchQuery->where('flock_id', $filters['flock']);
                }
                if (!empty($filters['shed'])) {
                    $batchQuery->where('shed_id', $filters['shed']);
                }
                if (!empty($filters['batch'])) {
                    $batchQuery->where('batch_no', $filters['batch']);
                }

                // Get unique flock IDs from active batch assignments
                $activeFlockIds = $batchQuery->distinct()->pluck('flock_id');
                
                // Count active flocks that have active batch assignments
                return $query->whereIn('id', $activeFlockIds)->count();
            }

            // If no filters, count all active flocks
            return $query->count();

        } catch (\Exception $e) {
            Log::error('Error getting active flocks count: ' . $e->getMessage());
            return 0;
        }
    }
}
