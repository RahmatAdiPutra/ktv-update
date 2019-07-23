<?php

namespace App\Http\Requests\Web\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Promotion;

class PromotionRequest extends FormRequest
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
            'name' => ['required'],
            'src' => ['required'],
            'start_date' => ['required'],
            'end_date' => ['required']
        ];
    }

    public function save($post, $id)
    {
        if ($id) {
            $promotion = Promotion::find($id);
        } else {
            $promotion = new Promotion();
        }
        
        foreach ($post as $field => $value) {
            $promotion->$field = $value;
        }
        $promotion->save();
    }
}
