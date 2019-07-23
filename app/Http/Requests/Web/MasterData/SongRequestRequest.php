<?php

namespace App\Http\Requests\Web\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\SongRequest;

class SongRequestRequest extends FormRequest
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
            'artist' => ['required'],
            'processed' => ['required']
        ];
    }

    public function save($post, $id)
    {
        if ($id) {
            $songRequest = SongRequest::find($id);
        } else {
            $songRequest = new SongRequest();
        }
        
        foreach ($post as $field => $value) {
            $songRequest->$field = $value;
        }
        $songRequest->save();
    }
}
