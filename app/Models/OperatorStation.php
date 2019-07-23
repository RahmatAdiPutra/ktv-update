<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorStation extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'ip_address',
        'name'
    ];

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'operator_station_has_room', 'operator_station_id', 'room_id');
    }
}
