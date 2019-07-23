<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SongTag extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name'
    ];

    public function songs()
    {
        return $this->belongsToMany(Song::class, 'song_has_tag', 'song_id', 'song_tag_id');
    }
}
