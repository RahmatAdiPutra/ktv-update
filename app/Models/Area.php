<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name'
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class)->orderBy('name');
    }
}
