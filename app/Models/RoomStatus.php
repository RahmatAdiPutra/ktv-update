<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomStatus extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'color'
    ];

    public function rooms()
    {
		return $this->hasMany(Room::class, 'room_status_id');
	}
}
