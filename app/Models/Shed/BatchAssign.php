<?php

namespace App\Models\Shed;

use Illuminate\Database\Eloquent\Model;
use App\Models\Master\Company;
use App\Models\Master\Flock;
use App\Models\Master\Shed;
use App\Models\Master\Batch;
use App\Models\Shed\ShedReceive;
use Illuminate\Database\Eloquent\Builder;
class BatchAssign extends Model
{
    protected $fillable = [
        'shed_receive_id',
        'job_no',
        'transaction_no',
        'flock_no',
        'flock_id',
        'company_id',
        'shed_id',
        'level',
        'batch_no',
        'batch_female_qty',
        'batch_male_qty',
        'batch_total_qty',
        'batch_female_mortality',
        'batch_male_mortality',
        'batch_total_mortality',
        'batch_excess_male',
        'batch_excess_female',
        'batch_sortage_male',
        'batch_sortage_female',
        'percentage',
        'created_by',
        'updated_by',
        'stage',
    ];

    protected $casts = [
        'created_at' => 'date',
        'growing_start_date' => 'date',
        'transfer_date' => 'date',
    ];

    // Relationship to shed receive
    public function shedReceive()
    {
        return $this->belongsTo(ShedReceive::class);
    }

    // Optional: relationships to flock, company, shed
    public function flock()
    {
        return $this->belongsTo(Flock::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function shed()
    {
        return $this->belongsTo(Shed::class);
    }
    public function batch()
    {
         return $this->belongsTo(Batch::class, 'batch_no', 'id');
    }



    // --- Filter scope
    public function scopeApplyFilters($query, $filters)
    {
        return $query
            ->when($filters['company'], fn($q, $company) => $q->where('company_id', $company))
            ->when($filters['project'], fn($q, $project) => $q->whereHas('flock.project', fn($sub) => $sub->where('id', $project)))
            ->when($filters['flock'], fn($q, $flock) => $q->where('flock_id', $flock))
            ->when($filters['shed'], fn($q, $shed) => $q->where('shed_id', $shed))
            ->when($filters['batch'], fn($q, $batch) => $q->where('id', $batch))
            ->when($filters['date'], function($q, $date) use ($filters) {
                if ($date === 'Last 7 Days') {
                    $q->where('created_at', '>=', now()->subDays(7));
                } elseif ($date === 'Last 1 Month') {
                    $q->where('created_at', '>=', now()->subMonth());
                } elseif ($date === 'Custom' && $filters['date_from'] && $filters['date_to']) {
                    $q->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
                }
            });
    }
}
