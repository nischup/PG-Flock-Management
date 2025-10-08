<?php

namespace App\Models\Production;
use App\Models\EggGrade;
use Illuminate\Database\Eloquent\Model;

class EggClassificationGrade extends Model
{
    protected $fillable = [
        'classification_id',
        'egg_grade_id',
        'flock_id',
        'job_no',
        'transaction_no',
        'company_id',
        'project_id',
        'shed_id',
        'batch_no',
        'flock_no',
        'quantity',
        'batchassign_id',
    ];

    public function classification()
    {
        return $this->belongsTo(EggClassification::class, 'classification_id');
    }

    public function grade()
    {
        return $this->belongsTo(EggGrade::class, 'egg_grade_id');
    }
}
