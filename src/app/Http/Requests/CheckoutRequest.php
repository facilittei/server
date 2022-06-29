<?php

namespace App\Http\Requests;

class CheckoutRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'credit_card' => 'required',
            'course_id' => 'required',
        ];
    }
}
