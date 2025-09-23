<?php

namespace App\Services;

use App\Models\DailyOperation\DailyEggCollection;
use App\Models\DailyOperation\DailyMortality;
use App\Models\DailyOperation\DailyOperation;
use App\Models\Master\Flock;
use App\Models\MovementAdjustment;
use App\Models\Shed\BatchAssign;
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
            if (! empty($filters['company'])) {
                $batchQuery->where('company_id', $filters['company']);
            }
            if (! empty($filters['project'])) {
                $batchQuery->where('project_id', $filters['project']);
            }
            if (! empty($filters['flock'])) {
                $batchQuery->where('flock_id', $filters['flock']);
            }
            if (! empty($filters['shed'])) {
                $batchQuery->where('shed_id', $filters['shed']);
            }
            if (! empty($filters['batch'])) {
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
            $cacheKey = 'dashboard_realtime_'.md5(serialize($filters));
            Cache::put($cacheKey, $metrics, 30);

            return $metrics;

        } catch (\Exception $e) {
            Log::error('Dashboard realtime data error: '.$e->getMessage());

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

        // Use combined mortality data from both DailyMortality and MovementAdjustment
        $combinedMortalityMale = $mortalityData['male'];
        $combinedMortalityFemale = $mortalityData['female'];
        $combinedTotalMortality = $mortalityData['total'];

        // For mortality rate calculation, use combined data from DailyMortality and MovementAdjustment tables
        // This gives us the actual mortality rate based on daily operations and movement adjustments
        $mortalityPercentage = $totalBirds > 0 ? ($combinedTotalMortality / $totalBirds) * 100 : 0;
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
                    'trend' => $this->calculateTrend('active_flocks', $activeFlocksCount),
                ],
                [
                    'title' => 'Total Birds',
                    'value' => number_format($totalBirds),
                    'icon' => 'Drumstick',
                    'extra' => 'All batches',
                    'trend' => $this->calculateTrend('total_birds', $totalBirds),
                ],
                [
                    'title' => 'Male Birds',
                    'value' => number_format($totalMale),
                    'icon' => 'User',
                    'extra' => number_format($malePercentage, 1).'% of total',
                    'trend' => $this->calculateTrend('male_birds', $totalMale),
                ],
                [
                    'title' => 'Female Birds',
                    'value' => number_format($totalFemale),
                    'icon' => 'User',
                    'extra' => number_format($femalePercentage, 1).'% of total',
                    'trend' => $this->calculateTrend('female_birds', $totalFemale),
                ],
                [
                    'title' => 'Mortality Rate',
                    'value' => number_format($mortalityPercentage, 2).'%',
                    'icon' => 'ShieldX',
                    'extra' => number_format($combinedTotalMortality).' birds (Daily: '.$mortalityData['daily_male'].'M/'.$mortalityData['daily_female'].'F, Movement: '.$mortalityData['movement_male'].'M/'.$mortalityData['movement_female'].'F)',
                    'trend' => $this->calculateTrend('mortality', $mortalityPercentage),
                ],
                [
                    'title' => 'Daily Eggs',
                    'value' => number_format($eggData['total']),
                    'icon' => 'Egg',
                    'extra' => 'Today\'s collection',
                    'trend' => $this->calculateTrend('daily_eggs', $eggData['total']),
                ],
                [
                    'title' => 'Hatchable Eggs',
                    'value' => number_format($eggData['hatchable']),
                    'icon' => 'FlaskConical',
                    'extra' => number_format($eggData['hatchable_percentage'], 1).'% of total',
                    'trend' => $this->calculateTrend('hatchable_eggs', $eggData['hatchable']),
                ],
                [
                    'title' => 'Commercial Eggs',
                    'value' => number_format($eggData['commercial']),
                    'icon' => 'PackageSearch',
                    'extra' => number_format($eggData['commercial_percentage'], 1).'% of total',
                    'trend' => $this->calculateTrend('commercial_eggs', $eggData['commercial']),
                ],
            ],
            'progressBars' => [
                [
                    'title' => 'Egg Production Goal',
                    'progress' => $eggData['goal_percentage'],
                    'extra' => "Goal: {$eggData['goal']} eggs",
                ],
                [
                    'title' => 'Hatchable Eggs Goal',
                    'progress' => $eggData['hatchable_goal_percentage'],
                    'extra' => "Goal: {$eggData['hatchable_goal']} eggs",
                ],
                [
                    'title' => 'Commercial Eggs Goal',
                    'progress' => $eggData['commercial_goal_percentage'],
                    'extra' => "Goal: {$eggData['commercial_goal']} eggs",
                ],
            ],
            'circleBars' => [
                [
                    'title' => 'Mortality',
                    'value' => round($mortalityPercentage, 2),
                    'type' => 'rounded',
                ],
                [
                    'title' => 'Male Chicks',
                    'value' => round($malePercentage, 2),
                    'type' => 'rounded',
                ],
                [
                    'title' => 'Female Chicks',
                    'value' => round($femalePercentage, 2),
                    'type' => 'straight',
                ],
                [
                    'title' => 'Excess Male',
                    'value' => (int) $batchAssigns->sum('batch_excess_male'),
                    'type' => 'straight',
                ],
                [
                    'title' => 'Excess Female',
                    'value' => (int) $batchAssigns->sum('batch_excess_female'),
                    'type' => 'straight',
                ],
                [
                    'title' => 'Worker Efficiency',
                    'value' => rand(85, 98),
                    'type' => 'straight',
                ],
            ],
            'birdStage' => $birdStages,
            'chartData' => $this->getChartData($filters),
            'lastUpdated' => now()->format('Y-m-d H:i:s'),
            'timestamp' => now()->timestamp,
        ];
    }

    /**
     * Get recent egg collection data
     */
    private function getRecentEggData(array $filters): array
    {
        $query = DailyEggCollection::query();

        // Apply date filters
        if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            $query->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
        } else {
            // If no specific date filter, show data from the last 7 days to include recent data
            $query->where('created_at', '>=', now()->subDays(7));
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
            'commercial_goal_percentage' => $commercial > 0 ? min(($commercial / 700) * 100, 100) : 0,
        ];
    }

    /**
     * Get mortality data from both DailyMortality and MovementAdjustment tables
     */
    private function getRecentMortalityData(array $filters): array
    {
        // Get mortality from DailyMortality table - get ALL data, not just recent
        $dailyMortalityQuery = DailyMortality::query();

        if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            $dailyMortalityQuery->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
        }
        // If no date filters, get all mortality data from DailyMortality

        $dailyMale = $dailyMortalityQuery->sum('male_qty');
        $dailyFemale = $dailyMortalityQuery->sum('female_qty');

        // Get mortality from MovementAdjustment table (type = 1 for mortality) - get ALL data
        $movementQuery = MovementAdjustment::where('type', 1);

        if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            $movementQuery->whereBetween('date', [$filters['date_from'], $filters['date_to']]);
        }
        // If no date filters, get all mortality data from MovementAdjustment

        $movementMale = $movementQuery->sum('male_qty');
        $movementFemale = $movementQuery->sum('female_qty');

        // Combine data from both sources
        $totalMale = $dailyMale + $movementMale;
        $totalFemale = $dailyFemale + $movementFemale;

        return [
            'male' => $totalMale,
            'female' => $totalFemale,
            'total' => $totalMale + $totalFemale,
            'daily_male' => $dailyMale,
            'daily_female' => $dailyFemale,
            'movement_male' => $movementMale,
            'movement_female' => $movementFemale,
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
            'productionTotal' => $total > 0 ? ($production / $total) * 100 : 0,
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

        // Get mortality data from DailyMortality table
        $dailyMortalityData = DailyMortality::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(male_qty) as male, SUM(female_qty) as female')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get mortality data from MovementAdjustment table (type = 1 for mortality)
        $movementMortalityData = MovementAdjustment::where('type', 1) // 1 = Mortality
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->selectRaw('DATE(date) as date, SUM(male_qty) as male, SUM(female_qty) as female')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Combine mortality data from both sources
        $totalMaleMortality = $dailyMortalityData->sum('male') + $movementMortalityData->sum('male');
        $totalFemaleMortality = $dailyMortalityData->sum('female') + $movementMortalityData->sum('female');

        return [
            'production' => $eggCollections->map(function ($item, $index) {
                return [
                    'label' => 'Day '.($index + 1),
                    'value' => $item->total,
                    'color' => '#3b82f6',
                ];
            })->toArray(),
            'mortality' => [
                [
                    'label' => 'Male',
                    'value' => $totalMaleMortality,
                    'color' => '#ef4444',
                ],
                [
                    'label' => 'Female',
                    'value' => $totalFemaleMortality,
                    'color' => '#f97316',
                ],
            ],
            'eggTypes' => [
                [
                    'label' => 'Hatchable',
                    'value' => 60,
                    'color' => '#10b981',
                ],
                [
                    'label' => 'Commercial',
                    'value' => 35,
                    'color' => '#3b82f6',
                ],
                [
                    'label' => 'Broken',
                    'value' => 5,
                    'color' => '#ef4444',
                ],
            ],
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
            'icon' => $change > 0 ? 'TrendingUp' : ($change < 0 ? 'TrendingDown' : 'Minus'),
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
            if (! empty($filters['company'])) {
                $batchQuery->where('company_id', $filters['company']);
            }
            if (! empty($filters['project'])) {
                $batchQuery->where('project_id', $filters['project']);
            }
            if (! empty($filters['flock'])) {
                $batchQuery->where('flock_id', $filters['flock']);
            }
            if (! empty($filters['shed'])) {
                $batchQuery->where('shed_id', $filters['shed']);
            }
            if (! empty($filters['batch'])) {
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
                $recentEggs = DailyEggCollection::whereHas('dailyOperation', function ($query) use ($batch) {
                    $query->where('batchassign_id', $batch->id)
                        ->whereDate('operation_date', today());
                })
                    ->sum('quantity');

                // Calculate age from shed receive date
                $startDate = $batch->shedReceive?->created_at ?? $batch->created_at;
                $age = '0 weeks 0 days';
                if ($startDate) {
                    $days = $startDate->diffInDays(now());
                    $weeks = floor($days / 7);
                    $remainingDays = $days % 7;
                    $age = "{$weeks} weeks {$remainingDays} days";
                }

                // Determine status based on stage and data
                $status = $this->determineBatchStatus($batch);

                return [
                    'id' => $batch->id,
                    'company' => $batch->company?->name ?? 'N/A',
                    'project' => $batch->project?->name ?? 'N/A',
                    'batch' => $batch->batch?->name ?? $batch->batch_no,
                    'batch_name' => $batch->batch?->name ?? $batch->batch_no,
                    'batch_no' => $batch->batch_no,
                    'age' => $age,
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
                    'updated_at' => $batch->updated_at,
                ];
            });

            return $tableData->toArray();

        } catch (\Exception $e) {
            Log::error('Batch performance data error: '.$e->getMessage());

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
        return match ($stage) {
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
                ['title' => 'Active Flocks', 'value' => '0', 'icon' => 'Factory', 'extra' => 'No data'],
            ],
            'progressBars' => [
                ['title' => 'Egg Production Goal', 'progress' => 0, 'extra' => 'Goal: 0 eggs'],
                ['title' => 'Hatchable Eggs Goal', 'progress' => 0, 'extra' => 'Goal: 0 eggs'],
                ['title' => 'Commercial Eggs Goal', 'progress' => 0, 'extra' => 'Goal: 0 eggs'],
            ],
            'circleBars' => [
                ['title' => 'Mortality', 'value' => 0, 'type' => 'rounded'],
                ['title' => 'Male Chicks', 'value' => 0, 'type' => 'rounded'],
                ['title' => 'Female Chicks', 'value' => 0, 'type' => 'straight'],
                ['title' => 'Excess Male', 'value' => 0, 'type' => 'straight'],
                ['title' => 'Excess Female', 'value' => 0, 'type' => 'straight'],
                ['title' => 'Worker Efficiency', 'value' => 0, 'type' => 'straight'],
            ],
            'birdStage' => [
                'bordingTotal' => 0,
                'growingTotal' => 0,
                'productionTotal' => 0,
            ],
            'chartData' => [
                'production' => [],
                'mortality' => [],
                'eggTypes' => [],
            ],
            'lastUpdated' => now()->format('Y-m-d H:i:s'),
            'timestamp' => now()->timestamp,
        ];
    }

    /**
     * Get detailed flock information for modal
     */
    public function getFlockDetails(array $filters = []): array
    {
        try {
            // Get base query for active flocks
            $flockQuery = Flock::where('status', 1);

            // If filters are applied, only get flocks that have active batch assignments
            if (! empty($filters['company']) || ! empty($filters['project']) ||
                ! empty($filters['flock']) || ! empty($filters['shed']) ||
                ! empty($filters['batch'])) {

                $batchQuery = BatchAssign::where('status', 1);

                if (! empty($filters['company'])) {
                    $batchQuery->where('company_id', $filters['company']);
                }
                if (! empty($filters['project'])) {
                    $batchQuery->where('project_id', $filters['project']);
                }
                if (! empty($filters['flock'])) {
                    $batchQuery->where('flock_id', $filters['flock']);
                }
                if (! empty($filters['shed'])) {
                    $batchQuery->where('shed_id', $filters['shed']);
                }
                if (! empty($filters['batch'])) {
                    $batchQuery->where('batch_no', $filters['batch']);
                }

                // Get unique flock IDs from active batch assignments
                $activeFlockIds = $batchQuery->distinct()->pluck('flock_id');

                // Get flocks that have active batch assignments
                $flockQuery->whereIn('id', $activeFlockIds);
            }

            // Get flocks with their related data
            $flocks = $flockQuery->with([
                'batchAssigns' => function ($query) {
                    $query->where('status', 1)
                        ->with(['company', 'shed', 'batch', 'project']);
                },
            ])->get();

            // Transform data for detailed view
            $flockDetails = $flocks->map(function ($flock) use ($filters) {
                // Get all batch assignments for this flock
                $batchAssigns = $flock->batchAssigns;

                // Calculate statistics
                $totalBirds = $batchAssigns->sum('batch_total_qty');
                $totalMale = $batchAssigns->sum('batch_male_qty');
                $totalFemale = $batchAssigns->sum('batch_female_qty');
                $totalMortality = $batchAssigns->sum('batch_total_mortality');
                $totalMortalityMale = $batchAssigns->sum('batch_male_mortality');
                $totalMortalityFemale = $batchAssigns->sum('batch_female_mortality');

                // Get recent daily operations for this flock
                $recentOperations = $this->getRecentOperationsForFlock($flock->id, $filters);

                // Get egg collection data for this flock
                $eggData = $this->getEggDataForFlock($flock->id, $filters);

                // Get mortality data for this flock
                $mortalityData = $this->getMortalityDataForFlock($flock->id, $filters);

                // Calculate mortality percentage
                $mortalityPercentage = $totalBirds > 0 ? ($totalMortality / $totalBirds) * 100 : 0;

                // Get unique companies, projects, and sheds for this flock
                $companies = $batchAssigns->pluck('company')->unique('id')->filter()->values();
                $projects = $batchAssigns->pluck('project')->unique('id')->filter()->values();
                $sheds = $batchAssigns->pluck('shed')->unique('id')->filter()->values();

                return [
                    'id' => $flock->id,
                    'code' => $flock->code,
                    'name' => $flock->name,
                    'status' => $flock->status,
                    'created_at' => $flock->created_at,
                    'updated_at' => $flock->updated_at,

                    // Statistics
                    'total_birds' => $totalBirds,
                    'male_birds' => $totalMale,
                    'female_birds' => $totalFemale,
                    'total_mortality' => $totalMortality,
                    'male_mortality' => $totalMortalityMale,
                    'female_mortality' => $totalMortalityFemale,
                    'mortality_percentage' => round($mortalityPercentage, 2),

                    // Related entities
                    'companies' => $companies->map(function ($company) {
                        return [
                            'name' => $company->name,
                        ];
                    }),
                    'projects' => $projects->map(function ($project) {
                        return [
                            'name' => $project->name,
                        ];
                    }),
                    'sheds' => $sheds->map(function ($shed) {
                        return [
                            'name' => $shed->name,
                        ];
                    }),

                    // Batch assignments
                    'batch_assignments' => $batchAssigns->map(function ($batch) {
                        return [
                            'id' => $batch->id,
                            'batch_no' => $batch->batch_no,
                            'batch_name' => $batch->batch?->name ?? 'N/A',
                            'transaction_no' => $batch->transaction_no,
                            'job_no' => $batch->job_no,
                            'stage' => $this->getStageName($batch->stage),
                            'stage_number' => $batch->stage,
                            'level' => $batch->level,
                            'percentage' => $batch->percentage ?? 0,
                            'total_qty' => $batch->batch_total_qty,
                            'male_qty' => $batch->batch_male_qty,
                            'female_qty' => $batch->batch_female_qty,
                            'total_mortality' => $batch->batch_total_mortality,
                            'male_mortality' => $batch->batch_male_mortality,
                            'female_mortality' => $batch->batch_female_mortality,
                            'excess_male' => $batch->batch_excess_male,
                            'excess_female' => $batch->batch_excess_female,
                            'shortage_male' => $batch->batch_sortage_male,
                            'shortage_female' => $batch->batch_sortage_female,
                            'company' => $batch->company?->name ?? 'N/A',
                            'project' => $batch->project?->name ?? 'N/A',
                            'shed' => $batch->shed?->name ?? 'N/A',
                            'created_at' => $batch->created_at,
                            'updated_at' => $batch->updated_at,
                        ];
                    }),

                    // Recent activity
                    'recent_operations' => $recentOperations,
                    'egg_data' => $eggData,
                    'mortality_data' => $mortalityData,

                    // PS Receive information (not available in current schema)
                    'ps_receive' => null,
                ];
            });

            return [
                'flocks' => $flockDetails,
                'total_flocks' => $flockDetails->count(),
                'summary' => [
                    'total_birds' => $flockDetails->sum('total_birds'),
                    'total_male' => $flockDetails->sum('male_birds'),
                    'total_female' => $flockDetails->sum('female_birds'),
                    'total_mortality' => $flockDetails->sum('total_mortality'),
                    'average_mortality_percentage' => $flockDetails->avg('mortality_percentage'),
                    'total_batch_assignments' => $flockDetails->sum(function ($flock) {
                        return count($flock['batch_assignments']);
                    }),
                ],
                'last_updated' => now()->format('Y-m-d H:i:s'),
                'timestamp' => now()->timestamp,
            ];

        } catch (\Exception $e) {
            Log::error('Error getting flock details: '.$e->getMessage());

            return [
                'flocks' => [],
                'total_flocks' => 0,
                'summary' => [
                    'total_birds' => 0,
                    'total_male' => 0,
                    'total_female' => 0,
                    'total_mortality' => 0,
                    'average_mortality_percentage' => 0,
                    'total_batch_assignments' => 0,
                ],
                'last_updated' => now()->format('Y-m-d H:i:s'),
                'timestamp' => now()->timestamp,
            ];
        }
    }

    /**
     * Get recent operations for a specific flock
     */
    private function getRecentOperationsForFlock(int $flockId, array $filters): array
    {
        try {
            $query = DailyOperation::where('flock_id', $flockId)
                ->with(['batchAssign', 'creator'])
                ->orderBy('operation_date', 'desc')
                ->limit(10);

            // Apply date filters
            if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
                $query->whereBetween('operation_date', [$filters['date_from'], $filters['date_to']]);
            } else {
                $query->where('operation_date', '>=', now()->subDays(7));
            }

            return $query->get()->map(function ($operation) {
                return [
                    'id' => $operation->id,
                    'operation_date' => $operation->operation_date,
                    'job_no' => $operation->job_no,
                    'transaction_no' => $operation->transaction_no,
                    'stage' => $this->getStageName($operation->stage),
                    'stage_number' => $operation->stage,
                    'created_by' => $operation->creator?->name ?? 'N/A',
                    'created_at' => $operation->created_at,
                ];
            })->toArray();

        } catch (\Exception $e) {
            Log::error('Error getting recent operations for flock: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Get egg data for a specific flock
     */
    private function getEggDataForFlock(int $flockId, array $filters): array
    {
        try {
            $query = DailyEggCollection::whereHas('dailyOperation', function ($q) use ($flockId) {
                $q->where('flock_id', $flockId);
            });

            // Apply date filters
            if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
                $query->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
            } else {
                $query->where('created_at', '>=', now()->subDays(7));
            }

            $total = $query->sum('quantity');
            $hatchable = $total * 0.6; // Assume 60% hatchable
            $commercial = $total * 0.35; // Assume 35% commercial

            return [
                'total' => $total,
                'hatchable' => $hatchable,
                'commercial' => $commercial,
                'hatchable_percentage' => $total > 0 ? ($hatchable / $total) * 100 : 0,
                'commercial_percentage' => $total > 0 ? ($commercial / $total) * 100 : 0,
            ];

        } catch (\Exception $e) {
            Log::error('Error getting egg data for flock: '.$e->getMessage());

            return [
                'total' => 0,
                'hatchable' => 0,
                'commercial' => 0,
                'hatchable_percentage' => 0,
                'commercial_percentage' => 0,
            ];
        }
    }

    /**
     * Get mortality data for a specific flock from both DailyMortality and MovementAdjustment tables
     */
    private function getMortalityDataForFlock(int $flockId, array $filters): array
    {
        try {
            // Get mortality from DailyMortality table for this flock
            $dailyQuery = DailyMortality::whereHas('dailyOperation', function ($q) use ($flockId) {
                $q->where('flock_id', $flockId);
            });

            // Apply date filters
            if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
                $dailyQuery->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
            }
            // If no date filters, get all mortality data for this flock

            $dailyMale = $dailyQuery->sum('male_qty');
            $dailyFemale = $dailyQuery->sum('female_qty');

            // Get mortality from MovementAdjustment table for this flock
            $movementQuery = MovementAdjustment::where('type', 1)
                ->where('flock_id', $flockId);

            // Apply date filters
            if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
                $movementQuery->whereBetween('date', [$filters['date_from'], $filters['date_to']]);
            }
            // If no date filters, get all mortality data for this flock

            $movementMale = $movementQuery->sum('male_qty');
            $movementFemale = $movementQuery->sum('female_qty');

            // Combine data from both sources
            $totalMale = $dailyMale + $movementMale;
            $totalFemale = $dailyFemale + $movementFemale;

            return [
                'male' => $totalMale,
                'female' => $totalFemale,
                'total' => $totalMale + $totalFemale,
                'daily_male' => $dailyMale,
                'daily_female' => $dailyFemale,
                'movement_male' => $movementMale,
                'movement_female' => $movementFemale,
            ];

        } catch (\Exception $e) {
            Log::error('Error getting mortality data for flock: '.$e->getMessage());

            return [
                'male' => 0,
                'female' => 0,
                'total' => 0,
                'daily_male' => 0,
                'daily_female' => 0,
                'movement_male' => 0,
                'movement_female' => 0,
            ];
        }
    }

    /**
     * Get mortality data for a specific flock from DailyMortality table only (for batch calculations)
     */
    private function getDailyMortalityDataForFlock(int $flockId, array $filters): array
    {
        try {
            // Get mortality from DailyMortality table for this flock only
            $dailyQuery = DailyMortality::whereHas('dailyOperation', function ($q) use ($flockId) {
                $q->where('flock_id', $flockId);
            });

            // Apply date filters
            if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
                $dailyQuery->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
            }
            // If no date filters, get all mortality data for this flock

            $dailyMale = $dailyQuery->sum('male_qty');
            $dailyFemale = $dailyQuery->sum('female_qty');

            return [
                'male' => $dailyMale,
                'female' => $dailyFemale,
                'total' => $dailyMale + $dailyFemale,
                'daily_male' => $dailyMale,
                'daily_female' => $dailyFemale,
                'movement_male' => 0,
                'movement_female' => 0,
            ];

        } catch (\Exception $e) {
            Log::error('Error getting daily mortality data for flock: '.$e->getMessage());

            return [
                'male' => 0,
                'female' => 0,
                'total' => 0,
                'daily_male' => 0,
                'daily_female' => 0,
                'movement_male' => 0,
                'movement_female' => 0,
            ];
        }
    }

    /**
     * Get mortality data for a specific batch from DailyMortality table only
     */
    private function getDailyMortalityDataForBatch(int $batchAssignId, array $filters): array
    {
        try {
            // Get mortality from DailyMortality table for this specific batch assignment
            $dailyQuery = DailyMortality::whereHas('dailyOperation', function ($q) use ($batchAssignId) {
                $q->where('batchassign_id', $batchAssignId);
            });

            // Apply date filters
            if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
                $dailyQuery->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
            }
            // If no date filters, get all mortality data for this batch

            $dailyMale = $dailyQuery->sum('male_qty');
            $dailyFemale = $dailyQuery->sum('female_qty');

            return [
                'male' => $dailyMale,
                'female' => $dailyFemale,
                'total' => $dailyMale + $dailyFemale,
                'daily_male' => $dailyMale,
                'daily_female' => $dailyFemale,
                'movement_male' => 0,
                'movement_female' => 0,
            ];

        } catch (\Exception $e) {
            Log::error('Error getting daily mortality data for batch: '.$e->getMessage());

            return [
                'male' => 0,
                'female' => 0,
                'total' => 0,
                'daily_male' => 0,
                'daily_female' => 0,
                'movement_male' => 0,
                'movement_female' => 0,
            ];
        }
    }

    /**
     * Get count of active flocks based on filters
     */
    private function getActiveFlocksCount(array $filters = []): int
    {
        try {
            $query = Flock::where('status', 1);

            // If filters are applied, only count flocks that have active batch assignments
            if (! empty($filters['company']) || ! empty($filters['project']) ||
                ! empty($filters['flock']) || ! empty($filters['shed']) ||
                ! empty($filters['batch'])) {

                $batchQuery = BatchAssign::where('status', 1);

                if (! empty($filters['company'])) {
                    $batchQuery->where('company_id', $filters['company']);
                }
                if (! empty($filters['project'])) {
                    $batchQuery->where('project_id', $filters['project']);
                }
                if (! empty($filters['flock'])) {
                    $batchQuery->where('flock_id', $filters['flock']);
                }
                if (! empty($filters['shed'])) {
                    $batchQuery->where('shed_id', $filters['shed']);
                }
                if (! empty($filters['batch'])) {
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
            Log::error('Error getting active flocks count: '.$e->getMessage());

            return 0;
        }
    }

    /**
     * Get detailed birds information by batch for modal
     */
    public function getBirdsDetails(array $filters = []): array
    {
        try {
            // Get all active batch assignments with their related data
            $batchQuery = BatchAssign::where('status', 1)
                ->with(['flock', 'company', 'shed', 'batch', 'project']);

            // Apply filters
            if (! empty($filters['company'])) {
                $batchQuery->where('company_id', $filters['company']);
            }
            if (! empty($filters['project'])) {
                $batchQuery->where('project_id', $filters['project']);
            }
            if (! empty($filters['flock'])) {
                $batchQuery->where('flock_id', $filters['flock']);
            }
            if (! empty($filters['shed'])) {
                $batchQuery->where('shed_id', $filters['shed']);
            }
            if (! empty($filters['batch'])) {
                $batchQuery->where('batch_id', $filters['batch']);
            }

            $batchAssigns = $batchQuery->get();

            // Calculate summary statistics
            $totalBirds = $batchAssigns->sum('batch_total_qty');
            $maleBirds = $batchAssigns->sum('batch_male_qty');
            $femaleBirds = $batchAssigns->sum('batch_female_qty');

            // Get mortality data from DailyMortality table only (for batch calculations)
            $dailyMortalityData = DailyMortality::query();
            if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
                $dailyMortalityData->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
            }
            $mortalityCount = $dailyMortalityData->sum('male_qty') + $dailyMortalityData->sum('female_qty');
            $mortalityRate = $totalBirds > 0 ? ($mortalityCount / $totalBirds) * 100 : 0;

            // Group by batch for detailed breakdown
            $batchDetails = $batchAssigns->groupBy('batch_no')->map(function ($batches, $batchNo) use ($filters) {
                $firstBatch = $batches->first();
                $totalBirds = $batches->sum('batch_total_qty');
                $maleBirds = $batches->sum('batch_male_qty');
                $femaleBirds = $batches->sum('batch_female_qty');

                // Get mortality data from DailyMortality table for this specific batch
                $batchAssignId = $firstBatch->id;
                $mortalityData = $this->getDailyMortalityDataForBatch($batchAssignId, $filters);
                $mortalityCount = $mortalityData['total'];
                $mortalityRate = $totalBirds > 0 ? ($mortalityCount / $totalBirds) * 100 : 0;

                // Calculate age from shed receive date
                $startDate = $firstBatch->shedReceive?->created_at ?? $firstBatch->created_at;
                $age = '0 weeks 0 days';
                if ($startDate) {
                    $days = $startDate->diffInDays(now());
                    $weeks = floor($days / 7);
                    $remainingDays = $days % 7;
                    $age = "{$weeks} weeks {$remainingDays} days";
                }

                return [
                    'batch_id' => $batchNo,
                    'batch_name' => $firstBatch->batch?->name ?? "Batch {$batchNo}",
                    'flock_name' => $firstBatch->flock?->code ?? 'Unknown Flock',
                    'flock_code' => $firstBatch->flock?->code ?? 'N/A',
                    'company_name' => $firstBatch->company?->name ?? 'Unknown Company',
                    'project_name' => $firstBatch->project?->name ?? 'Unknown Project',
                    'shed_name' => $firstBatch->shed?->name ?? 'Unknown Shed',
                    'total_birds' => $totalBirds,
                    'male_birds' => $maleBirds,
                    'female_birds' => $femaleBirds,
                    'mortality_count' => $mortalityCount,
                    'mortality_rate' => round($mortalityRate, 2),
                    'daily_male' => $mortalityData['daily_male'],
                    'daily_female' => $mortalityData['daily_female'],
                    'movement_male' => $mortalityData['movement_male'],
                    'movement_female' => $mortalityData['movement_female'],
                    'assignments_count' => $batches->count(),
                    'start_date' => $batches->min('start_date'),
                    'end_date' => $batches->max('end_date'),
                    'age' => $age,
                    'status' => $firstBatch->status == 1 ? 'Active' : 'Inactive',
                ];
            })->values();

            // Get recent daily operations for context
            $recentOperations = DailyOperation::whereIn('batchassign_id', $batchAssigns->pluck('id'))
                ->with(['batchAssign.flock', 'batchAssign.batch', 'batchAssign.shed'])
                ->orderBy('operation_date', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($operation) {
                    return [
                        'id' => $operation->id,
                        'operation_date' => $operation->operation_date,
                        'flock_name' => $operation->batchAssign?->flock?->name ?? 'Unknown',
                        'batch_name' => $operation->batchAssign?->batch?->name ?? 'Unknown',
                        'shed_name' => $operation->batchAssign?->shed?->name ?? 'Unknown',
                        'operation_type' => $operation->operation_type ?? 'Unknown',
                        'description' => $operation->description ?? 'No description',
                        'birds_affected' => $operation->birds_affected ?? 0,
                    ];
                });

            // Summary statistics
            $summary = [
                'total_birds' => $totalBirds,
                'male_birds' => $maleBirds,
                'female_birds' => $femaleBirds,
                'mortality_count' => $mortalityCount,
                'mortality_rate' => round($mortalityRate, 2),
                'total_batches' => $batchDetails->count(),
                'active_batches' => $batchDetails->where('status', 'Active')->count(),
                'total_assignments' => $batchAssigns->count(),
            ];

            return [
                'summary' => $summary,
                'batch_details' => $batchDetails,
                'recent_operations' => $recentOperations,
                'timestamp' => time(),
            ];

        } catch (\Exception $e) {
            Log::error('Error getting birds details: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return [
                'summary' => [
                    'total_birds' => 0,
                    'male_birds' => 0,
                    'female_birds' => 0,
                    'mortality_count' => 0,
                    'mortality_rate' => 0,
                    'total_batches' => 0,
                    'active_batches' => 0,
                    'total_assignments' => 0,
                ],
                'batch_details' => [],
                'recent_operations' => [],
                'timestamp' => time(),
            ];
        }
    }

    /**
     * Get detailed mortality information for modal
     */
    public function getMortalityDetails(array $filters = []): array
    {
        try {
            // Get all active batch assignments with their related data
            $batchQuery = BatchAssign::where('status', 1)
                ->with(['flock', 'company', 'shed', 'batch', 'project']);

            // Apply filters
            if (! empty($filters['company'])) {
                $batchQuery->where('company_id', $filters['company']);
            }
            if (! empty($filters['project'])) {
                $batchQuery->where('project_id', $filters['project']);
            }
            if (! empty($filters['flock'])) {
                $batchQuery->where('flock_id', $filters['flock']);
            }
            if (! empty($filters['shed'])) {
                $batchQuery->where('shed_id', $filters['shed']);
            }
            if (! empty($filters['batch'])) {
                $batchQuery->where('batch_no', $filters['batch']);
            }

            $batchAssigns = $batchQuery->get();

            // Calculate summary statistics
            $totalBirds = $batchAssigns->sum('batch_total_qty');

            // Get mortality data from DailyMortality and MovementAdjustment tables
            $mortalityData = $this->getRecentMortalityData($filters);
            $totalMortality = $mortalityData['total'];
            $maleMortality = $mortalityData['male'];
            $femaleMortality = $mortalityData['female'];
            $overallMortalityRate = $totalBirds > 0 ? ($totalMortality / $totalBirds) * 100 : 0;

            // Group by flock for detailed breakdown
            $flockMortalityDetails = $batchAssigns->groupBy('flock_id')->map(function ($batches, $flockId) use ($filters) {
                $firstBatch = $batches->first();
                $totalBirds = $batches->sum('batch_total_qty');

                // Get mortality data from both DailyMortality and MovementAdjustment tables for this flock
                $mortalityData = $this->getMortalityDataForFlock($flockId, $filters);
                $totalMortality = $mortalityData['total'];
                $maleMortality = $mortalityData['male'];
                $femaleMortality = $mortalityData['female'];
                $mortalityRate = $totalBirds > 0 ? ($totalMortality / $totalBirds) * 100 : 0;

                return [
                    'flock_id' => $flockId,
                    'flock_name' => $firstBatch->flock?->name ?? 'Unknown Flock',
                    'flock_code' => $firstBatch->flock?->code ?? 'N/A',
                    'total_birds' => $totalBirds,
                    'total_mortality' => $totalMortality,
                    'male_mortality' => $maleMortality,
                    'female_mortality' => $femaleMortality,
                    'mortality_rate' => round($mortalityRate, 2),
                    'daily_male' => $mortalityData['daily_male'],
                    'daily_female' => $mortalityData['daily_female'],
                    'movement_male' => $mortalityData['movement_male'],
                    'movement_female' => $mortalityData['movement_female'],
                    'batches_count' => $batches->count(),
                    'companies' => $batches->map(fn ($b) => $b->company?->name)->unique()->filter()->values(),
                    'projects' => $batches->map(fn ($b) => $b->project?->name)->unique()->filter()->values(),
                    'sheds' => $batches->map(fn ($b) => $b->shed?->name)->unique()->filter()->values(),
                    'status' => 'Active',
                ];
            })->values();

            // Group by batch for batch-level details
            $batchMortalityDetails = $batchAssigns->groupBy('batch_no')->map(function ($batches, $batchNo) use ($filters) {
                $firstBatch = $batches->first();
                $totalBirds = $batches->sum('batch_total_qty');

                // Get mortality data from DailyMortality table for this specific batch
                $batchAssignId = $firstBatch->id;
                $mortalityData = $this->getDailyMortalityDataForBatch($batchAssignId, $filters);
                $totalMortality = $mortalityData['total'];
                $maleMortality = $mortalityData['male'];
                $femaleMortality = $mortalityData['female'];
                $mortalityRate = $totalBirds > 0 ? ($totalMortality / $totalBirds) * 100 : 0;

                return [
                    'batch_no' => $batchNo,
                    'batch_name' => $firstBatch->batch?->name ?? "Batch {$batchNo}",
                    'flock_name' => $firstBatch->flock?->code ?? 'Unknown Flock',
                    'flock_code' => $firstBatch->flock?->code ?? 'N/A',
                    'company_name' => $firstBatch->company?->name ?? 'Unknown Company',
                    'project_name' => $firstBatch->project?->name ?? 'Unknown Project',
                    'shed_name' => $firstBatch->shed?->name ?? 'Unknown Shed',
                    'total_birds' => $totalBirds,
                    'total_mortality' => $totalMortality,
                    'male_mortality' => $maleMortality,
                    'female_mortality' => $femaleMortality,
                    'mortality_rate' => round($mortalityRate, 2),
                    'daily_male' => $mortalityData['daily_male'],
                    'daily_female' => $mortalityData['daily_female'],
                    'movement_male' => $mortalityData['movement_male'],
                    'movement_female' => $mortalityData['movement_female'],
                    'assignments_count' => $batches->count(),
                    'start_date' => $batches->min('growing_start_date'),
                    'transfer_date' => $batches->max('transfer_date'),
                    'status' => 'Active',
                ];
            })->values();

            // Get recent operations (simplified since daily_operations doesn't have operation_type/description)
            $recentMortalityOperations = DailyOperation::whereIn('batchassign_id', $batchAssigns->pluck('id'))
                ->with(['batchAssign.flock', 'batchAssign.batch', 'batchAssign.shed', 'creator'])
                ->orderBy('operation_date', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($operation) {
                    return [
                        'id' => $operation->id,
                        'operation_date' => $operation->operation_date,
                        'operation_type' => 'Daily Operation',
                        'flock_name' => $operation->batchAssign?->flock?->name ?? 'Unknown',
                        'batch_name' => $operation->batchAssign?->batch?->name ?? 'Unknown',
                        'shed_name' => $operation->batchAssign?->shed?->name ?? 'Unknown',
                        'description' => 'Daily operation recorded',
                        'created_by' => $operation->creator?->name ?? 'Unknown',
                    ];
                });

            // Calculate mortality trends by flock
            $mortalityTrends = $flockMortalityDetails->map(function ($flock) {
                $rate = $flock['mortality_rate'];
                if ($rate <= 2) {
                    $trend = 'excellent';
                    $trendColor = 'green';
                } elseif ($rate <= 5) {
                    $trend = 'good';
                    $trendColor = 'blue';
                } elseif ($rate <= 10) {
                    $trend = 'moderate';
                    $trendColor = 'yellow';
                } else {
                    $trend = 'high';
                    $trendColor = 'red';
                }

                return [
                    'flock_id' => $flock['flock_id'],
                    'flock_name' => $flock['flock_name'],
                    'mortality_rate' => $rate,
                    'trend' => $trend,
                    'trend_color' => $trendColor,
                ];
            });

            // Summary statistics
            $summary = [
                'total_birds' => $totalBirds,
                'total_mortality' => $totalMortality,
                'male_mortality' => $maleMortality,
                'female_mortality' => $femaleMortality,
                'overall_mortality_rate' => round($overallMortalityRate, 2),
                'total_flocks' => $flockMortalityDetails->count(),
                'total_batches' => $batchMortalityDetails->count(),
                'excellent_flocks' => $mortalityTrends->where('trend', 'excellent')->count(),
                'good_flocks' => $mortalityTrends->where('trend', 'good')->count(),
                'moderate_flocks' => $mortalityTrends->where('trend', 'moderate')->count(),
                'high_risk_flocks' => $mortalityTrends->where('trend', 'high')->count(),
            ];

            return [
                'summary' => $summary,
                'flock_details' => $flockMortalityDetails,
                'batch_details' => $batchMortalityDetails,
                'mortality_trends' => $mortalityTrends,
                'recent_operations' => $recentMortalityOperations,
                'timestamp' => time(),
            ];

        } catch (\Exception $e) {
            Log::error('Error getting mortality details: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return [
                'summary' => [
                    'total_birds' => 0,
                    'total_mortality' => 0,
                    'male_mortality' => 0,
                    'female_mortality' => 0,
                    'overall_mortality_rate' => 0,
                    'total_flocks' => 0,
                    'total_batches' => 0,
                    'excellent_flocks' => 0,
                    'good_flocks' => 0,
                    'moderate_flocks' => 0,
                    'high_risk_flocks' => 0,
                ],
                'flock_details' => [],
                'batch_details' => [],
                'mortality_trends' => [],
                'recent_operations' => [],
                'timestamp' => time(),
            ];
        }
    }

    /**
     * Get detailed daily eggs information for modal
     */
    public function getDailyEggsDetails(array $filters = []): array
    {
        try {
            // Get all active batch assignments with their related data
            $batchQuery = BatchAssign::where('status', 1)
                ->with(['flock', 'company', 'shed', 'batch', 'project']);

            // Apply filters
            if (! empty($filters['company'])) {
                $batchQuery->where('company_id', $filters['company']);
            }
            if (! empty($filters['project'])) {
                $batchQuery->where('project_id', $filters['project']);
            }
            if (! empty($filters['flock'])) {
                $batchQuery->where('flock_id', $filters['flock']);
            }
            if (! empty($filters['shed'])) {
                $batchQuery->where('shed_id', $filters['shed']);
            }
            if (! empty($filters['batch'])) {
                $batchQuery->where('batch_no', $filters['batch']);
            }

            $batchAssigns = $batchQuery->get();

            // Get daily egg collections for these batch assignments through daily operations
            $eggCollectionsQuery = DailyEggCollection::whereHas('dailyOperation', function ($query) use ($batchAssigns, $filters) {
                $query->whereIn('batchassign_id', $batchAssigns->pluck('id'));

                // Apply date filters
                if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
                    $query->whereBetween('operation_date', [$filters['date_from'], $filters['date_to']]);
                } elseif (! empty($filters['date'])) {
                    if ($filters['date'] === 'Last 7 Days') {
                        $query->where('operation_date', '>=', now()->subDays(7));
                    } elseif ($filters['date'] === 'Last 1 Month') {
                        $query->where('operation_date', '>=', now()->subMonth());
                    }
                } else {
                    // If no specific date filter, show data from the last 7 days to include recent data
                    $query->where('operation_date', '>=', now()->subDays(7));
                }
            })
                ->with(['dailyOperation.batchAssign.flock', 'dailyOperation.batchAssign.company', 'dailyOperation.batchAssign.shed', 'dailyOperation.batchAssign.batch', 'dailyOperation.batchAssign.project'])
                ->orderBy('created_at', 'desc');

            $eggCollections = $eggCollectionsQuery->get();

            // Calculate summary statistics
            $totalEggs = $eggCollections->sum('quantity');
            // For now, we'll use simple calculations since we don't have classification data in daily_egg_collections
            // In a real scenario, you might want to join with egg_classifications table or add classification fields to daily_egg_collections
            $commercialEggs = $totalEggs * 0.7; // Assume 70% commercial
            $technicalEggs = $totalEggs * 0.2; // Assume 20% technical
            $hatchingEggs = $totalEggs * 0.1; // Assume 10% hatching
            $rejectedEggs = 0; // No rejected eggs in daily collections
            $commercialPercentage = $totalEggs > 0 ? ($commercialEggs / $totalEggs) * 100 : 0;
            $rejectionRate = 0; // No rejection rate for daily collections

            // Group by flock for detailed breakdown
            $flockEggDetails = $eggCollections->groupBy('dailyOperation.batchAssign.flock_id')->map(function ($collections, $flockId) {
                $firstCollection = $collections->first();
                $totalEggs = $collections->sum('quantity');
                $commercialEggs = $totalEggs * 0.7; // Assume 70% commercial
                $technicalEggs = $totalEggs * 0.2; // Assume 20% technical
                $hatchingEggs = $totalEggs * 0.1; // Assume 10% hatching
                $rejectedEggs = 0; // No rejected eggs in daily collections
                $commercialPercentage = $totalEggs > 0 ? ($commercialEggs / $totalEggs) * 100 : 0;
                $rejectionRate = 0; // No rejection rate for daily collections

                return [
                    'flock_id' => $flockId,
                    'flock_name' => $firstCollection->dailyOperation?->batchAssign?->flock?->name ?? 'Unknown Flock',
                    'flock_code' => $firstCollection->dailyOperation?->batchAssign?->flock?->code ?? 'N/A',
                    'total_eggs' => $totalEggs,
                    'commercial_eggs' => $commercialEggs,
                    'technical_eggs' => $technicalEggs,
                    'hatching_eggs' => $hatchingEggs,
                    'rejected_eggs' => $rejectedEggs,
                    'commercial_percentage' => round($commercialPercentage, 2),
                    'rejection_rate' => round($rejectionRate, 2),
                    'collections_count' => $collections->count(),
                    'companies' => $collections->map(fn ($c) => $c->dailyOperation?->batchAssign?->company?->name)->unique()->filter()->values(),
                    'projects' => $collections->map(fn ($c) => $c->dailyOperation?->batchAssign?->project?->name)->unique()->filter()->values(),
                    'sheds' => $collections->map(fn ($c) => $c->dailyOperation?->batchAssign?->shed?->name)->unique()->filter()->values(),
                    'last_collection_date' => $collections->max('created_at'),
                    'status' => 'Active',
                ];
            })->values();

            // Group by batch for batch-level details
            $batchEggDetails = $eggCollections->groupBy('dailyOperation.batchAssign.batch_no')->map(function ($collections, $batchNo) {
                $firstCollection = $collections->first();
                $totalEggs = $collections->sum('quantity');
                $commercialEggs = $totalEggs * 0.7; // Assume 70% commercial
                $technicalEggs = $totalEggs * 0.2; // Assume 20% technical
                $hatchingEggs = $totalEggs * 0.1; // Assume 10% hatching
                $rejectedEggs = 0; // No rejected eggs in daily collections
                $commercialPercentage = $totalEggs > 0 ? ($commercialEggs / $totalEggs) * 100 : 0;
                $rejectionRate = 0; // No rejection rate for daily collections

                return [
                    'batch_no' => $batchNo,
                    'batch_name' => $firstCollection->dailyOperation?->batchAssign?->batch?->name ?? "Batch {$batchNo}",
                    'flock_name' => $firstCollection->dailyOperation?->batchAssign?->flock?->code ?? 'Unknown Flock',
                    'flock_code' => $firstCollection->dailyOperation?->batchAssign?->flock?->code ?? 'N/A',
                    'company_name' => $firstCollection->dailyOperation?->batchAssign?->company?->name ?? 'Unknown Company',
                    'project_name' => $firstCollection->dailyOperation?->batchAssign?->project?->name ?? 'Unknown Project',
                    'shed_name' => $firstCollection->dailyOperation?->batchAssign?->shed?->name ?? 'Unknown Shed',
                    'total_eggs' => $totalEggs,
                    'commercial_eggs' => $commercialEggs,
                    'technical_eggs' => $technicalEggs,
                    'hatching_eggs' => $hatchingEggs,
                    'rejected_eggs' => $rejectedEggs,
                    'commercial_percentage' => round($commercialPercentage, 2),
                    'rejection_rate' => round($rejectionRate, 2),
                    'collections_count' => $collections->count(),
                    'last_collection_date' => $collections->max('created_at'),
                    'status' => 'Active',
                ];
            })->values();

            // Get recent egg collections
            $recentEggCollections = $eggCollections->take(10)->map(function ($collection) {
                $totalEggs = $collection->quantity;
                $commercialEggs = $totalEggs * 0.7; // Assume 70% commercial
                $technicalEggs = $totalEggs * 0.2; // Assume 20% technical
                $hatchingEggs = $totalEggs * 0.1; // Assume 10% hatching
                $rejectedEggs = 0; // No rejected eggs in daily collections

                return [
                    'id' => $collection->id,
                    'collection_date' => $collection->created_at->format('Y-m-d'),
                    'flock_name' => $collection->dailyOperation?->batchAssign?->flock?->name ?? 'Unknown',
                    'batch_name' => $collection->dailyOperation?->batchAssign?->batch?->name ?? 'Unknown',
                    'shed_name' => $collection->dailyOperation?->batchAssign?->shed?->name ?? 'Unknown',
                    'total_eggs' => $totalEggs,
                    'commercial_eggs' => $commercialEggs,
                    'technical_eggs' => $technicalEggs,
                    'hatching_eggs' => $hatchingEggs,
                    'rejected_eggs' => $rejectedEggs,
                    'commercial_percentage' => $totalEggs > 0 ? round(($commercialEggs / $totalEggs) * 100, 2) : 0,
                    'remarks' => $collection->note ?? 'No remarks',
                    'created_by' => 'System', // Daily collections don't have creator field
                ];
            });

            // Calculate production trends by flock
            $productionTrends = $flockEggDetails->map(function ($flock) {
                $commercialRate = $flock['commercial_percentage'];
                if ($commercialRate >= 80) {
                    $trend = 'excellent';
                    $trendColor = 'green';
                } elseif ($commercialRate >= 70) {
                    $trend = 'good';
                    $trendColor = 'blue';
                } elseif ($commercialRate >= 60) {
                    $trend = 'moderate';
                    $trendColor = 'yellow';
                } else {
                    $trend = 'poor';
                    $trendColor = 'red';
                }

                return [
                    'flock_id' => $flock['flock_id'],
                    'flock_name' => $flock['flock_name'],
                    'commercial_percentage' => $commercialRate,
                    'trend' => $trend,
                    'trend_color' => $trendColor,
                ];
            });

            // Summary statistics
            $summary = [
                'total_eggs' => $totalEggs,
                'commercial_eggs' => $commercialEggs,
                'technical_eggs' => $technicalEggs,
                'hatching_eggs' => $hatchingEggs,
                'rejected_eggs' => $rejectedEggs,
                'commercial_percentage' => round($commercialPercentage, 2),
                'rejection_rate' => round($rejectionRate, 2),
                'total_flocks' => $flockEggDetails->count(),
                'total_batches' => $batchEggDetails->count(),
                'total_collections' => $eggCollections->count(),
                'excellent_flocks' => $productionTrends->where('trend', 'excellent')->count(),
                'good_flocks' => $productionTrends->where('trend', 'good')->count(),
                'moderate_flocks' => $productionTrends->where('trend', 'moderate')->count(),
                'poor_flocks' => $productionTrends->where('trend', 'poor')->count(),
            ];

            return [
                'summary' => $summary,
                'flock_details' => $flockEggDetails,
                'batch_details' => $batchEggDetails,
                'production_trends' => $productionTrends,
                'recent_classifications' => $recentEggCollections,
                'timestamp' => time(),
            ];

        } catch (\Exception $e) {
            Log::error('Error getting daily eggs details: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return [
                'summary' => [
                    'total_eggs' => 0,
                    'commercial_eggs' => 0,
                    'technical_eggs' => 0,
                    'hatching_eggs' => 0,
                    'rejected_eggs' => 0,
                    'commercial_percentage' => 0,
                    'rejection_rate' => 0,
                    'total_flocks' => 0,
                    'total_batches' => 0,
                    'total_collections' => 0,
                    'excellent_flocks' => 0,
                    'good_flocks' => 0,
                    'moderate_flocks' => 0,
                    'poor_flocks' => 0,
                ],
                'flock_details' => [],
                'batch_details' => [],
                'production_trends' => [],
                'recent_classifications' => [],
                'timestamp' => time(),
            ];
        }
    }

    /**
     * Get detailed hatchable eggs information for modal
     */
    public function getHatchableEggsDetails(array $filters = []): array
    {
        try {
            // Get all active batch assignments with their related data
            $batchQuery = BatchAssign::where('status', 1)
                ->with(['flock', 'company', 'shed', 'batch', 'project']);

            // Apply filters
            if (! empty($filters['company'])) {
                $batchQuery->where('company_id', $filters['company']);
            }
            if (! empty($filters['project'])) {
                $batchQuery->where('project_id', $filters['project']);
            }
            if (! empty($filters['flock'])) {
                $batchQuery->where('flock_id', $filters['flock']);
            }
            if (! empty($filters['shed'])) {
                $batchQuery->where('shed_id', $filters['shed']);
            }
            if (! empty($filters['batch'])) {
                $batchQuery->where('batch_no', $filters['batch']);
            }

            $batchAssigns = $batchQuery->get();

            // Get egg classifications for these batch assignments, focusing on hatching eggs
            $eggClassifications = \App\Models\Production\EggClassification::whereIn('batchassign_id', $batchAssigns->pluck('id'))
                ->where('hatching_eggs', '>', 0) // Only get records with hatching eggs
                ->with(['batchAssign.flock', 'batchAssign.company', 'batchAssign.shed', 'batchAssign.batch', 'batchAssign.project'])
                ->orderBy('classification_date', 'desc')
                ->get();

            // Calculate summary statistics for hatching eggs
            $totalHatchingEggs = $eggClassifications->sum('hatching_eggs');
            $totalEggs = $eggClassifications->sum('total_eggs');
            $hatchingPercentage = $totalEggs > 0 ? ($totalHatchingEggs / $totalEggs) * 100 : 0;

            // Group by flock for detailed breakdown
            $flockHatchingDetails = $eggClassifications->groupBy('batchAssign.flock_id')->map(function ($classifications, $flockId) {
                $firstClassification = $classifications->first();
                $totalHatchingEggs = $classifications->sum('hatching_eggs');
                $totalEggs = $classifications->sum('total_eggs');
                $hatchingPercentage = $totalEggs > 0 ? ($totalHatchingEggs / $totalEggs) * 100 : 0;

                return [
                    'flock_id' => $flockId,
                    'flock_name' => $firstClassification->batchAssign?->flock?->name ?? 'Unknown Flock',
                    'flock_code' => $firstClassification->batchAssign?->flock?->code ?? 'N/A',
                    'total_hatching_eggs' => $totalHatchingEggs,
                    'total_eggs' => $totalEggs,
                    'hatching_percentage' => round($hatchingPercentage, 2),
                    'classifications_count' => $classifications->count(),
                    'companies' => $classifications->map(fn ($c) => $c->batchAssign?->company?->name)->unique()->filter()->values(),
                    'projects' => $classifications->map(fn ($c) => $c->batchAssign?->project?->name)->unique()->filter()->values(),
                    'sheds' => $classifications->map(fn ($c) => $c->batchAssign?->shed?->name)->unique()->filter()->values(),
                    'last_classification_date' => $classifications->max('classification_date'),
                    'status' => 'Active',
                ];
            })->values();

            // Group by batch for batch-level details
            $batchHatchingDetails = $eggClassifications->groupBy('batchAssign.batch_no')->map(function ($classifications, $batchNo) {
                $firstClassification = $classifications->first();
                $totalHatchingEggs = $classifications->sum('hatching_eggs');
                $totalEggs = $classifications->sum('total_eggs');
                $hatchingPercentage = $totalEggs > 0 ? ($totalHatchingEggs / $totalEggs) * 100 : 0;

                return [
                    'batch_no' => $batchNo,
                    'batch_name' => $firstClassification->batchAssign?->batch?->name ?? "Batch {$batchNo}",
                    'flock_name' => $firstClassification->batchAssign?->flock?->code ?? 'Unknown Flock',
                    'flock_code' => $firstClassification->batchAssign?->flock?->code ?? 'N/A',
                    'company_name' => $firstClassification->batchAssign?->company?->name ?? 'Unknown Company',
                    'project_name' => $firstClassification->batchAssign?->project?->name ?? 'Unknown Project',
                    'shed_name' => $firstClassification->batchAssign?->shed?->name ?? 'Unknown Shed',
                    'total_hatching_eggs' => $totalHatchingEggs,
                    'total_eggs' => $totalEggs,
                    'hatching_percentage' => round($hatchingPercentage, 2),
                    'classifications_count' => $classifications->count(),
                    'last_classification_date' => $classifications->max('classification_date'),
                    'status' => 'Active',
                ];
            })->values();

            // Get recent hatching egg classifications
            $recentHatchingClassifications = $eggClassifications->take(10)->map(function ($classification) {
                return [
                    'id' => $classification->id,
                    'classification_date' => $classification->classification_date,
                    'flock_name' => $classification->batchAssign?->flock?->name ?? 'Unknown',
                    'batch_name' => $classification->batchAssign?->batch?->name ?? 'Unknown',
                    'shed_name' => $classification->batchAssign?->shed?->name ?? 'Unknown',
                    'total_hatching_eggs' => $classification->hatching_eggs,
                    'total_eggs' => $classification->total_eggs,
                    'hatching_percentage' => $classification->total_eggs > 0 ? round(($classification->hatching_eggs / $classification->total_eggs) * 100, 2) : 0,
                    'remarks' => $classification->remarks ?? 'No remarks',
                    'created_by' => $classification->creator?->name ?? 'Unknown',
                ];
            });

            // Calculate hatching performance trends by flock
            // Priority system: Hatchable eggs are first priority, commercial eggs are second priority
            $hatchingTrends = $flockHatchingDetails->map(function ($flock) {
                $hatchingRate = $flock['hatching_percentage'];
                if ($hatchingRate >= 95) {
                    $trend = 'excellent';
                    $trendColor = 'green';
                } elseif ($hatchingRate >= 80) {
                    $trend = 'good';
                    $trendColor = 'blue';
                } elseif ($hatchingRate >= 70) {
                    $trend = 'moderate';
                    $trendColor = 'yellow';
                } else {
                    $trend = 'poor';
                    $trendColor = 'red';
                }

                return [
                    'flock_id' => $flock['flock_id'],
                    'flock_name' => $flock['flock_name'],
                    'hatching_percentage' => $hatchingRate,
                    'trend' => $trend,
                    'trend_color' => $trendColor,
                ];
            });

            // Summary statistics
            $summary = [
                'total_hatching_eggs' => $totalHatchingEggs,
                'total_eggs' => $totalEggs,
                'hatching_percentage' => round($hatchingPercentage, 2),
                'total_flocks' => $flockHatchingDetails->count(),
                'total_batches' => $batchHatchingDetails->count(),
                'total_collections' => $eggClassifications->count(),
                'excellent_flocks' => $hatchingTrends->where('trend', 'excellent')->count(),
                'good_flocks' => $hatchingTrends->where('trend', 'good')->count(),
                'moderate_flocks' => $hatchingTrends->where('trend', 'moderate')->count(),
                'poor_flocks' => $hatchingTrends->where('trend', 'poor')->count(),
            ];

            return [
                'summary' => $summary,
                'flock_details' => $flockHatchingDetails,
                'batch_details' => $batchHatchingDetails,
                'hatching_trends' => $hatchingTrends,
                'recent_classifications' => $recentHatchingClassifications,
                'timestamp' => time(),
            ];

        } catch (\Exception $e) {
            Log::error('Error getting hatchable eggs details: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return [
                'summary' => [
                    'total_hatching_eggs' => 0,
                    'total_eggs' => 0,
                    'hatching_percentage' => 0,
                    'total_flocks' => 0,
                    'total_batches' => 0,
                    'total_collections' => 0,
                    'excellent_flocks' => 0,
                    'good_flocks' => 0,
                    'moderate_flocks' => 0,
                    'poor_flocks' => 0,
                ],
                'flock_details' => [],
                'batch_details' => [],
                'hatching_trends' => [],
                'recent_classifications' => [],
                'timestamp' => time(),
            ];
        }
    }

    /**
     * Get detailed male birds information for modal
     */
    public function getMaleBirdsDetails(array $filters = []): array
    {
        try {
            // Get all active batch assignments with their related data
            $batchQuery = BatchAssign::where('status', 1)
                ->with(['flock', 'company', 'shed', 'batch', 'project']);

            // Apply filters
            if (! empty($filters['company'])) {
                $batchQuery->where('company_id', $filters['company']);
            }
            if (! empty($filters['project'])) {
                $batchQuery->where('project_id', $filters['project']);
            }
            if (! empty($filters['flock'])) {
                $batchQuery->where('flock_id', $filters['flock']);
            }
            if (! empty($filters['shed'])) {
                $batchQuery->where('shed_id', $filters['shed']);
            }
            if (! empty($filters['batch'])) {
                $batchQuery->where('batch_no', $filters['batch']);
            }

            $batchAssigns = $batchQuery->get();

            // Calculate summary statistics for male birds
            $totalMaleBirds = $batchAssigns->sum('batch_male_qty');
            $totalBirds = $batchAssigns->sum('batch_total_qty');
            $totalFemaleBirds = $batchAssigns->sum('batch_female_qty');

            // Get mortality data from DailyMortality table only (for batch calculations)
            $dailyMortalityData = DailyMortality::query();
            if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
                $dailyMortalityData->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
            }
            $maleMortality = $dailyMortalityData->sum('male_qty');
            $malePercentage = $totalBirds > 0 ? ($totalMaleBirds / $totalBirds) * 100 : 0;
            $maleMortalityRate = $totalMaleBirds > 0 ? ($maleMortality / $totalMaleBirds) * 100 : 0;

            // Group by flock for detailed breakdown
            $flockMaleDetails = $batchAssigns->groupBy('flock_id')->map(function ($batches, $flockId) use ($filters) {
                $firstBatch = $batches->first();
                $totalMaleBirds = $batches->sum('batch_male_qty');
                $totalBirds = $batches->sum('batch_total_qty');
                $totalFemaleBirds = $batches->sum('batch_female_qty');

                // Get mortality data from DailyMortality table only for this flock
                $mortalityData = $this->getDailyMortalityDataForFlock($flockId, $filters);
                $maleMortality = $mortalityData['male'];
                $malePercentage = $totalBirds > 0 ? ($totalMaleBirds / $totalBirds) * 100 : 0;
                $maleMortalityRate = $totalMaleBirds > 0 ? ($maleMortality / $totalMaleBirds) * 100 : 0;

                return [
                    'flock_id' => $flockId,
                    'flock_name' => $firstBatch->flock?->name ?? 'Unknown Flock',
                    'flock_code' => $firstBatch->flock?->code ?? 'N/A',
                    'total_male_birds' => $totalMaleBirds,
                    'total_birds' => $totalBirds,
                    'total_female_birds' => $totalFemaleBirds,
                    'male_percentage' => round($malePercentage, 2),
                    'male_mortality' => $maleMortality,
                    'male_mortality_rate' => round($maleMortalityRate, 2),
                    'batches_count' => $batches->count(),
                    'companies' => $batches->map(fn ($b) => $b->company?->name)->unique()->filter()->values(),
                    'projects' => $batches->map(fn ($b) => $b->project?->name)->unique()->filter()->values(),
                    'sheds' => $batches->map(fn ($b) => $b->shed?->name)->unique()->filter()->values(),
                    'last_update' => $batches->max('updated_at'),
                    'status' => 'Active',
                ];
            })->values();

            // Group by batch for batch-level details
            $batchMaleDetails = $batchAssigns->groupBy('batch_no')->map(function ($batches, $batchNo) {
                $firstBatch = $batches->first();
                $totalMaleBirds = $batches->sum('batch_male_qty');
                $totalBirds = $batches->sum('batch_total_qty');
                $totalFemaleBirds = $batches->sum('batch_female_qty');
                $maleMortality = $batches->sum('batch_male_mortality');
                $malePercentage = $totalBirds > 0 ? ($totalMaleBirds / $totalBirds) * 100 : 0;
                $maleMortalityRate = $totalMaleBirds > 0 ? ($maleMortality / $totalMaleBirds) * 100 : 0;

                return [
                    'batch_no' => $batchNo,
                    'batch_name' => $firstBatch->batch?->name ?? "Batch {$batchNo}",
                    'flock_name' => $firstBatch->flock?->code ?? 'Unknown Flock',
                    'flock_code' => $firstBatch->flock?->code ?? 'N/A',
                    'company_name' => $firstBatch->company?->name ?? 'Unknown Company',
                    'project_name' => $firstBatch->project?->name ?? 'Unknown Project',
                    'shed_name' => $firstBatch->shed?->name ?? 'Unknown Shed',
                    'total_male_birds' => $totalMaleBirds,
                    'total_birds' => $totalBirds,
                    'total_female_birds' => $totalFemaleBirds,
                    'male_percentage' => round($malePercentage, 2),
                    'male_mortality' => $maleMortality,
                    'male_mortality_rate' => round($maleMortalityRate, 2),
                    'assignments_count' => $batches->count(),
                    'last_update' => $batches->max('updated_at'),
                    'status' => 'Active',
                ];
            })->values();

            // Get recent daily operations for male birds context
            $recentMaleOperations = DailyOperation::whereIn('batchassign_id', $batchAssigns->pluck('id'))
                ->with(['batchAssign.flock', 'batchAssign.batch', 'batchAssign.shed', 'creator'])
                ->orderBy('operation_date', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($operation) {
                    return [
                        'id' => $operation->id,
                        'operation_date' => $operation->operation_date,
                        'flock_name' => $operation->batchAssign?->flock?->name ?? 'Unknown',
                        'batch_name' => $operation->batchAssign?->batch?->name ?? 'Unknown',
                        'shed_name' => $operation->batchAssign?->shed?->name ?? 'Unknown',
                        'operation_type' => 'Daily Operation',
                        'description' => 'Daily operation recorded',
                        'created_by' => $operation->creator?->name ?? 'Unknown',
                    ];
                });

            // Calculate male bird performance trends by flock
            $maleTrends = $flockMaleDetails->map(function ($flock) {
                $maleRate = $flock['male_percentage'];
                if ($maleRate >= 50) {
                    $trend = 'excellent';
                    $trendColor = 'green';
                } elseif ($maleRate >= 45) {
                    $trend = 'good';
                    $trendColor = 'blue';
                } elseif ($maleRate >= 40) {
                    $trend = 'moderate';
                    $trendColor = 'yellow';
                } else {
                    $trend = 'poor';
                    $trendColor = 'red';
                }

                return [
                    'flock_id' => $flock['flock_id'],
                    'flock_name' => $flock['flock_name'],
                    'male_percentage' => $maleRate,
                    'trend' => $trend,
                    'trend_color' => $trendColor,
                ];
            });

            // Summary statistics
            $summary = [
                'total_male_birds' => $totalMaleBirds,
                'total_birds' => $totalBirds,
                'total_female_birds' => $totalFemaleBirds,
                'male_percentage' => round($malePercentage, 2),
                'male_mortality' => $maleMortality,
                'male_mortality_rate' => round($maleMortalityRate, 2),
                'total_flocks' => $flockMaleDetails->count(),
                'total_batches' => $batchMaleDetails->count(),
                'total_assignments' => $batchAssigns->count(),
                'excellent_flocks' => $maleTrends->where('trend', 'excellent')->count(),
                'good_flocks' => $maleTrends->where('trend', 'good')->count(),
                'moderate_flocks' => $maleTrends->where('trend', 'moderate')->count(),
                'poor_flocks' => $maleTrends->where('trend', 'poor')->count(),
            ];

            return [
                'summary' => $summary,
                'flock_details' => $flockMaleDetails,
                'batch_details' => $batchMaleDetails,
                'male_trends' => $maleTrends,
                'recent_operations' => $recentMaleOperations,
                'timestamp' => time(),
            ];

        } catch (\Exception $e) {
            Log::error('Error getting male birds details: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return [
                'summary' => [
                    'total_male_birds' => 0,
                    'total_birds' => 0,
                    'total_female_birds' => 0,
                    'male_percentage' => 0,
                    'male_mortality' => 0,
                    'male_mortality_rate' => 0,
                    'total_flocks' => 0,
                    'total_batches' => 0,
                    'total_assignments' => 0,
                    'excellent_flocks' => 0,
                    'good_flocks' => 0,
                    'moderate_flocks' => 0,
                    'poor_flocks' => 0,
                ],
                'flock_details' => [],
                'batch_details' => [],
                'male_trends' => [],
                'recent_operations' => [],
                'timestamp' => time(),
            ];
        }
    }

    /**
     * Get detailed female birds information for modal
     */
    public function getFemaleBirdsDetails(array $filters = []): array
    {
        try {
            // Get all active batch assignments with their related data
            $batchQuery = BatchAssign::where('status', 1)
                ->with(['flock', 'company', 'shed', 'batch', 'project']);

            // Apply filters
            if (! empty($filters['company'])) {
                $batchQuery->where('company_id', $filters['company']);
            }
            if (! empty($filters['project'])) {
                $batchQuery->where('project_id', $filters['project']);
            }
            if (! empty($filters['flock'])) {
                $batchQuery->where('flock_id', $filters['flock']);
            }
            if (! empty($filters['shed'])) {
                $batchQuery->where('shed_id', $filters['shed']);
            }
            if (! empty($filters['batch'])) {
                $batchQuery->where('batch_no', $filters['batch']);
            }

            $batchAssigns = $batchQuery->get();

            // Calculate summary statistics for female birds
            $totalFemaleBirds = $batchAssigns->sum('batch_female_qty');
            $totalBirds = $batchAssigns->sum('batch_total_qty');
            $totalMaleBirds = $batchAssigns->sum('batch_male_qty');

            // Get mortality data from DailyMortality table only (for batch calculations)
            $dailyMortalityData = DailyMortality::query();
            if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
                $dailyMortalityData->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
            }
            $femaleMortality = $dailyMortalityData->sum('female_qty');
            $femalePercentage = $totalBirds > 0 ? ($totalFemaleBirds / $totalBirds) * 100 : 0;
            $femaleMortalityRate = $totalFemaleBirds > 0 ? ($femaleMortality / $totalFemaleBirds) * 100 : 0;

            // Group by flock for detailed breakdown
            $flockFemaleDetails = $batchAssigns->groupBy('flock_id')->map(function ($batches, $flockId) use ($filters) {
                $firstBatch = $batches->first();
                $totalFemaleBirds = $batches->sum('batch_female_qty');
                $totalBirds = $batches->sum('batch_total_qty');
                $totalMaleBirds = $batches->sum('batch_male_qty');

                // Get mortality data from DailyMortality table only for this flock
                $mortalityData = $this->getDailyMortalityDataForFlock($flockId, $filters);
                $femaleMortality = $mortalityData['female'];
                $femalePercentage = $totalBirds > 0 ? ($totalFemaleBirds / $totalBirds) * 100 : 0;
                $femaleMortalityRate = $totalFemaleBirds > 0 ? ($femaleMortality / $totalFemaleBirds) * 100 : 0;

                return [
                    'flock_id' => $flockId,
                    'flock_name' => $firstBatch->flock?->name ?? 'Unknown Flock',
                    'flock_code' => $firstBatch->flock?->code ?? 'N/A',
                    'total_female_birds' => $totalFemaleBirds,
                    'total_birds' => $totalBirds,
                    'total_male_birds' => $totalMaleBirds,
                    'female_percentage' => round($femalePercentage, 2),
                    'female_mortality' => $femaleMortality,
                    'female_mortality_rate' => round($femaleMortalityRate, 2),
                    'batches_count' => $batches->count(),
                    'companies' => $batches->map(fn ($b) => $b->company?->name)->unique()->filter()->values(),
                    'projects' => $batches->map(fn ($b) => $b->project?->name)->unique()->filter()->values(),
                    'sheds' => $batches->map(fn ($b) => $b->shed?->name)->unique()->filter()->values(),
                    'last_update' => $batches->max('updated_at'),
                    'status' => 'Active',
                ];
            })->values();

            // Group by batch for batch-level details
            $batchFemaleDetails = $batchAssigns->groupBy('batch_no')->map(function ($batches, $batchNo) {
                $firstBatch = $batches->first();
                $totalFemaleBirds = $batches->sum('batch_female_qty');
                $totalBirds = $batches->sum('batch_total_qty');
                $totalMaleBirds = $batches->sum('batch_male_qty');
                $femaleMortality = $batches->sum('batch_female_mortality');
                $femalePercentage = $totalBirds > 0 ? ($totalFemaleBirds / $totalBirds) * 100 : 0;
                $femaleMortalityRate = $totalFemaleBirds > 0 ? ($femaleMortality / $totalFemaleBirds) * 100 : 0;

                return [
                    'batch_no' => $batchNo,
                    'batch_name' => $firstBatch->batch?->name ?? "Batch {$batchNo}",
                    'flock_name' => $firstBatch->flock?->code ?? 'Unknown Flock',
                    'flock_code' => $firstBatch->flock?->code ?? 'N/A',
                    'company_name' => $firstBatch->company?->name ?? 'Unknown Company',
                    'project_name' => $firstBatch->project?->name ?? 'Unknown Project',
                    'shed_name' => $firstBatch->shed?->name ?? 'Unknown Shed',
                    'total_female_birds' => $totalFemaleBirds,
                    'total_birds' => $totalBirds,
                    'total_male_birds' => $totalMaleBirds,
                    'female_percentage' => round($femalePercentage, 2),
                    'female_mortality' => $femaleMortality,
                    'female_mortality_rate' => round($femaleMortalityRate, 2),
                    'assignments_count' => $batches->count(),
                    'last_update' => $batches->max('updated_at'),
                    'status' => 'Active',
                ];
            })->values();

            // Get recent daily operations for female birds context
            $recentFemaleOperations = DailyOperation::whereIn('batchassign_id', $batchAssigns->pluck('id'))
                ->with(['batchAssign.flock', 'batchAssign.batch', 'batchAssign.shed', 'creator'])
                ->orderBy('operation_date', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($operation) {
                    return [
                        'id' => $operation->id,
                        'operation_date' => $operation->operation_date,
                        'flock_name' => $operation->batchAssign?->flock?->name ?? 'Unknown',
                        'batch_name' => $operation->batchAssign?->batch?->name ?? 'Unknown',
                        'shed_name' => $operation->batchAssign?->shed?->name ?? 'Unknown',
                        'operation_type' => 'Daily Operation',
                        'description' => 'Daily operation recorded',
                        'created_by' => $operation->creator?->name ?? 'Unknown',
                    ];
                });

            // Calculate female bird performance trends by flock
            $femaleTrends = $flockFemaleDetails->map(function ($flock) {
                $femaleRate = $flock['female_percentage'];
                if ($femaleRate >= 50) {
                    $trend = 'excellent';
                    $trendColor = 'green';
                } elseif ($femaleRate >= 45) {
                    $trend = 'good';
                    $trendColor = 'blue';
                } elseif ($femaleRate >= 40) {
                    $trend = 'moderate';
                    $trendColor = 'yellow';
                } else {
                    $trend = 'poor';
                    $trendColor = 'red';
                }

                return [
                    'flock_id' => $flock['flock_id'],
                    'flock_name' => $flock['flock_name'],
                    'female_percentage' => $femaleRate,
                    'trend' => $trend,
                    'trend_color' => $trendColor,
                ];
            });

            // Summary statistics
            $summary = [
                'total_female_birds' => $totalFemaleBirds,
                'total_birds' => $totalBirds,
                'total_male_birds' => $totalMaleBirds,
                'female_percentage' => round($femalePercentage, 2),
                'female_mortality' => $femaleMortality,
                'female_mortality_rate' => round($femaleMortalityRate, 2),
                'total_flocks' => $flockFemaleDetails->count(),
                'total_batches' => $batchFemaleDetails->count(),
                'total_assignments' => $batchAssigns->count(),
                'excellent_flocks' => $femaleTrends->where('trend', 'excellent')->count(),
                'good_flocks' => $femaleTrends->where('trend', 'good')->count(),
                'moderate_flocks' => $femaleTrends->where('trend', 'moderate')->count(),
                'poor_flocks' => $femaleTrends->where('trend', 'poor')->count(),
            ];

            return [
                'summary' => $summary,
                'flock_details' => $flockFemaleDetails,
                'batch_details' => $batchFemaleDetails,
                'female_trends' => $femaleTrends,
                'recent_operations' => $recentFemaleOperations,
                'timestamp' => time(),
            ];

        } catch (\Exception $e) {
            Log::error('Error getting female birds details: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return [
                'summary' => [
                    'total_female_birds' => 0,
                    'total_birds' => 0,
                    'total_male_birds' => 0,
                    'female_percentage' => 0,
                    'female_mortality' => 0,
                    'female_mortality_rate' => 0,
                    'total_flocks' => 0,
                    'total_batches' => 0,
                    'total_assignments' => 0,
                    'excellent_flocks' => 0,
                    'good_flocks' => 0,
                    'moderate_flocks' => 0,
                    'poor_flocks' => 0,
                ],
                'flock_details' => [],
                'batch_details' => [],
                'female_trends' => [],
                'recent_operations' => [],
                'timestamp' => time(),
            ];
        }
    }
}
