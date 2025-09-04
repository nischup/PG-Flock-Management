<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VaccineScheduleDetail extends Model
{
    protected $table = 'vaccine_schedules_details';

    protected $fillable = [
        'vaccine_schedule_id',
        'disease_id',
        'vaccine_id',
        'age',
        'vaccination_date',
        'next_vaccination_date',
        'status',
        'notes',
        'administered_by',
        'is_active',
    ];

    protected $casts = [
        'vaccination_date' => 'date',
        'next_vaccination_date' => 'date',
        'is_active' => 'integer',
    ];

    /**
     * Get the vaccine schedule that owns the detail.
     */
    public function vaccineSchedule(): BelongsTo
    {
        return $this->belongsTo(VaccineSchedule::class);
    }

    /**
     * Get the disease that owns the detail.
     */
    public function disease(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Disease::class);
    }

    /**
     * Get the vaccine that owns the detail.
     */
    public function vaccine(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Vaccine::class);
    }
}
