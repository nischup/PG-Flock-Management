<?php

namespace App\Models\Production;
use App\Models\Shed\BatchAssign;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class EggClassification extends Model
{
    protected $fillable = [
        'batchassign_id',    // foreign key to batch_assigns
        'classification_date',
        'total_eggs',
        'rejected_eggs',
        'technical_eggs',
        'hatching_eggs',
        'commercial_eggs',
        'remarks',
        'created_by',        // foreign key to users
    ];

    /**
     * Relation to batch assign
     */
    public function batchAssign()
    {
        return $this->belongsTo(BatchAssign::class, 'batchassign_id');
    }

    /**
     * Relation to creator (user)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Rejected eggs
     */
    public function rejectedEggs()
    {
        return $this->hasMany(EggClassificationRejected::class, 'classification_id');
    }

    /**
     * Technical eggs
     */
    public function technicalEggs()
    {
        return $this->hasMany(EggClassificationTechnical::class, 'classification_id');
    }

    public function grades()
    {
        return $this->hasMany(EggClassificationGrade::class, 'classification_id')->with('grade');
    }
}
