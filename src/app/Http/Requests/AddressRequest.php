<?php

namespace App\Http\Requests;

class AddressRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'postcode' => 'required',
            'street' => 'required',
            'number' => 'required',
            'state' => 'required',
            'city' => 'required',
            'country' => 'required',
        ];
    }
}
