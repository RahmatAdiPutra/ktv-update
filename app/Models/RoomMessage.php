<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomMessage extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'room_session_id',
        'message',
        'is_important',
        'read_at'
    ];

    public function session()
    {
        return $this->belongsTo(RoomSession::class, 'room_session_id');
    }
}
