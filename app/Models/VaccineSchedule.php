<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VaccineSchedule extends Model
{
    protected $fillable = [
        'company_id',
        'job_no',
        'project_id',
        'flock_no',
        'shed_id',
        'batch_no',
        'breed_type_id',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    /**
     * Get the company that owns the vaccine schedule.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Company::class);
    }

    /**
     * Get the project that owns the vaccine schedule.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Project::class);
    }

    /**
     * Get the shed that owns the vaccine schedule.
     */
    public function shed(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Shed::class);
    }

    /**
     * Get the breed type that owns the vaccine schedule.
     */
    public function breedType(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\BreedType::class);
    }

    /**
     * Get the vaccine schedule details for the vaccine schedule.
     */
    public function vaccineScheduleDetails(): HasMany
    {
        return $this->hasMany(VaccineScheduleDetail::class);
    }
}
