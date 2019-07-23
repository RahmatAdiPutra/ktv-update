<?php

namespace App\Http\Requests\Web\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\RoomStatus;

class RoomStatusRequest extends FormRequest
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
            'color' => ['required']
        ];
    }

    public function save($post, $id)
    {
        if ($id) {
            $roomStatus = RoomStatus::find($id);
        } else {
            $roomStatus = new RoomStatus();
        }
        
        foreach ($post as $field => $value) {
            $roomStatus->$field = $value;
        }
        $roomStatus->save();
    }
}
