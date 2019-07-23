<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Album extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'release_date',
        'cover_art',
        'code'
    ];

    public function songs()
    {
        return $this->belongsToMany(Song::class, 'album_has_song', 'album_id', 'song_id');
    }
}
