<?php

namespace App\Http\Requests\Web\TransactionData;

use App\Models\Playlist;
use Illuminate\Foundation\Http\FormRequest;

class PlaylistRequest extends FormRequest
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
            'playlist_category_id' => [],
            'name' => [],
        ];
    }

    public function save($post, $id)
    {
        // dd($id);
        if ($id) {
            $playlist = Playlist::find($id);
        } else {
            $playlist = new Playlist();
        }
        
        foreach ($post as $field => $value) {
            $playlist->$field = $value;
        }
        $playlist->save();
    }
}
