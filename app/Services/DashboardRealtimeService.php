<?php

// updated

namespace App\Services;

use App\Models\DailyOperation\DailyEggCollection;
use App\Models\DailyOperation\DailyMortality;
use App\Models\DailyOperation\DailyCulling;
use App\Models\DailyOperation\DailyDestroy;
use App\Models\DailyOperation\DailySexingError;
use App\Models\DailyOperation\DailyOperation;
use App\Models\Production\EggClassification;
use App\Models\FirmLabTest;
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
        
        $totalAssignedMale   = $batchAssigns->sum('batch_male_qty');
        $totalAssignedFemale = $batchAssigns->sum('batch_female_qty');

        $operationIds = DailyOperation::whereIn('batchassign_id', $batchAssigns->pluck('id'))
            ->pluck('id');

        // 2. Now sum male/female per table using those IDs
        $totalMortalityMale   = DailyMortality::whereIn('daily_operation_id', $operationIds)->sum('male_qty');
        $totalMortalityFemale = DailyMortality::whereIn('daily_operation_id', $operationIds)->sum('female_qty');

        $totalLabSendMale   = FirmLabTest::whereIn('batch_assign_id', $batchAssigns->pluck('id'))->sum('firm_lab_send_male_qty');
        $totalLabSendFemale = FirmLabTest::whereIn('batch_assign_id', $batchAssigns->pluck('id'))->sum('firm_lab_send_female_qty');

        $totalCullingMale     = DailyCulling::whereIn('daily_operation_id', $operationIds)->sum('male_qty');
        $totalCullingFemale   = DailyCulling::whereIn('daily_operation_id', $operationIds)->sum('female_qty');

        $totalDestroyedMale   = DailyDestroy::whereIn('daily_operation_id', $operationIds)->sum('male_qty');
        $totalDestroyedFemale = DailyDestroy::whereIn('daily_operation_id', $operationIds)->sum('female_qty');

        $totalSexingErrorMale   = DailySexingError::whereIn('daily_operation_id', $operationIds)->sum('male_qty');
        $totalSexingErrorFemale = DailySexingError::whereIn('daily_operation_id', $operationIds)->sum('female_qty');

        $totalAssignedMale   = $batchAssigns->sum('batch_male_qty');
        $totalAssignedFemale = $batchAssigns->sum('batch_female_qty');

        $totalMale = $totalAssignedMale - (
            $totalMortalityMale + 
            $totalLabSendMale + 
            $totalCullingMale + 
            $totalDestroyedMale + 
            $totalSexingErrorMale
        );

        $totalFemale = $totalAssignedFemale - (
            $totalMortalityFemale + 
            $totalLabSendFemale + 
            $totalCullingFemale + 
            $totalDestroyedFemale + 
            $totalSexingErrorFemale
        );

        $totalBirds = $totalMale + $totalFemale;

        $femaleExcessBirds = $batchAssigns->sum('batch_excess_female');
        $maleSortageBirds = $batchAssigns->sum('batch_sortage_male');
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
        $femaleExcessPercentage = $totalBirds > 0 ? ($femaleExcessBirds / $totalBirds) * 100 : 0;
        $maleSortagePercentage = $totalBirds > 0 ? ($maleSortageBirds / $totalBirds) * 100 : 0;
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
                    'title' => 'Current Birds',
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
                    'extra' => number_format($combinedTotalMortality).' birds ',
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
                    'title' => 'Total Eggs',
                    'progress' => $eggData['total_percentage_based_on_chicks'],
                    'extra' => "Goal: {$eggData['goal']} eggs",
                ],
                [
                    'title' => 'Hatching Eggs',
                    'progress' => $eggData['hatchable_percentage'],
                    'extra' => "Goal: {$eggData['hatchable_goal']} eggs",
                ],
                [
                    'title' => 'Commercial Eggs',
                    'progress' => $eggData['commercial_percentage'],
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
                    'title' => 'Sortage Male',
                    'value' => round($maleSortagePercentage,2),
                    'type' => 'straight',
                ],
                [
                    'title' => 'Excess Female',
                    'value' => round($femaleExcessPercentage,2),
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
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
        } else {
            // Default: last 7 days
            $query->where('created_at', '>=', now()->subDays(7));
        }

        // Get DailyOperation IDs linked to these egg collections
        $dailyOperationIds = $query->pluck('daily_operation_id')->unique();

        // Get hatchable/commercial/broken eggs
        $classQuery = EggClassification::query();
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $classQuery->whereBetween('classification_date', [$filters['date_from'], $filters['date_to']]);
        } else {
            $classQuery->where('classification_date', '>=', now()->subDays(7));
        }

        $hatchable  = $classQuery->sum('hatching_eggs');
        $commercial = $classQuery->sum('commercial_eggs');
        $broken     = $classQuery->sum('rejected_eggs');

        $totalEggs = $query->sum('quantity');
        // ----- Calculate current chicks from BatchAssign and deductions -----
        $operations = DailyOperation::with('batchAssign')
        ->whereIn('id', $dailyOperationIds)
        ->get();

        $batchAssignIds = $operations->pluck('batchassign_id')->unique();

        
        $batchAssignsData = BatchAssign::where('status', 1)
        ->get()
        ->keyBy('id');
            
        // ----- Calculate current chicks from daily operations -----
        $mortality = DailyMortality::whereIn('daily_operation_id', $dailyOperationIds)
            ->selectRaw('daily_operation_id, SUM(male_qty) as male, SUM(female_qty) as female')
            ->groupBy('daily_operation_id')
            ->get()
            ->keyBy('daily_operation_id');

        $culling = DailyCulling::whereIn('daily_operation_id', $dailyOperationIds)
            ->selectRaw('daily_operation_id, SUM(male_qty) as male, SUM(female_qty) as female')
            ->groupBy('daily_operation_id')
            ->get()
            ->keyBy('daily_operation_id');

        $destroy = DailyDestroy::whereIn('daily_operation_id', $dailyOperationIds)
            ->selectRaw('daily_operation_id, SUM(male_qty) as male, SUM(female_qty) as female')
            ->groupBy('daily_operation_id')
            ->get()
            ->keyBy('daily_operation_id');

        $sexingError = DailySexingError::whereIn('daily_operation_id', $dailyOperationIds)
            ->selectRaw('daily_operation_id, SUM(male_qty) as male, SUM(female_qty) as female')
            ->groupBy('daily_operation_id')
            ->get()
            ->keyBy('daily_operation_id');

        // Loop through unique batches instead of operations
        $currentMale = $currentFemale = 0;

        foreach ($batchAssignsData as $batchId => $batch) {
            $assignedMale = $batch->batch_male_qty ?? 0;
            $assignedFemale = $batch->batch_female_qty ?? 0;

            // Sum all deductions related to this batch
            $maleDeducted = 0;
            $femaleDeducted = 0;

            // Loop over operations in this batch
            $batchOperations = $operations->where('batchassign_id', $batchId);
            foreach ($batchOperations as $op) {
                $opId = $op->id;
                $maleDeducted += ($mortality[$opId]->male ?? 0)
                            + ($culling[$opId]->male ?? 0)
                            + ($destroy[$opId]->male ?? 0)
                            + ($sexingError[$opId]->male ?? 0);
                $femaleDeducted += ($mortality[$opId]->female ?? 0)
                                + ($culling[$opId]->female ?? 0)
                                + ($destroy[$opId]->female ?? 0)
                                + ($sexingError[$opId]->female ?? 0);
            }

            // Add lab send deductions (batch-based)
            $maleDeducted += $labSend[$batchId]->male ?? 0;
            $femaleDeducted += $labSend[$batchId]->female ?? 0;

            // Compute current chicks for this batch
            $currentMale += max($assignedMale - $maleDeducted, 0);
            $currentFemale += max($assignedFemale - $femaleDeducted, 0);
        }

        $currentTotalChicks = $currentMale + $currentFemale;

        // Percentage of eggs based on total current chicks
        $totalPercentageBasedOnChicks = $currentTotalChicks > 0
            ? ($totalEggs / $currentTotalChicks) * 100
            : 0;    
        
        return [
            'total' => $totalEggs,
            'hatchable' => $hatchable,
            'commercial' => $commercial,
            'broken' => $broken,
            'hatchable_percentage' => round($totalEggs > 0 ? ($hatchable / $totalEggs) * 100 : 0,2),
            'commercial_percentage' => round($totalEggs > 0 ? ($commercial / $totalEggs) * 100 : 0,2),
            'goal' => 2000,
            'hatchable_goal' => 1200,
            'commercial_goal' => 700,
            'goal_percentage' => $totalEggs > 0 ? min(($totalEggs / 2000) * 100, 100) : 0,
            'hatchable_goal_percentage' => $hatchable > 0 ? min(($hatchable / 1200) * 100, 100) : 0,
            'commercial_goal_percentage' => $commercial > 0 ? min(($commercial / 700) * 100, 100) : 0,
            'current_male_chicks' => $currentMale,
            'current_female_chicks' => $currentFemale,
            'current_total_chicks' => $currentTotalChicks,
            'total_percentage_based_on_chicks' => round($totalPercentageBasedOnChicks, 2),
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
    // Date range: last 7 days
    $startDate = now()->subDays(7);
    $endDate = now();

    // ------------------------------
    // 1. Daily Egg Collections
    // ------------------------------
    $eggCollections = DailyEggCollection::whereBetween('created_at', [$startDate, $endDate])
        ->selectRaw('SUM(quantity) as total')
        ->first();

    $totalEggs = $eggCollections->total ?? 0;

    // ------------------------------
    // 2. Mortality Data
    // ------------------------------
    $dailyMortalityData = DailyMortality::whereBetween('created_at', [$startDate, $endDate])
        ->selectRaw('SUM(male_qty) as male, SUM(female_qty) as female')
        ->first();

    $movementMortalityData = MovementAdjustment::where('type', 1) // 1 = Mortality
        ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
        ->selectRaw('SUM(male_qty) as male, SUM(female_qty) as female')
        ->first();

    $totalMaleMortality = ($dailyMortalityData->male ?? 0) + ($movementMortalityData->male ?? 0);
    $totalFemaleMortality = ($dailyMortalityData->female ?? 0) + ($movementMortalityData->female ?? 0);

    // ------------------------------
    // 3. Egg Classification Data
    // ------------------------------
    $eggClassification = EggClassification::whereBetween('classification_date', [$startDate, $endDate])
        ->selectRaw('SUM(hatching_eggs) as hatchable, SUM(commercial_eggs) as commercial')
        ->first();

    $totalHatchable = $eggClassification->hatchable ?? 0;
    $totalCommercial = $eggClassification->commercial ?? 0;

    // Calculate percentages based on total eggs
    $hatchablePercentage = $totalEggs > 0 ? round(($totalHatchable / $totalEggs) * 100, 2) : 0;
    $commercialPercentage = $totalEggs > 0 ? round(($totalCommercial / $totalEggs) * 100, 2) : 0;

    // ------------------------------
    // 4. Prepare Chart Data
    // ------------------------------
    return [
        'totalEggs' => $totalEggs,
        'production' => [
            [
                'label' => 'Total Eggs',
                'value' => $totalEggs,
                'color' => '#3b82f6',
            ],
        ],
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
                'value' => $hatchablePercentage,
                'color' => '#10b981',
            ],
            [
                'label' => 'Commercial',
                'value' => $commercialPercentage,
                'color' => '#3b82f6',
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
            $batchAssigns = $batchQuery->with(['flock', 'shed', 'company', 'batch', 'project', 'shedReceive'])
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
                $totalAssignBirds = $batchAssigns->sum('batch_total_qty');
                $totalBatchMale = $totalAssignMale = $batchAssigns->sum('batch_male_qty');
                $totalBatchFemale = $totalAssignFemale= $batchAssigns->sum('batch_female_qty');
                
                $batchFemaleMortality = $batchAssigns->sum('batch_female_mortality');
                $batchMaleMortality = $batchAssigns->sum('batch_male_mortality');
                

                // Get all daily_operation IDs linked to those batch assigns
                $operationIds = DailyOperation::whereIn('batchassign_id', $batchAssigns->pluck('id'))
                    ->pluck('id');

                // 4. Calculate losses from daily_* tables (male/female separately)
                $totaldailyMortalityMale   = DailyMortality::whereIn('daily_operation_id', $operationIds)->sum('male_qty');
                $totaldailyMortalityFemale = DailyMortality::whereIn('daily_operation_id', $operationIds)->sum('female_qty');

                $totalLabSendMale     = FirmLabTest::whereIn('batch_assign_id', $batchAssigns->pluck('id'))->sum('firm_lab_send_male_qty');
                $totalLabSendFemale   = FirmLabTest::whereIn('batch_assign_id', $batchAssigns->pluck('id'))->sum('firm_lab_send_female_qty');

                $totalCullingMale     = DailyCulling::whereIn('daily_operation_id', $operationIds)->sum('male_qty');
                $totalCullingFemale   = DailyCulling::whereIn('daily_operation_id', $operationIds)->sum('female_qty');

                $totalDestroyedMale   = DailyDestroy::whereIn('daily_operation_id', $operationIds)->sum('male_qty');
                $totalDestroyedFemale = DailyDestroy::whereIn('daily_operation_id', $operationIds)->sum('female_qty');

                $totalSexingErrorMale   = DailySexingError::whereIn('daily_operation_id', $operationIds)->sum('male_qty');
                $totalSexingErrorFemale = DailySexingError::whereIn('daily_operation_id', $operationIds)->sum('female_qty');




                 $totalMortalityMale =   $totaldailyMortalityFemale ;
                 $totalMortalityFemale =  $totaldailyMortalityMale;
                // 5. Final current birds
                $totalMale = $totalBatchMale - (
                    $totaldailyMortalityMale+
                    $totalLabSendMale +
                    $totalCullingMale +
                    $totalDestroyedMale +
                    $totalSexingErrorMale
                );

                $totalFemale = $totalBatchFemale - (
                    $totaldailyMortalityFemale+
                    $totalLabSendFemale +
                    $totalCullingFemale +
                    $totalDestroyedFemale +
                    $totalSexingErrorFemale
                );

                $othersRejection = $totalLabSendFemale +
                    $totalCullingFemale +
                    $totalDestroyedFemale +
                    $totalSexingErrorFemale + $totalLabSendMale +
                    $totalCullingMale +
                    $totalDestroyedMale +
                    $totalSexingErrorMale;
                

                $totalBirds = $totalMale + $totalFemale;


                $totalMortality = $totalMortalityMale + $totalMortalityFemale;

                // Get recent daily operations for this flock
                $recentOperations = $this->getRecentOperationsForFlock($flock->id, $filters);

                // Get egg collection data for this flock
                $eggData = $this->getEggDataForFlock($flock->id, $filters);

                // Get mortality data for this flock
                $mortalityData = $this->getMortalityDataForFlock($flock->id, $filters);

                // Calculate mortality percentage
                $mortalityPercentage = $totalBirds > 0 ? ($totalMortality / $totalBirds) * 100 : 0;

                // Calculate mortality percentage
                $otherRejectionPercentage = $totalBirds > 0 ? ($othersRejection / $totalBirds) * 100 : 0;

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
                    'total_assign_bird'=>$totalAssignBirds,
                    'assign_male_birds'=>$totalAssignMale,
                    'assign_female_birds'=>$totalAssignFemale,
                    // Statistics
                    'total_birds' => $totalBirds,
                    'male_birds' => $totalMale,
                    'female_birds' => $totalFemale,
                    'total_mortality' => $totalMortality,
                    'others_rejection'=>$othersRejection,
                    'rejection_precentage'=> round($otherRejectionPercentage, 2),
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
        // 1. Get all active batch assignments with relationships
        $batchQuery = BatchAssign::where('status', 1)
            ->with(['flock', 'company', 'shed', 'batch', 'project']);

        // Apply filters
        if (!empty($filters['company'])) $batchQuery->where('company_id', $filters['company']);
        if (!empty($filters['project'])) $batchQuery->where('project_id', $filters['project']);
        if (!empty($filters['flock'])) $batchQuery->where('flock_id', $filters['flock']);
        if (!empty($filters['shed'])) $batchQuery->where('shed_id', $filters['shed']);
        if (!empty($filters['batch'])) $batchQuery->where('batch_no', $filters['batch']);

        $batchAssigns = $batchQuery->get();
        if ($batchAssigns->isEmpty()) {
            return [
                'summary' => [],
                'flock_details' => [],
                'batch_details' => [],
                'timestamp' => time(),
            ];
        }

        $batchIds = $batchAssigns->pluck('id');

        // 2. Collect operation IDs once
        $operationIds = DailyOperation::whereIn('batchassign_id', $batchIds)
            ->pluck('id', 'batchassign_id');

        // 3. Preload aggregates for each operation type
        $mortality = DailyMortality::whereIn('daily_operation_id', $operationIds->values())
            ->selectRaw('daily_operation_id, SUM(male_qty) as male, SUM(female_qty) as female')
            ->groupBy('daily_operation_id')
            ->get()
            ->keyBy('daily_operation_id');

        $labSend = FirmLabTest::whereIn('batch_assign_id', $batchIds)
            ->selectRaw('batch_assign_id, SUM(firm_lab_send_male_qty) as male, SUM(firm_lab_send_female_qty) as female')
            ->groupBy('batch_assign_id')
            ->get()
            ->keyBy('batch_assign_id');

        $culling = DailyCulling::whereIn('daily_operation_id', $operationIds->values())
            ->selectRaw('daily_operation_id, SUM(male_qty) as male, SUM(female_qty) as female')
            ->groupBy('daily_operation_id')
            ->get()
            ->keyBy('daily_operation_id');

        $destroy = DailyDestroy::whereIn('daily_operation_id', $operationIds->values())
            ->selectRaw('daily_operation_id, SUM(male_qty) as male, SUM(female_qty) as female')
            ->groupBy('daily_operation_id')
            ->get()
            ->keyBy('daily_operation_id');

        $sexingError = DailySexingError::whereIn('daily_operation_id', $operationIds->values())
            ->selectRaw('daily_operation_id, SUM(male_qty) as male, SUM(female_qty) as female')
            ->groupBy('daily_operation_id')
            ->get()
            ->keyBy('daily_operation_id');

        // Helper: resolve aggregates for one batch
        $resolveOps = function ($batchId, $opIds) use ($mortality, $labSend, $culling, $destroy, $sexingError) {
            $mMale = $mFemale = $lMale = $lFemale = $cMale = $cFemale = $dMale = $dFemale = $sMale = $sFemale = 0;

            foreach ($opIds as $opId) {
                if (isset($mortality[$opId])) {
                    $mMale += $mortality[$opId]->male;
                    $mFemale += $mortality[$opId]->female;
                }
                if (isset($culling[$opId])) {
                    $cMale += $culling[$opId]->male;
                    $cFemale += $culling[$opId]->female;
                }
                if (isset($destroy[$opId])) {
                    $dMale += $destroy[$opId]->male;
                    $dFemale += $destroy[$opId]->female;
                }
                if (isset($sexingError[$opId])) {
                    $sMale += $sexingError[$opId]->male;
                    $sFemale += $sexingError[$opId]->female;
                }
            }

            if (isset($labSend[$batchId])) {
                $lMale = $labSend[$batchId]->male;
                $lFemale = $labSend[$batchId]->female;
            }

            return [
                'mortality_male'   => $mMale,
                'mortality_female' => $mFemale,
                'lab_male'         => $lMale,
                'lab_female'       => $lFemale,
                'culling_male'     => $cMale,
                'culling_female'   => $cFemale,
                'destroyed_male'   => $dMale,
                'destroyed_female' => $dFemale,
                'sexing_male'      => $sMale,
                'sexing_female'    => $sFemale,
                'total_male_mortality' => $mMale,
            ];
        };

        // 4. Batch-level details
        $batchDetails = $batchAssigns->map(function ($batch) use ($operationIds, $resolveOps) {
            $opIds = $operationIds->filter(fn($_, $id) => $id === $batch->id)->values();
            $ops = $resolveOps($batch->id, $opIds);

            $totalMale   = $batch->batch_male_qty;
            $totalFemale = $batch->batch_female_qty;
            $totalBirds  = $batch->batch_total_qty;

            $deductedMale   = $ops['mortality_male'] + $ops['lab_male'] + $ops['culling_male'] + $ops['destroyed_male'] + $ops['sexing_male'];
            $deductedFemale = $ops['mortality_female'] + $ops['lab_female'] + $ops['culling_female'] + $ops['destroyed_female'] + $ops['sexing_female'];
            
            $currentMale   = $totalMale - $deductedMale;
            $currentFemale = $totalFemale - $deductedFemale;
            $currentTotal  = $currentMale + $currentFemale;
            // Age calculation
            $startDate = $batch->shedReceive?->created_at ?? $batch->created_at;
            $age = '0 weeks 0 days';
            if ($startDate) {
                $days = $startDate->diffInDays(now());
                $weeks = floor($days / 7);
                $remainingDays = $days % 7;
                $age = "{$weeks} weeks {$remainingDays} days";
            }

            return [
                'batch_no' => $batch->batch_no,
                'batch_name' => $batch->batch?->name ?? 'Unknown Batch',
                'flock_name' => $batch->flock?->name ?? 'Unknown Flock',
                'company_name' => $batch->company?->name ?? 'Unknown Company',
                'project_name' => $batch->project?->name ?? 'Unknown Project',
                'shed_name' => $batch->shed?->name ?? 'Unknown Shed',

                // Assigned birds
                'total_male_birds' => $totalMale,
                'total_female_birds' => $totalFemale,
                'total_birds' => $totalBirds,
                'assign_male_percentage' => $totalBirds > 0 ? round(($totalMale / $totalBirds) * 100, 2) : 0,
                'assign_female_percentage' => $totalBirds > 0 ? round(($totalFemale / $totalBirds) * 100, 2) : 0,

                // Current birds
                'current_male_birds' => $currentMale,
                'current_female_birds' => $currentFemale,
                'current_total_birds' => $currentTotal,
                'current_male_percentage' => $currentTotal > 0 ? round(($currentMale / $currentTotal) * 100, 2) : 0,
                'current_female_percentage' => $currentTotal > 0 ? round(($currentFemale / $currentTotal) * 100, 2) : 0,

                // Mortality & rejection
                'male_mortality' => $ops['mortality_male'],
                'female_mortality' => $ops['mortality_female'],
                'male_mortality_percentage' => $totalMale > 0 ? round(($ops['mortality_male'] / $totalMale) * 100, 2) : 0,
                'female_mortality_percentage' => $totalFemale > 0 ? round(($ops['mortality_female'] / $totalFemale) * 100, 2) : 0,
                'total_mortality' => $ops['mortality_male'] + $ops['mortality_female'],
                'total_mortality_percentage' => $totalBirds > 0 
                    ? round((($ops['mortality_male'] + $ops['mortality_female']) / $totalBirds) * 100, 2) 
                    : 0,
                'total_other_rejection' => ($ops['lab_male'] + $ops['culling_male'] + $ops['destroyed_male'] + $ops['sexing_male'])
                          + ($ops['lab_female'] + $ops['culling_female'] + $ops['destroyed_female'] + $ops['sexing_female']),
                'total_other_rejection_percentage' => $totalBirds > 0
                ? round((($ops['lab_male'] + $ops['culling_male'] + $ops['destroyed_male'] + $ops['sexing_male'])
                        + ($ops['lab_female'] + $ops['culling_female'] + $ops['destroyed_female'] + $ops['sexing_female'])) / $totalBirds * 100, 2)
                : 0,
                'age' => $age,
                'status' => $batch->status == 1 ? 'Active' : 'Inactive',
            ];
        });

        // 5. Flock-level aggregation
        $flockDetails = $batchDetails->groupBy('flock_name')->map(function ($batches, $flockName) {
            $totalMale   = $batches->sum('total_male_birds');
            $totalFemale = $batches->sum('total_female_birds');
            $totalBirds  = $batches->sum('total_birds');

            $currentMale   = $batches->sum('current_male_birds');
            $currentFemale = $batches->sum('current_female_birds');
            $currentTotal  = $batches->sum('current_total_birds');

            $mortalityMale  = $batches->sum('male_mortality');
            $mortalityFemale = $batches->sum('female_mortality');
            $otherRejMale   = $batches->sum('other_rejection_male');
            $otherRejFemale = $batches->sum('other_rejection_female');

            return [
                'flock_name' => $flockName,
                'total_male_birds' => $totalMale,
                'total_female_birds' => $totalFemale,
                'total_birds' => $totalBirds,
                'current_male_birds' => $currentMale,
                'current_female_birds' => $currentFemale,
                'current_total_birds' => $currentTotal,
                'assign_male_percentage' => $totalBirds > 0 ? round(($totalMale / $totalBirds) * 100, 2) : 0,
                'assign_female_percentage' => $totalBirds > 0 ? round(($totalFemale / $totalBirds) * 100, 2) : 0,
                'current_male_percentage' => $currentTotal > 0 ? round(($currentMale / $currentTotal) * 100, 2) : 0,
                'current_female_percentage' => $currentTotal > 0 ? round(($currentFemale / $currentTotal) * 100, 2) : 0,
                'male_mortality' => $mortalityMale,
                'female_mortality' => $mortalityFemale,
                'other_rejection_male' => $otherRejMale,
                'other_rejection_female' => $otherRejFemale,
                'batches_count' => $batches->count(),
            ];
        })->values();

        // 6. Summary
        $summary = [
            'total_male_birds' => $batchDetails->sum('total_male_birds'),
            'total_female_birds' => $batchDetails->sum('total_female_birds'),
            'total_birds' => $batchDetails->sum('total_birds'),

            'current_male_birds' => $batchDetails->sum('current_male_birds'),
            'current_female_birds' => $batchDetails->sum('current_female_birds'),
            'current_total_birds' => $batchDetails->sum('current_total_birds'),

            'total_male_mortality' => $batchDetails->sum('male_mortality'),
            'total_female_mortality' => $batchDetails->sum('female_mortality'),
            'total_other_rejection_male' => $batchDetails->sum('other_rejection_male'),
            'total_other_rejection_female' => $batchDetails->sum('other_rejection_female'),

            'assign_male_percentage' => $batchDetails->sum('total_birds') > 0
                ? round(($batchDetails->sum('total_male_birds') / $batchDetails->sum('total_birds')) * 100, 2) : 0,
            'assign_female_percentage' => $batchDetails->sum('total_birds') > 0
                ? round(($batchDetails->sum('total_female_birds') / $batchDetails->sum('total_birds')) * 100, 2) : 0,
            'current_male_percentage' => $batchDetails->sum('current_total_birds') > 0
                ? round(($batchDetails->sum('current_male_birds') / $batchDetails->sum('current_total_birds')) * 100, 2) : 0,
            'current_female_percentage' => $batchDetails->sum('current_total_birds') > 0
                ? round(($batchDetails->sum('current_female_birds') / $batchDetails->sum('total_birds')) * 100, 2) : 0,

            'total_batches' => $batchDetails->count(),
            'total_flocks' => $flockDetails->count(),
            'total_assignments' => $batchAssigns->count(),
            'active_batches' => $batchAssigns->where('status',1)->count(),
        ];

        return [
            'summary' => $summary,
            'flock_details' => $flockDetails,
            'batch_details' => $batchDetails,
            'timestamp' => time(),
        ];
    } catch (\Exception $e) {
        Log::error('Error getting male birds details: '.$e->getMessage());
        Log::error('Stack trace: '.$e->getTraceAsString());

        return [
            'summary' => [],
            'flock_details' => [],
            'batch_details' => [],
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

            

        // 1. Query collections with dailyOperation + batchAssign
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
                $query->where('operation_date', '=', now()->subDays(7));
            }
        })
        ->with([
            'dailyOperation.batchAssign.flock',
            'dailyOperation.batchAssign.company',
            'dailyOperation.batchAssign.shed',
            'dailyOperation.batchAssign.batch',
            'dailyOperation.batchAssign.project',
        ])
        ->orderBy('created_at', 'desc');

        $eggCollections = $eggCollectionsQuery->get();

        // 2. Collect batch/date pairs
        $batchDatePairs = $eggCollections->map(fn($c) => [
            'batchassign_id' => $c->dailyOperation->batchassign_id,
            'date'           => $c->dailyOperation->operation_date->toDateString(),
        ]);

        // 3. Fetch EggClassifications
        $eggClassifications = EggClassification::whereIn('batchassign_id', $batchAssigns->pluck('id'))
            ->whereIn('classification_date', $batchDatePairs->pluck('date')->unique())
            ->get()
            ->groupBy(fn($item) => $item->batchassign_id.'_'.$item->classification_date);

        // 4. Attach classification values to each collection
        $eggCollections->each(function ($collection) use ($eggClassifications) {
            $key = $collection->dailyOperation->batchassign_id.'_'.$collection->dailyOperation->operation_date->toDateString();
            $classification = $eggClassifications->get($key)?->first();

            $collection->commercial_eggs = $classification->commercial_eggs ?? 0;
            $collection->hatching_eggs   = $classification->hatching_eggs ?? 0;
            $collection->rejected_eggs   = $classification->rejected_eggs ?? 0;
            $collection->technical_eggs   = $classification->technical_eggs ?? 0;

        });

      

        // 5. Summary
        $totalCommercialEggs = $eggCollections->sum('commercial_eggs');
        $totalHatchingEggs   = $eggCollections->sum('hatching_eggs');
        $totalRejectedEggs   = $eggCollections->sum('rejected_eggs');
        $totalEggs           = $eggCollections->sum('quantity');
        $technicalEggs =  $eggCollections->sum('technical_eggs');
        

        $commercialPercentage = $totalEggs > 0 ? ($totalCommercialEggs / $totalEggs) * 100 : 0;
        $hatchingPercentage   = $totalEggs > 0 ? ($totalHatchingEggs / $totalEggs) * 100 : 0;
        $rejectionRate        = $totalEggs > 0 ? ($totalRejectedEggs / $totalEggs) * 100 : 0;

        // 6. Group by flock
        $flockEggDetails = $eggCollections->groupBy('dailyOperation.batchAssign.flock_id')->map(function ($collections, $flockId) {
            $first = $collections->first();
            $commercial = $collections->sum('commercial_eggs');
            $hatching   = $collections->sum('hatching_eggs');
            $rejected   = $collections->sum('rejected_eggs');
            $total      = $collections->sum('quantity');

            return [
                'flock_id'    => $flockId,
                'flock_name'  => $first->dailyOperation?->batchAssign?->flock?->name ?? 'Unknown Flock',
                'flock_code'  => $first->dailyOperation?->batchAssign?->flock?->code ?? 'N/A',
                'total_eggs'  => $total,
                'commercial_eggs' => $commercial,
                'hatching_eggs'   => $hatching,
                'rejected_eggs'   => $rejected,
                'commercial_percentage' => $total > 0 ? round(($commercial / $total) * 100, 2) : 0,
                'hatching_percentage'   => $total > 0 ? round(($hatching / $total) * 100, 2) : 0,
                'rejection_rate'        => $total > 0 ? round(($rejected / $total) * 100, 2) : 0,
                'collections_count'     => $collections->count(),
                'companies' => $collections->map(fn ($c) => $c->dailyOperation?->batchAssign?->company?->name)->unique()->filter()->values(),
                'projects'  => $collections->map(fn ($c) => $c->dailyOperation?->batchAssign?->project?->name)->unique()->filter()->values(),
                'sheds'     => $collections->map(fn ($c) => $c->dailyOperation?->batchAssign?->shed?->name)->unique()->filter()->values(),
                'last_collection_date' => $collections->max('created_at'),
                'status' => 'Active',
            ];
        })->values();
        
        // 7. Group by batch
        $batchEggDetails = $eggCollections->groupBy('dailyOperation.batchAssign.batch_no')->map(function ($collections, $batchNo) {
            $first = $collections->first();
            $commercial = $collections->sum('commercial_eggs');
            $hatching   = $collections->sum('hatching_eggs');
            $rejected   = $collections->sum('rejected_eggs');
            $total      = $collections->sum('quantity');

            return [
                'batch_no'   => $batchNo,
                'batch_name' => $first->dailyOperation?->batchAssign?->batch?->name ?? "Batch {$batchNo}",
                'flock_name' => $first->dailyOperation?->batchAssign?->flock?->name ?? 'Unknown Flock',
                'flock_code' => $first->dailyOperation?->batchAssign?->flock?->code ?? 'N/A',
                'company_name' => $first->dailyOperation?->batchAssign?->company?->name ?? 'Unknown Company',
                'project_name' => $first->dailyOperation?->batchAssign?->project?->name ?? 'Unknown Project',
                'shed_name'    => $first->dailyOperation?->batchAssign?->shed?->name ?? 'Unknown Shed',
                'total_eggs'   => $total,
                'commercial_eggs' => $commercial,
                'hatching_eggs'   => $hatching,
                'rejected_eggs'   => $rejected,
                'commercial_percentage' => $total > 0 ? round(($commercial / $total) * 100, 2) : 0,
                'hatching_percentage'   => $total > 0 ? round(($hatching / $total) * 100, 2) : 0,
                'rejection_rate'        => $total > 0 ? round(($rejected / $total) * 100, 2) : 0,
                'collections_count'     => $collections->count(),
                'last_collection_date'  => $collections->max('created_at'),
                'status' => 'Active',
            ];
        })->values();

       


            // 8. Recent collections
            $recentEggCollections = $eggCollections->take(10)->map(function ($c) {
                //$total = $c->commercial_eggs + $c->hatching_eggs + $c->rejected_eggs;

                $total = $c->quantity;

                return [
                    'id'             => $c->id,
                    'collection_date'=> $c->created_at->format('Y-m-d'),
                    'flock_name'     => $c->dailyOperation?->batchAssign?->flock?->name ?? 'Unknown',
                    'batch_name'     => $c->dailyOperation?->batchAssign?->batch?->name ?? 'Unknown',
                    'shed_name'      => $c->dailyOperation?->batchAssign?->shed?->name ?? 'Unknown',
                    'total_eggs'     => $total,
                    'commercial_eggs'=> $c->commercial_eggs,
                    'hatching_eggs'  => $c->hatching_eggs,
                    'rejected_eggs'  => $c->rejected_eggs,
                    'commercial_percentage' => $total > 0 ? round(($c->commercial_eggs / $total) * 100, 2) : 0,
                    'remarks'        => $c->note ?? 'No remarks',
                    'created_by'     => 'System',
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
                'commercial_eggs' => $totalCommercialEggs,
                'technical_eggs' => $technicalEggs,
                'hatching_eggs' => $totalHatchingEggs,
                'rejected_eggs' =>$totalRejectedEggs,
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
                    'total_eggs' => 555,
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
            $eggClassifications = EggClassification::whereIn('batchassign_id', $batchAssigns->pluck('id'))
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
                'total_eggs' => $totalEggs,
                'total_hatching_eggs' => $totalHatchingEggs,
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
            // 1. Get all active batch assignments with relationships
            $batchQuery = BatchAssign::where('status', 1)
                ->with(['flock', 'company', 'shed', 'batch', 'project']);

            // Apply filters
            if (!empty($filters['company'])) $batchQuery->where('company_id', $filters['company']);
            if (!empty($filters['project'])) $batchQuery->where('project_id', $filters['project']);
            if (!empty($filters['flock']))   $batchQuery->where('flock_id', $filters['flock']);
            if (!empty($filters['shed']))    $batchQuery->where('shed_id', $filters['shed']);
            if (!empty($filters['batch']))   $batchQuery->where('batch_no', $filters['batch']);

            $batchAssigns = $batchQuery->get();

            if ($batchAssigns->isEmpty()) {
                return [
                    'summary' => [],
                    'flock_details' => [],
                    'batch_details' => [],
                    'timestamp' => time(),
                ];
            }

            $batchIds = $batchAssigns->pluck('id');

            // 2. Collect operation IDs grouped by batch
            $operationIds = DailyOperation::whereIn('batchassign_id', $batchIds)
                ->pluck('id', 'batchassign_id')
                ->groupBy(function ($_, $key) {
                    return $key; // group by batchassign_id
                });

            // 3. Preload aggregates for each operation type
            $mortality = DailyMortality::whereIn('daily_operation_id', $operationIds->flatten())
                ->selectRaw('daily_operation_id, SUM(male_qty) as male, SUM(female_qty) as female')
                ->groupBy('daily_operation_id')
                ->get()
                ->keyBy('daily_operation_id');

            $labSend = FirmLabTest::whereIn('batch_assign_id', $batchIds)
                ->selectRaw('batch_assign_id, SUM(firm_lab_send_male_qty) as male, SUM(firm_lab_send_female_qty) as female')
                ->groupBy('batch_assign_id')
                ->get()
                ->keyBy('batch_assign_id');

            $culling = DailyCulling::whereIn('daily_operation_id', $operationIds->flatten())
                ->selectRaw('daily_operation_id, SUM(male_qty) as male, SUM(female_qty) as female')
                ->groupBy('daily_operation_id')
                ->get()
                ->keyBy('daily_operation_id');

            $destroy = DailyDestroy::whereIn('daily_operation_id', $operationIds->flatten())
                ->selectRaw('daily_operation_id, SUM(male_qty) as male, SUM(female_qty) as female')
                ->groupBy('daily_operation_id')
                ->get()
                ->keyBy('daily_operation_id');

            $sexingError = DailySexingError::whereIn('daily_operation_id', $operationIds->flatten())
                ->selectRaw('daily_operation_id, SUM(male_qty) as male, SUM(female_qty) as female')
                ->groupBy('daily_operation_id')
                ->get()
                ->keyBy('daily_operation_id');

            // Helper: resolve aggregates for one batch
            $resolveOps = function ($batchId, $opIds) use ($mortality, $labSend, $culling, $destroy, $sexingError) {
                $mMale = $mFemale = $lMale = $lFemale = $cMale = $cFemale = $dMale = $dFemale = $sMale = $sFemale = 0;

                foreach ($opIds as $opId) {
                    if (isset($mortality[$opId])) {
                        $mMale += $mortality[$opId]->male;
                        $mFemale += $mortality[$opId]->female;
                    }
                    if (isset($culling[$opId])) {
                        $cMale += $culling[$opId]->male;
                        $cFemale += $culling[$opId]->female;
                    }
                    if (isset($destroy[$opId])) {
                        $dMale += $destroy[$opId]->male;
                        $dFemale += $destroy[$opId]->female;
                    }
                    if (isset($sexingError[$opId])) {
                        $sMale += $sexingError[$opId]->male;
                        $sFemale += $sexingError[$opId]->female;
                    }
                }

                if (isset($labSend[$batchId])) {
                    $lMale = $labSend[$batchId]->male;
                    $lFemale = $labSend[$batchId]->female;
                }

                return [
                    'mortality_male'   => $mMale,
                    'mortality_female' => $mFemale,
                    'lab_male'         => $lMale,
                    'lab_female'       => $lFemale,
                    'culling_male'     => $cMale,
                    'culling_female'   => $cFemale,
                    'destroyed_male'   => $dMale,
                    'destroyed_female' => $dFemale,
                    'sexing_male'      => $sMale,
                    'sexing_female'    => $sFemale,
                ];
            };

            // 4. Batch details
            $batchDetails = $batchAssigns->map(function ($batch) use ($operationIds, $resolveOps) {
                $opIds = $operationIds->get($batch->id, collect());
                $ops = $resolveOps($batch->id, $opIds);

                $totalMale   = $batch->batch_male_qty;
                $totalFemale = $batch->batch_female_qty;
                $totalBirds  = $batch->batch_total_qty;

                $deductedMale   = $ops['mortality_male'] + $ops['lab_male'] + $ops['culling_male'] + $ops['destroyed_male'] + $ops['sexing_male'];
                $deductedFemale = $ops['mortality_female'] + $ops['lab_female'] + $ops['culling_female'] + $ops['destroyed_female'] + $ops['sexing_female'];

                $currentMale   = $totalMale - $deductedMale;
                $currentFemale = $totalFemale - $deductedFemale;
                $currentTotal  = $currentMale + $currentFemale;

                $assignMalePct  = $totalBirds > 0 ? ($totalMale / $totalBirds) * 100 : 0;
                $currentMalePct = $currentTotal > 0 ? ($currentMale / $currentTotal) * 100 : 0;
                $maleMortalityPct = $totalMale > 0 ? ($ops['mortality_male'] / $totalMale) * 100 : 0;

                $otherRejMale   = $ops['lab_male'] + $ops['culling_male'] + $ops['destroyed_male'] + $ops['sexing_male'];
                $otherRejFemale = $ops['lab_female'] + $ops['culling_female'] + $ops['destroyed_female'] + $ops['sexing_female'];

                $otherRejectionMalePct   = $totalMale > 0 ? ($otherRejMale / $totalMale) * 100 : 0;
                $otherRejectionFemalePct = $totalFemale > 0 ? ($otherRejFemale / $totalFemale) * 100 : 0;

                return [
                    'batch_id'                     => $batch->id,
                    'flock_id'                     => $batch->flock_id,
                    'batch_no'                     => $batch->batch_no,
                    'batch_name'                   => $batch->batch?->name ?? 'Unknown Batch',
                    'flock_name'                   => $batch->flock?->name ?? 'Unknown Flock',
                    'company_name'                 => $batch->company?->name ?? 'Unknown Company',
                    'project_name'                 => $batch->project?->name ?? 'Unknown Project',
                    'shed_name'                    => $batch->shed?->name ?? 'Unknown Shed',

                    'total_male_birds'             => $totalMale,
                    'total_female_birds'           => $totalFemale,
                    'total_birds'                  => $totalBirds,

                    'current_male_birds'           => $currentMale,
                    'current_female_birds'         => $currentFemale,
                    'current_total_birds'          => $currentTotal,

                    'assign_male_percentage'       => round($assignMalePct, 2),
                    'current_male_percentage'      => round($currentMalePct, 2),

                    'mortality_male'               => $ops['mortality_male'],
                    'male_mortality_percentage'    => round($maleMortalityPct, 2),

                    'other_rejection_male'         => $otherRejMale,
                    'other_rejection_female'       => $otherRejFemale,
                    'other_rejection_male_percentage'   => round($otherRejectionMalePct, 2),
                    'other_rejection_female_percentage' => round($otherRejectionFemalePct, 2),

                    'lab_send_male'                => $ops['lab_male'],
                    'culling_male'                 => $ops['culling_male'],
                    'destroyed_male'               => $ops['destroyed_male'],
                    'sexing_error_male'            => $ops['sexing_male'],
                ];
            });

            // 5. Group by flock
            $flockDetails = $batchDetails->groupBy('flock_name')->map(function ($batches, $flockName) {
                $totalMale   = $batches->sum('total_male_birds');
                $totalFemale = $batches->sum('total_female_birds');
                $totalBirds  = $batches->sum('total_birds');

                $currentMale   = $batches->sum('current_male_birds');
                $currentFemale = $batches->sum('current_female_birds');
                $currentTotal  = $batches->sum('current_total_birds');

                $mortalityMale  = $batches->sum('mortality_male');
                $otherRejMale   = $batches->sum('other_rejection_male');
                $otherRejFemale = $batches->sum('other_rejection_female');

                $assignMalePct  = $totalBirds > 0 ? ($totalMale / $totalBirds) * 100 : 0;
                $currentMalePct = $currentTotal > 0 ? ($currentMale / $currentTotal) * 100 : 0;
                $maleMortalityPct = $totalMale > 0 ? ($mortalityMale / $totalMale) * 100 : 0;
                $otherRejectionMalePct   = $totalMale > 0 ? ($otherRejMale / $totalMale) * 100 : 0;
                $otherRejectionFemalePct = $totalFemale > 0 ? ($otherRejFemale / $totalFemale) * 100 : 0;

                return [
                    'flock_name'                   => $flockName,
                    'total_male_birds'             => $totalMale,
                    'total_female_birds'           => $totalFemale,
                    'total_birds'                  => $totalBirds,
                    'current_male_birds'           => $currentMale,
                    'current_female_birds'         => $currentFemale,
                    'current_total_birds'          => $currentTotal,
                    'assign_male_percentage'       => round($assignMalePct, 2),
                    'current_male_percentage'      => round($currentMalePct, 2),
                    'male_mortality'               => $mortalityMale,
                    'male_mortality_percentage'    => round($maleMortalityPct, 2),
                    'other_rejection_male'         => $otherRejMale,
                    'other_rejection_female'       => $otherRejFemale,
                    'other_rejection_male_percentage'   => round($otherRejectionMalePct, 2),
                    'other_rejection_female_percentage' => round($otherRejectionFemalePct, 2),
                    'batches_count'                => $batches->count(),
                ];
            })->values();

            // 6. New summary calculations
            $excellentFlocks = $flockDetails->filter(fn($f) => $f['current_male_percentage'] >= 50)->count();
            $goodFlocks      = $flockDetails->filter(fn($f) => $f['current_male_percentage'] >= 45 && $f['current_male_percentage'] < 50)->count();
            $moderateFlocks  = $flockDetails->filter(fn($f) => $f['current_male_percentage'] >= 40 && $f['current_male_percentage'] < 45)->count();
            $poorFlocks      = $flockDetails->filter(fn($f) => $f['current_male_percentage'] < 40)->count();

            $totalFlocks      = $flockDetails->count();
            $totalBatches     = $batchDetails->count();
            $totalAssignments = $batchAssigns->count();

            // 7. Summary
            $summary = [
                'total_male_birds'       => $batchDetails->sum('total_male_birds'),
                'total_female_birds'     => $batchDetails->sum('total_female_birds'),
                'total_birds'            => $batchDetails->sum('total_birds'),

                'current_male_birds'     => $batchDetails->sum('current_male_birds'),
                'current_female_birds'   => $batchDetails->sum('current_female_birds'),
                'current_total_birds'    => $batchDetails->sum('current_total_birds'),

                'total_male_mortality'       => $batchDetails->sum('mortality_male'),
                'total_other_rejection_male' => $batchDetails->sum('other_rejection_male'),
                'total_other_rejection_female' => $batchDetails->sum('other_rejection_female'),

                'assign_male_percentage'      => $batchDetails->sum('total_birds') > 0
                    ? round(($batchDetails->sum('total_male_birds') / $batchDetails->sum('total_birds')) * 100, 2)
                    : 0,

                'current_male_percentage'     => $batchDetails->sum('current_total_birds') > 0
                    ? round(($batchDetails->sum('current_male_birds') / $batchDetails->sum('current_total_birds')) * 100, 2)
                    : 0,

                'male_mortality_percentage'   => $batchDetails->sum('total_male_birds') > 0
                    ? round(($batchDetails->sum('mortality_male') / $batchDetails->sum('total_male_birds')) * 100, 2)
                    : 0,

                'other_rejection_male_percentage' => $batchDetails->sum('total_male_birds') > 0
                    ? round(($batchDetails->sum('other_rejection_male') / $batchDetails->sum('total_male_birds')) * 100, 2)
                    : 0,

                'other_rejection_female_percentage' => $batchDetails->sum('total_female_birds') > 0
                    ? round(($batchDetails->sum('other_rejection_female') / $batchDetails->sum('total_female_birds')) * 100, 2)
                    : 0,

                // New fields
                'total_flocks'         => $totalFlocks,
                'total_batches'        => $totalBatches,
                'total_assignments'    => $totalAssignments,
                'excellent_flocks'     => $excellentFlocks,
                'good_flocks'          => $goodFlocks,
                'moderate_flocks'      => $moderateFlocks,
                'poor_flocks'          => $poorFlocks,
            ];

            return [
                'summary'       => $summary,
                'flock_details' => $flockDetails,
                'batch_details' => $batchDetails,
                'timestamp'     => time(),
            ];

        } catch (\Exception $e) {
            Log::error('Error getting male birds details: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());
            return [
                'summary' => [],
                'flock_details' => [],
                'batch_details' => [],
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
            $batchQuery = BatchAssign::where('status', 1)
                ->with(['flock', 'company', 'shed', 'batch', 'project']);

            // Apply filters
            if (!empty($filters['company'])) $batchQuery->where('company_id', $filters['company']);
            if (!empty($filters['project'])) $batchQuery->where('project_id', $filters['project']);
            if (!empty($filters['flock'])) $batchQuery->where('flock_id', $filters['flock']);
            if (!empty($filters['shed'])) $batchQuery->where('shed_id', $filters['shed']);
            if (!empty($filters['batch'])) $batchQuery->where('batch_no', $filters['batch']);

            $batchAssigns = $batchQuery->get();

            if ($batchAssigns->isEmpty()) {
                return [
                    'summary' => [],
                    'flock_details' => [],
                    'batch_details' => [],
                    'female_trends' => [],
                    'recent_operations' => [],
                    'timestamp' => time(),
                ];
            }

            $batchIds = $batchAssigns->pluck('id');

            $operationIds = DailyOperation::whereIn('batchassign_id', $batchIds)
                ->pluck('id', 'batchassign_id')
                ->groupBy(fn($_, $key) => $key);

            // Aggregate tables
            $mortality = DailyMortality::whereIn('daily_operation_id', $operationIds->flatten())
                ->selectRaw('daily_operation_id, SUM(female_qty) as female, SUM(male_qty) as male')
                ->groupBy('daily_operation_id')
                ->get()
                ->keyBy('daily_operation_id');

            $labSend = FirmLabTest::whereIn('batch_assign_id', $batchIds)
                ->selectRaw('batch_assign_id, SUM(firm_lab_send_female_qty) as female, SUM(firm_lab_send_male_qty) as male')
                ->groupBy('batch_assign_id')
                ->get()
                ->keyBy('batch_assign_id');

            $culling = DailyCulling::whereIn('daily_operation_id', $operationIds->flatten())
                ->selectRaw('daily_operation_id, SUM(female_qty) as female, SUM(male_qty) as male')
                ->groupBy('daily_operation_id')
                ->get()
                ->keyBy('daily_operation_id');

            $destroy = DailyDestroy::whereIn('daily_operation_id', $operationIds->flatten())
                ->selectRaw('daily_operation_id, SUM(female_qty) as female, SUM(male_qty) as male')
                ->groupBy('daily_operation_id')
                ->get()
                ->keyBy('daily_operation_id');

            $sexingError = DailySexingError::whereIn('daily_operation_id', $operationIds->flatten())
                ->selectRaw('daily_operation_id, SUM(female_qty) as female, SUM(male_qty) as male')
                ->groupBy('daily_operation_id')
                ->get()
                ->keyBy('daily_operation_id');

            $resolveOps = function ($batchId, $opIds) use ($mortality, $labSend, $culling, $destroy, $sexingError) {
                $mFemale = $mMale = $lFemale = $lMale = $cFemale = $cMale = $dFemale = $dMale = $sFemale = $sMale = 0;

                foreach ($opIds as $opId) {
                    $mFemale += $mortality[$opId]->female ?? 0;
                    $mMale   += $mortality[$opId]->male ?? 0;

                    $cFemale += $culling[$opId]->female ?? 0;
                    $cMale   += $culling[$opId]->male ?? 0;

                    $dFemale += $destroy[$opId]->female ?? 0;
                    $dMale   += $destroy[$opId]->male ?? 0;

                    $sFemale += $sexingError[$opId]->female ?? 0;
                    $sMale   += $sexingError[$opId]->male ?? 0;
                }

                if (isset($labSend[$batchId])) {
                    $lFemale = $labSend[$batchId]->female;
                    $lMale   = $labSend[$batchId]->male;
                }

                return [
                    'mortality_female' => $mFemale,
                    'mortality_male'   => $mMale,
                    'lab_female'       => $lFemale,
                    'lab_male'         => $lMale,
                    'culling_female'   => $cFemale,
                    'culling_male'     => $cMale,
                    'destroyed_female' => $dFemale,
                    'destroyed_male'   => $dMale,
                    'sexing_female'    => $sFemale,
                    'sexing_male'      => $sMale,
                ];
            };

            $batchDetails = $batchAssigns->map(function ($batch) use ($operationIds, $resolveOps) {
                $opIds = $operationIds->get($batch->id, collect());
                $ops = $resolveOps($batch->id, $opIds);

                $totalFemale = $batch->batch_female_qty;
                $totalMale   = $batch->batch_male_qty;
                $totalBirds  = $batch->batch_total_qty;

                $deductedFemale = $ops['mortality_female'] + $ops['lab_female'] + $ops['culling_female'] + $ops['destroyed_female'] + $ops['sexing_female'];
                $currentFemale  = $totalFemale - $deductedFemale;
                $currentTotal   = $currentFemale + ($totalMale - ($ops['mortality_male'] + $ops['lab_male'] + $ops['culling_male'] + $ops['destroyed_male'] + $ops['sexing_male']));

                $femalePercentage = $totalBirds > 0 ? ($totalFemale / $totalBirds) * 100 : 0;
                $femaleMortalityRate = $totalFemale > 0 ? ($ops['mortality_female'] / $totalFemale) * 100 : 0;
                $otherRejFemalePct = $totalFemale > 0 ? (($ops['lab_female'] + $ops['culling_female'] + $ops['destroyed_female'] + $ops['sexing_female']) / $totalFemale) * 100 : 0;
                $femaleMortalityRate    = $totalFemale > 0 ? ($ops['mortality_female'] / $totalFemale) * 100 : 0;
                return [
                    'batch_id' => $batch->id,
                    'flock_id' => $batch->flock_id,
                    'batch_no' => $batch->batch_no,
                    'batch_name' => $batch->batch?->name ?? 'Unknown Batch',
                    'flock_name' => $batch->flock?->name ?? 'Unknown Flock',
                    'total_female_birds' => $totalFemale,
                    'total_birds' => $totalBirds,
                    'female_mortality' => $ops['mortality_female'],
                    'female_mortality_rate' => round($femaleMortalityRate, 2),
                    'current_female_birds' => $currentFemale,
                    'current_total_birds' => $currentTotal,
                    'company_name' => $batch->company?->name ?? 'Unknown Company',
                    'female_percentage' => round($femalePercentage, 2),
                    'current_female_percentage' => $currentTotal > 0 ? round(($currentFemale / $totalBirds) * 100, 2) : 0,
                    'other_rejection_female' => $ops['lab_female'] + $ops['culling_female'] + $ops['destroyed_female'] + $ops['sexing_female'],
                    'other_rejection_female_percentage' => round($otherRejFemalePct, 2),
                ];
            });

            $flockDetails = $batchDetails->groupBy('flock_name')->map(function ($batches, $flockName) {
                $totalFemale = $batches->sum('total_female_birds');
                $currentFemale = $batches->sum('current_female_birds');
                $totalBirds = $batches->sum('total_birds');
                $currentTotal = $batches->sum('current_total_birds');
                $mortalityFemale = $batches->sum('female_mortality');
                $otherRejFemale = $batches->sum('other_rejection_female');

                $femalePct = $totalBirds > 0 ? ($totalFemale / $totalBirds) * 100 : 0;
                $currentFemalePct = $currentTotal > 0 ? ($currentFemale / $totalBirds) * 100 : 0;
                $femaleMortalityPct = $totalFemale > 0 ? ($mortalityFemale / $totalFemale) * 100 : 0;
                $otherRejFemalePct = $totalFemale > 0 ? ($otherRejFemale / $totalFemale) * 100 : 0;

                return [
                    'flock_name' => $flockName,
                    'total_female_birds' => $totalFemale,
                    'current_female_birds' => $currentFemale,
                    'total_birds' => $totalBirds,
                    'current_total_birds' => $currentTotal,
                    'female_percentage' => round($femalePct, 2),
                    'current_female_percentage' => round($currentFemalePct, 2),
                    'female_mortality' => $mortalityFemale,
                    'female_mortality_percentage' => round($femaleMortalityPct, 2),
                    'current_female_percentage' => round($currentFemalePct, 2),
                    'other_rejection_female' => $otherRejFemale,
                    'other_rejection_female_percentage' => round($otherRejFemalePct, 2),
                    'batches_count' => $batches->count(),
                ];
            })->values();

            // Female trends
            $femaleTrends = $flockDetails->map(function ($flock) {
                $rate = $flock['female_percentage'];
                $trend = $rate >= 50 ? 'excellent' : ($rate >= 45 ? 'good' : ($rate >= 40 ? 'moderate' : 'poor'));
                $trendColor = match($trend) {
                    'excellent' => 'green',
                    'good' => 'blue',
                    'moderate' => 'yellow',
                    'poor' => 'red',
                };
                return [
                    'flock_name' => $flock['flock_name'],
                    'female_percentage' => $rate,
                    'trend' => $trend,
                    'trend_color' => $trendColor,
                ];
            });

            $summary = [
                'total_female_birds' => $batchDetails->sum('total_female_birds'),
                'current_female_birds' => $batchDetails->sum('current_female_birds'),
                'total_birds' => $batchDetails->sum('total_birds'),
                'current_total_birds' => $batchDetails->sum('current_total_birds'),
                'female_percentage' => $batchDetails->sum('total_birds') > 0
                    ? round(($batchDetails->sum('total_female_birds') / $batchDetails->sum('total_birds')) * 100, 2)
                    : 0,
                'current_female_percentage' => $batchDetails->sum('current_total_birds') > 0? round(($batchDetails->sum('current_female_birds') / $batchDetails->sum('total_birds')) * 100, 2)
        : 0,
                'female_mortality' => $batchDetails->sum('female_mortality'),
                'female_mortality_rate' => $batchDetails->sum('total_female_birds') > 0
                    ? round(($batchDetails->sum('female_mortality') / $batchDetails->sum('total_female_birds')) * 100, 2)
                    : 0,
                'other_rejection_female' => $batchDetails->sum('other_rejection_female'),
                'other_rejection_female_percentage' => $batchDetails->sum('total_female_birds') > 0
                    ? round(($batchDetails->sum('other_rejection_female') / $batchDetails->sum('total_female_birds')) * 100, 2)
                    : 0,
                'total_flocks' => $flockDetails->count(),
                'total_batches' => $batchDetails->count(),
                'total_assignments' => $batchAssigns->count(),
                'excellent_flocks' => $femaleTrends->where('trend', 'excellent')->count(),
                'good_flocks' => $femaleTrends->where('trend', 'good')->count(),
                'moderate_flocks' => $femaleTrends->where('trend', 'moderate')->count(),
                'poor_flocks' => $femaleTrends->where('trend', 'poor')->count(),
            ];

            $recentOperations = DailyOperation::whereIn('batchassign_id', $batchAssigns->pluck('id'))
                ->with(['batchAssign.flock', 'batchAssign.batch', 'batchAssign.shed', 'creator'])
                ->orderBy('operation_date', 'desc')
                ->limit(10)
                ->get();

            return [
                'summary' => $summary,
                'flock_details' => $flockDetails,
                'batch_details' => $batchDetails,
                'female_trends' => $femaleTrends,
                'recent_operations' => $recentOperations,
                'timestamp' => time(),
            ];

        } catch (\Exception $e) {
            Log::error('Error getting female birds details: '.$e->getMessage());
            Log::error($e->getTraceAsString());
            return [
                'summary' => [],
                'flock_details' => [],
                'batch_details' => [],
                'female_trends' => [],
                'recent_operations' => [],
                'timestamp' => time(),
            ];
        }
    }

}
