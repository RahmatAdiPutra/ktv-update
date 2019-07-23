<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Playlist extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'playlist_category_id',
        'name'
    ];

    public function songs()
    {
        return $this->belongsToMany(Song::class, 'playlist_has_song', 'playlist_id', 'song_id');
    }

    public function category()
    {
        return $this->belongsTo(PlaylistCategory::class, 'playlist_category_id');
    }
}
