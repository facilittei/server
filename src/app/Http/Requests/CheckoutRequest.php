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
            'description' => 'required',
            'amount' => 'required|numeric',
            'customer.name' => 'required',
            'customer.email' => 'required|email',
            'customer.document' => 'required|max:11',
            'customer.address.street' => 'required',
            'customer.address.number' => 'required',
            'customer.address.state' => 'required',
            'customer.address.post_code' => 'required',
            'credit_card' => 'required',
            'course_id' => 'required',
        ];
    }
}
