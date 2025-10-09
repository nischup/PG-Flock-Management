<?php

namespace App\Models\Production;
use App\Models\EggGrade;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class EggClassificationGradeDetail extends Model
{
    use HasFactory;

    protected $table = 'egg_classification_grade_details';

    protected $fillable = [
        'egg_classification_grade_id',
        'egg_grade_id',
        'quantity',
    ];

    /**
     * Each grade detail belongs to an egg classification grade
     */
    public function classificationGrade()
    {
        return $this->belongsTo(EggClassificationGrade::class, 'egg_classification_grade_id');
    }

    /**
     * Each grade detail is associated with an egg grade
     */
    public function eggGrade()
    {
        return $this->belongsTo(EggGrade::class, 'egg_grade_id');
    }
}
