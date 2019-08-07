<?php

namespace App\Http\Requests\Web\TransactionData;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Song;

class SongRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'song_genre_id' => [],
            'song_language_id' => [],
            'title' => [],
            'title_non_latin' => [],
            'artist_label' => [],
            'type' => [],
            'cover_art' => [],
            'code' => [],
            'lyric' => [],
            'is_new_song' => [],
            'file_path' => [],
            'volume' => [],
            'audio_channel' => [],
            'release_year' => [],
            'updated_by' => []
        ];
    }

    public function save($post, $id)
    {
        if ($id) {
            $song = Song::find($id);
        } else {
            $song = new Song();
        }
        
        foreach ($post as $field => $value) {
            $song->$field = $value;
        }
        $song->save();
    }
}
