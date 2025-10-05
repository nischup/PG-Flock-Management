<?php

namespace App\Services;

use App\Models\Shed\BatchAssign;
use App\Models\DailyOperation\DailyOperation;
use App\Models\MovementAdjustment;
use App\Models\BirdTransfer\BirdTransfer;

class BirdInventoryService
{
    /**
     * Get closing birds for a single BatchAssign
     */
    public static function getBatchAssignClosingBirds(BatchAssign $batchAssign, ?string $reportDate = null): array
    {
        // ------------------------
        // 1. Daily Operations
        // ------------------------
        $dailyTotals = DailyOperation::where('batchassign_id', $batchAssign->id)
            ->when($reportDate, fn($q) => $q->whereDate('operation_date', '<=', $reportDate))
            ->leftJoin('daily_mortalities', 'daily_mortalities.daily_operation_id', '=', 'daily_operations.id')
            ->leftJoin('daily_cullings', 'daily_cullings.daily_operation_id', '=', 'daily_operations.id')
            ->leftJoin('daily_destroys', 'daily_destroys.daily_operation_id', '=', 'daily_operations.id')
            ->leftJoin('daily_sexing_errors', 'daily_sexing_errors.daily_operation_id', '=', 'daily_operations.id')
            ->selectRaw("
                SUM(daily_mortalities.female_qty) as mortality_female,
                SUM(daily_mortalities.male_qty) as mortality_male,
                SUM(daily_cullings.female_qty) as culling_female,
                SUM(daily_cullings.male_qty) as culling_male,
                SUM(daily_destroys.female_qty) as destroy_female,
                SUM(daily_destroys.male_qty) as destroy_male,
                SUM(daily_sexing_errors.female_qty) as sex_error_female,
                SUM(daily_sexing_errors.male_qty) as sex_error_male
            ")
            ->first();

        $mortalityFemale = $dailyTotals->mortality_female ?? 0;
        $mortalityMale   = $dailyTotals->mortality_male ?? 0;
        $cullingFemale   = $dailyTotals->culling_female ?? 0;
        $cullingMale     = $dailyTotals->culling_male ?? 0;
        $destroyFemale   = $dailyTotals->destroy_female ?? 0;
        $destroyMale     = $dailyTotals->destroy_male ?? 0;
        $sexErrorFemale  = $dailyTotals->sex_error_female ?? 0;
        $sexErrorMale    = $dailyTotals->sex_error_male ?? 0;

        // ------------------------
        // 2. Movement Adjustments (Batch Assign Stage)
        // ------------------------
        $movementTotals = MovementAdjustment::where('stage', 4)
            ->where('stage_id', $batchAssign->id)
            ->when($reportDate, fn($q) => $q->whereDate('date', '<=', $reportDate))
            ->selectRaw("
                SUM(CASE WHEN type = 1 THEN female_qty ELSE 0 END) as mortality_adjust_female,
                SUM(CASE WHEN type = 1 THEN male_qty ELSE 0 END) as mortality_adjust_male,
                SUM(CASE WHEN type = 2 THEN female_qty ELSE 0 END) as excess_female,
                SUM(CASE WHEN type = 2 THEN male_qty ELSE 0 END) as excess_male,
                SUM(CASE WHEN type = 3 THEN female_qty ELSE 0 END) as shortage_female,
                SUM(CASE WHEN type = 3 THEN male_qty ELSE 0 END) as shortage_male
            ")
            ->first();

        $mortalityAdjustFemale = $movementTotals->mortality_adjust_female ?? 0;
        $mortalityAdjustMale   = $movementTotals->mortality_adjust_male ?? 0;
        $excessFemale          = $movementTotals->excess_female ?? 0;
        $excessMale            = $movementTotals->excess_male ?? 0;
        $shortageFemale        = $movementTotals->shortage_female ?? 0;
        $shortageMale          = $movementTotals->shortage_male ?? 0;

        // ------------------------
        // 3. Transfer Mortality & Adjustments (Stage 5)
        // ------------------------
        $transferIds = BirdTransfer::where('batch_assign_id ', $batchAssign->id)->pluck('id');

        $transferTotals = MovementAdjustment::where('stage', 5)
            ->whereIn('stage_id', $transferIds)
            ->when($reportDate, fn($q) => $q->whereDate('date', '<=', $reportDate))
            ->selectRaw("
                SUM(CASE WHEN type = 1 THEN female_qty ELSE 0 END) as transfer_mortality_female,
                SUM(CASE WHEN type = 1 THEN male_qty ELSE 0 END) as transfer_mortality_male,
                SUM(CASE WHEN type = 2 THEN female_qty ELSE 0 END) as transfer_excess_female,
                SUM(CASE WHEN type = 2 THEN male_qty ELSE 0 END) as transfer_excess_male,
                SUM(CASE WHEN type = 3 THEN female_qty ELSE 0 END) as transfer_shortage_female,
                SUM(CASE WHEN type = 3 THEN male_qty ELSE 0 END) as transfer_shortage_male
            ")
            ->first();

        $transferMortalityFemale = $transferTotals->transfer_mortality_female ?? 0;
        $transferMortalityMale   = $transferTotals->transfer_mortality_male ?? 0;
        $transferExcessFemale    = $transferTotals->transfer_excess_female ?? 0;
        $transferExcessMale      = $transferTotals->transfer_excess_male ?? 0;
        $transferShortageFemale  = $transferTotals->transfer_shortage_female ?? 0;
        $transferShortageMale    = $transferTotals->transfer_shortage_male ?? 0;

        // ------------------------
        // 4. Firm Lab Sends
        // ------------------------
        $firmLabTotals = $batchAssign->firmLabTests()
            ->when($reportDate, fn($q) => $q->whereDate('created_at', '<=', $reportDate))
            ->selectRaw("
                SUM(firm_lab_send_female_qty) as lab_send_female,
                SUM(firm_lab_send_male_qty) as lab_send_male
            ")
            ->first();

        $firmLabFemale = $firmLabTotals->lab_send_female ?? 0;
        $firmLabMale   = $firmLabTotals->lab_send_male ?? 0;

        // ------------------------
        // 5. Opening Quantities
        // ------------------------
        $openingFemale = $batchAssign->batch_female_qty ?? 0;
        $openingMale   = $batchAssign->batch_male_qty ?? 0;

        // ------------------------
        // 6. Calculate Closing Birds
        // ------------------------
        $closingFemale = $openingFemale
            - ($mortalityFemale + $cullingFemale + $destroyFemale + $sexErrorFemale)
            - ($shortageFemale + $transferShortageFemale)
            - $firmLabFemale
            - $transferMortalityFemale
            + ($excessFemale + $transferExcessFemale)
            - $mortalityAdjustFemale;

        $closingMale = $openingMale
            - ($mortalityMale + $cullingMale + $destroyMale + $sexErrorMale)
            - ($shortageMale + $transferShortageMale)
            - $firmLabMale
            - $transferMortalityMale
            + ($excessMale + $transferExcessMale)
            - $mortalityAdjustMale;

        return [
            'closing_female' => max($closingFemale, 0),
            'closing_male'   => max($closingMale, 0),
        ];
    }

    /**
     * Get closing birds for a Flock + Batch (aggregated from all batchassigns)
     */
    public static function getFlockBatchClosingBirds(int $flockId, int $batchId, ?string $reportDate = null): array
    {
        // Get all batchassign ids for this flock + batch
        $batchAssignIds = BatchAssign::where('flock_id', $flockId)
            ->where('batch_id', $batchId)
            ->pluck('id');

        // ------------------------
        // 1. Daily Operations
        // ------------------------
        $dailyTotals = DailyOperation::whereIn('batchassign_id', $batchAssignIds)
            ->when($reportDate, fn($q) => $q->whereDate('operation_date', '<=', $reportDate))
            ->leftJoin('daily_mortalities', 'daily_mortalities.daily_operation_id', '=', 'daily_operations.id')
            ->leftJoin('daily_cullings', 'daily_cullings.daily_operation_id', '=', 'daily_operations.id')
            ->leftJoin('daily_destroys', 'daily_destroys.daily_operation_id', '=', 'daily_operations.id')
            ->leftJoin('daily_sexing_errors', 'daily_sexing_errors.daily_operation_id', '=', 'daily_operations.id')
            ->selectRaw("
                SUM(daily_mortalities.female_qty) as mortality_female,
                SUM(daily_mortalities.male_qty) as mortality_male,
                SUM(daily_cullings.female_qty) as culling_female,
                SUM(daily_cullings.male_qty) as culling_male,
                SUM(daily_destroys.female_qty) as destroy_female,
                SUM(daily_destroys.male_qty) as destroy_male,
                SUM(daily_sexing_errors.female_qty) as sex_error_female,
                SUM(daily_sexing_errors.male_qty) as sex_error_male
            ")
            ->first();

        $mortalityFemale = $dailyTotals->mortality_female ?? 0;
        $mortalityMale   = $dailyTotals->mortality_male ?? 0;
        $cullingFemale   = $dailyTotals->culling_female ?? 0;
        $cullingMale     = $dailyTotals->culling_male ?? 0;
        $destroyFemale   = $dailyTotals->destroy_female ?? 0;
        $destroyMale     = $dailyTotals->destroy_male ?? 0;
        $sexErrorFemale  = $dailyTotals->sex_error_female ?? 0;
        $sexErrorMale    = $dailyTotals->sex_error_male ?? 0;

        // ------------------------
        // 2. Movement Adjustments (Batch Assign stage)
        // ------------------------
        $movementTotals = MovementAdjustment::where('stage', 4)
            ->whereIn('stage_id', $batchAssignIds)
            ->when($reportDate, fn($q) => $q->whereDate('date', '<=', $reportDate))
            ->selectRaw("
                SUM(CASE WHEN type = 1 THEN female_qty ELSE 0 END) as mortality_adjust_female,
                SUM(CASE WHEN type = 1 THEN male_qty ELSE 0 END) as mortality_adjust_male,
                SUM(CASE WHEN type = 2 THEN female_qty ELSE 0 END) as excess_female,
                SUM(CASE WHEN type = 2 THEN male_qty ELSE 0 END) as excess_male,
                SUM(CASE WHEN type = 3 THEN female_qty ELSE 0 END) as shortage_female,
                SUM(CASE WHEN type = 3 THEN male_qty ELSE 0 END) as shortage_male
            ")
            ->first();

        $mortalityAdjustFemale = $movementTotals->mortality_adjust_female ?? 0;
        $mortalityAdjustMale   = $movementTotals->mortality_adjust_male ?? 0;
        $excessFemale          = $movementTotals->excess_female ?? 0;
        $excessMale            = $movementTotals->excess_male ?? 0;
        $shortageFemale        = $movementTotals->shortage_female ?? 0;
        $shortageMale          = $movementTotals->shortage_male ?? 0;

        // ------------------------
        // 3. Transfer Mortality (Stage 5)
        // ------------------------
        $transferIds = BirdTransfer::whereIn('batch_assign_id ', $batchAssignIds)->pluck('id');

        $transferTotals = MovementAdjustment::where('stage', 5)
            ->whereIn('stage_id', $transferIds)
            ->when($reportDate, fn($q) => $q->whereDate('date', '<=', $reportDate))
            ->selectRaw("
                SUM(CASE WHEN type = 1 THEN female_qty ELSE 0 END) as transfer_mortality_female,
                SUM(CASE WHEN type = 1 THEN male_qty ELSE 0 END) as transfer_mortality_male,
                SUM(CASE WHEN type = 2 THEN female_qty ELSE 0 END) as transfer_excess_female,
                SUM(CASE WHEN type = 2 THEN male_qty ELSE 0 END) as transfer_excess_male,
                SUM(CASE WHEN type = 3 THEN female_qty ELSE 0 END) as transfer_shortage_female,
                SUM(CASE WHEN type = 3 THEN male_qty ELSE 0 END) as transfer_shortage_male
            ")
            ->first();

        $transferMortalityFemale = $transferTotals->transfer_mortality_female ?? 0;
        $transferMortalityMale   = $transferTotals->transfer_mortality_male ?? 0;
        $transferExcessFemale    = $transferTotals->transfer_excess_female ?? 0;
        $transferExcessMale      = $transferTotals->transfer_excess_male ?? 0;
        $transferShortageFemale  = $transferTotals->transfer_shortage_female ?? 0;
        $transferShortageMale    = $transferTotals->transfer_shortage_male ?? 0;

        // ------------------------
        // 4. Opening Quantities
        // ------------------------
        $openingFemale = BatchAssign::whereIn('id', $batchAssignIds)->sum('batch_female_qty');
        $openingMale   = BatchAssign::whereIn('id', $batchAssignIds)->sum('batch_male_qty');

        // ------------------------
        // 5. Calculate Closing Birds
        // ------------------------
        $closingFemale = $openingFemale
            - ($mortalityFemale + $cullingFemale + $destroyFemale + $sexErrorFemale)
            - ($shortageFemale + $transferShortageFemale)
            - $mortalityAdjustFemale
            + ($excessFemale + $transferExcessFemale)
            - $transferMortalityFemale; // transfer mortality subtracted

        $closingMale = $openingMale
            - ($mortalityMale + $cullingMale + $destroyMale + $sexErrorMale)
            - ($shortageMale + $transferShortageMale)
            - $mortalityAdjustMale
            + ($excessMale + $transferExcessMale)
            - $transferMortalityMale;

        return [
            'closing_female' => max($closingFemale, 0),
            'closing_male'   => max($closingMale, 0),
        ];
    }
}
