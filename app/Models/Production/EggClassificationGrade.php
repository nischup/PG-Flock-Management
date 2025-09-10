<?php

namespace App\Models\Production;
use App\Models\Master\EggGrade;
use Illuminate\Database\Eloquent\Model;

class EggClassificationGrade extends Model
{
    protected $fillable = [
        'classification_id',
        'egg_grade_id',
        'quantity',
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
