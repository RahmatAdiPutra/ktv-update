<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomRemote extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'room_session_id',
        'ip_address',
        'blacklist',
        'connected_at',
        'disconnected_at'
    ];

    public function session()
    {
        return $this->belongsTo(RoomSession::class, 'room_session_id');
    }
}
