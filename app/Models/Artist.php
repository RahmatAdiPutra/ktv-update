<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\NeoAuth\User;
use Illuminate\Support\Facades\DB;

class Artist extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'artist_category_id',
        'name',
        'photo',
        'code'
    ];

    public function songs()
    {
        return $this->belongsToMany(Song::class, 'artist_has_song', 'artist_id', 'song_id');
    }

    public function albums()
    {
        return $this->belongsToMany(Album::class, 'artists_has_albums', 'artists_id', 'albums_id');
    }

    public function category()
    {
        return $this->belongsTo(ArtistCategory::class, 'artist_category_id');
    }

    public function country()
    {
        return $this->belongsTo(SongLanguage::class, 'language_id')->select('id', 'name');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id')->select('user_id', 'user_name');
    }
}
