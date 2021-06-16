<?php

namespace App\Http\Requests;

class UserCreateRequest extends UserRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), ['password' => 'required|min:8']);
    }
}
