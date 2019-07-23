<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Middleware\RoomSession;

class SessionMovedHistory extends Model
{

    public $timestamps = false;

    public $fillable = [
        'room_session_id',
        'to_room_id',
        'from_room_id',
        'moved_by',
        'moved_at'
    ];

    public function toRoom()
    {
        return $this->belongsTo(Room::class, 'to_room_id');
    }

    public function fromRoom()
    {
        return $this->belongsTo(Room::class, 'from_room_id');
    }

    public function session()
    {
        return $this->belongsTo(RoomSession::class, 'room_session_id');
    }

    public function movedBy()
    {
        return $this->belongsTo(User::class, 'moved_by');
    }
}
