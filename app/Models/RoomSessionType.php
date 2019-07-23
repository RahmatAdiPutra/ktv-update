<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomSessionType extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'timer_countdown',
        'count_song_played'
    ];

    protected $casts = [
        'timer_countdown' => 'boolean'
    ];

    public function sessions()
    {
		return $this->hasMany(RoomSession::class, 'room_session_type_id');
	}
}
