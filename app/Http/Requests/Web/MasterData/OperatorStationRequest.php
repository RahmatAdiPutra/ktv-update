<?php

namespace App\Http\Requests\Web\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\OperatorStation;

class OperatorStationRequest extends FormRequest
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
            'ip_address' => ['required'],
            'name' => ['required']
        ];
    }

    public function save($post, $id)
    {
        if ($id) {
            $operator = OperatorStation::find($id);
        } else {
            $operator = new OperatorStation();
        }
        
        foreach ($post as $field => $value) {
            $operator->$field = $value;
        }
        $operator->save();
    }
}
