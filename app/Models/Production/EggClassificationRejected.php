<?php

namespace App\Models\Production;
use App\Models\Master\EggType;
use Illuminate\Database\Eloquent\Model;



class EggClassificationRejected extends Model
{
    protected $fillable = [
        'classification_id', // foreign key to egg_classifications
        'egg_type_id',       // foreign key to egg_types
        'quantity',          // number of eggs
        'note',              // optional note
    ];

    /**
     * Relation to master classification
     */
    public function classification()
    {
        return $this->belongsTo(EggClassification::class, 'classification_id');
    }

    /**
     * Relation to egg type
     */
    public function eggType()
    {
        return $this->belongsTo(EggType::class, 'egg_type_id');
    }
}
