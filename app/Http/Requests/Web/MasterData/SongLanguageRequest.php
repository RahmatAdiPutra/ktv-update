<?php

namespace App\Http\Requests\Web\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\SongLanguage;

class SongLanguageRequest extends FormRequest
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
            $songLanguage = SongLanguage::find($id);
        } else {
            $songLanguage = new SongLanguage();
        }
        
        foreach ($post as $field => $value) {
            $songLanguage->$field = $value;
        }
        $songLanguage->save();
    }
}
