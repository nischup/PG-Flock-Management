<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class FeedType extends Model
{
    protected $table = 'feed_types';

    protected $fillable = [
        'name',
        'status',
    ];
}
