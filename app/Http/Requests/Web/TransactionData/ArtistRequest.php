<?php

namespace App\Http\Requests\Web\TransactionData;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Artist;

class ArtistRequest extends FormRequest
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
            'artist_category_id' => [],
            'language_id' => [],
            'name' => [],
            'name_non_latin' => [],
            'photo' => [],
            'code' => [],
            'popularity' => [],
            'flag_check' => [],
            'updated_by' => []
        ];
    }

    public function save($post, $id)
    {
        // dd($id);
        if ($id) {
            $artist = Artist::find($id);
        } else {
            $artist = new Artist();
        }
        
        foreach ($post as $field => $value) {
            $artist->$field = $value;
        }
        $artist->save();
    }
}
