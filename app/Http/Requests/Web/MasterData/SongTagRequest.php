<?php

namespace App\Http\Requests\Web\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\SongTag;

class SongTagRequest extends FormRequest
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
            'name' => ['required']
        ];
    }

    public function save($post, $id)
    {
        if ($id) {
            $songTag = SongTag::find($id);
        } else {
            $songTag = new SongTag();
        }
        
        foreach ($post as $field => $value) {
            $songTag->$field = $value;
        }
        $songTag->save();
    }
}
