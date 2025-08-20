<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    protected $table = 'feeds';

    protected $fillable = [
        'feed_type_id', // references feed_types table
        'feed_name',
        'status',
    ];

    // Relation to FeedType
    public function feedType()
    {
        return $this->belongsTo(FeedType::class, 'feed_type_id');
    }
}
