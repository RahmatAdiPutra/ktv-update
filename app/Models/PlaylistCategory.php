<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaylistCategory extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'name'
    ];

    public function playlists()
    {
		return $this->hasMany(Playlist::class, 'playlist_category_id');
	}
}
