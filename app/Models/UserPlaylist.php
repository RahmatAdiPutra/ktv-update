<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPlaylist extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name'
    ];

    public function songs()
    {
        return $this->belongsToMany(Song::class, 'user_playlist_has_song', 'user_playlist_id', 'song_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
