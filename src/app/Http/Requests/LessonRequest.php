<?php

namespace App\Http\Requests;

class LessonRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'chapter_id' => 'required|numeric',
            'title' => 'required',
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
        $input['chapter_id'] = $this->route('chapter_id');

        return $input;
    }
}
