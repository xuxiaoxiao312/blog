<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TagCreateRequest extends FormRequest
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
            'tag' => 'bail|required|unique:tags,tag', // unique:数据表，字段名
            'title' => 'required',
            'subtitle' =>'required',
            'layout' => 'required',
            'page_image' => 'nullable',
            'meta_description'=>'nullable'
        ];
    }
}
