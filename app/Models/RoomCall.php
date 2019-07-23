<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomCall extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'room_id',
        'room_session_id',
        'call_type',
        'responded_at'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function session()
    {
        return $this->belongsTo(RoomSession::class, 'room_session_id');
    }
}
