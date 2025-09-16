<?php

namespace App\Models\DailyOperation;
use  App\Models\Shed\BatchAssign;
use  App\Models\User;

use Illuminate\Database\Eloquent\Model;

class DailyOperation extends Model
{
    protected $fillable = [
        'batchassign_id',   // FK to batch_assigns
        'operation_date',   // Date of the operation
        'created_by',       // User who created this record
        'updated_by',       // User who updated (nullable)
        'status',
        'job_no', 
        'transaction_no',  
        'flock_no',  
        'flock_id', 
        'company_id', 
        'shed_id',
        'batch_no', 
        'stage',   // Status (default 1)
    ];

    protected $casts = [
        
        'operation_date' => 'date',
        
    ];
   
    public function batchAssign()
    {
        return $this->belongsTo(BatchAssign::class, 'batchassign_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function mortalities()
    {
        return $this->hasMany(DailyMortality::class);
    }

    public function destroys()
    {
        return $this->hasMany(DailyDestroy::class);
    }

    public function cullings()
    {
        return $this->hasMany(DailyCulling::class);
    }

    public function sexingErrors()
    {
        return $this->hasMany(DailySexingError::class);
    }

    public function feeds()
    {
        return $this->hasMany(DailyFeed::class);
    }

    public function waters()
    {
        return $this->hasMany(DailyWater::class);
    }

    public function lights()
    {
        return $this->hasMany(DailyLight::class);
    }

    public function weights()
    {
        return $this->hasMany(DailyWeight::class);
    }

    public function temperatures()
    {
        return $this->hasMany(DailyTemperature::class);
    }

    public function feedingPrograms()
    {
        return $this->hasMany(DailyFeedingProgram::class);
    }

    public function feedFinishings()
    {
        return $this->hasMany(DailyFeedFinishing::class);
    }

    public function humidities()
    {
        return $this->hasMany(DailyHumidity::class);
    }

    public function eggCollections()
    {
        return $this->hasMany(DailyEggCollection::class);
    }

    public function medicines()
    {
        return $this->hasMany(DailyMedicine::class);
    }

    public function vaccines()
    {
        return $this->hasMany(DailyVaccine::class);
    }
}
