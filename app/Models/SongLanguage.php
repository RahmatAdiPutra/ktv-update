<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SongLanguage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name'
    ];

    public function songs()
    {
		return $this->hasMany(Song::class, 'song_language_id');
    }
}
