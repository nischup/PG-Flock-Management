<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'code',
        'description',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the company that owns the project.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Company::class);
    }

    /**
     * Get the vaccine schedules for the project.
     */
    public function vaccineSchedules(): HasMany
    {
        return $this->hasMany(VaccineSchedule::class);
    }
}
