<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\NeoAuth\User;
use Illuminate\Support\Facades\DB;

class Song extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'song_genre_id',
        'song_language_id',
        'title',
        'title_non_latin',
        'type',
        'cover_art',
        'code',
        'lyric',
        'is_new_song',
        'file_path',
        'volume',
        'audio_channel'
    ];

    public function sessions()
    {
        return $this->belongsToMany(RoomSession::class, 'room_sessions_has_songs', 'song_id', 'room_sessions_id');
    }

    public function albums()
    {
        return $this->belongsToMany(Album::class, 'album_has_song', 'song_id', 'album_id');
    }

    public function artists()
    {
        return $this->belongsToMany(Artist::class, 'artist_has_song', 'song_id', 'artist_id');
    }

    public function playlists()
    {
        return $this->belongsToMany(Playlist::class, 'playlist_has_song', 'song_id', 'playlist_id');
    }

    public function userPlaylists()
    {
        return $this->belongsToMany(UserPlaylist::class, 'user_playlist_has_song', 'song_id', 'user_playlist_id');
    }

    public function tags()
    {
        return $this->belongsToMany(SongTag::class, 'song_has_tag', 'song_id', 'song_tag_id');
    }

    public function language()
    {
        return $this->belongsTo(SongLanguage::class, 'song_language_id');
    }

    public function genre()
    {
        return $this->belongsTo(SongGenre::class, 'song_genre_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id')->select('user_id', 'user_name');
    }
}
