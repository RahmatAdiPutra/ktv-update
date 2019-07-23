<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomSession extends Model
{

    public $timestamps = false;

    protected $fillable = [
        'area_id',
        'room_session_type_id',
        'room_type_id',
        'guest_name',
        'opened_by',
        'opened_at',
        'closed_by',
        'closed_at',
        'hour_duration'
    ];

    protected $casts = [
        'timer_countdown' => 'boolean'
    ];

    public function remotes()
    {
        return $this->hasMany(RoomRemote::class);
    }

    public function messages()
    {
        return $this->hasMany(RoomMessage::class);
    }

    public function calls()
    {
        return $this->hasMany(RoomCall::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function sessionType()
    {
        return $this->belongsTo(RoomSessionType::class, 'room_session_type_id');
    }

    public function songs()
    {
        return $this->belongsToMany(Song::class, 'room_session_has_song')
            ->withPivot('is_played', 'order_num', 'count_play')
            ->orderBy('order_num');
    }

    public function init()
    {
        return [
            'token' => $this->room->token,
            'guestName' => $this->guest_name,
            'playlist' => $this->songs
        ];
    }
}
