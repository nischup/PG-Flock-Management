<?php

namespace App\Models\Ps;

use Illuminate\Database\Eloquent\Model;

class PsReceiveAttachment extends Model
{
   protected $table = 'ps_receive_attachments';

    protected $fillable = [
        'ps_receive_id',
        'file_path',
        'file_type',
    ];

    // Belongs to one PS Receive
    public function receive()
    {
        return $this->belongsTo(PsReceive::class, 'ps_receive_id');
    }
}
