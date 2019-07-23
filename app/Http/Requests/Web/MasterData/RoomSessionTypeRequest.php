<?php

namespace App\Http\Requests\Web\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\RoomSessionType;

class RoomSessionTypeRequest extends FormRequest
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
            'timer_countdown' => ['required'],
            'count_song_played' => ['required']
        ];
    }

    public function save($post, $id)
    {
        if ($id) {
            $roomSessionType = RoomSessionType::find($id);
        } else {
            $roomSessionType = new RoomSessionType();
        }
        
        foreach ($post as $field => $value) {
            $roomSessionType->$field = $value;
        }
        $roomSessionType->save();
    }
}
