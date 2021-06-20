<?php

namespace App\Http\Requests;

class CourseAnnulRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'users_id' => 'required|array',
        ];
    }
}
