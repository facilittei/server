<?php

namespace App\Http\Requests;

class CommentRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'lesson_id' => 'required|numeric',
            'description' => 'required',
        ];
    }

    /**
     * Get all of the input and files for the request.
     *
     * @param  array|mixed|null  $keys
     * @return array
     */
    public function all($keys = null)
    {
        $input = parent::all();
        $input['lesson_id'] = $this->route('lesson_id');
        return $input;
    }
}
