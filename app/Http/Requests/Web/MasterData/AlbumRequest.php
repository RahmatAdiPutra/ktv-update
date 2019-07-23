<?php

namespace App\Http\Requests\Web\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Album;

class AlbumRequest extends FormRequest
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
            'title' => ['required'],
            'release_date' => ['required'],
            'cover_art' => ['required'],
            'code' => ['required']
        ];
    }

    public function save($post, $id)
    {
        if ($id) {
            $album = Album::find($id);
        } else {
            $album = new Album();
        }
        
        foreach ($post as $field => $value) {
            $album->$field = $value;
        }
        $album->save();
    }
}
