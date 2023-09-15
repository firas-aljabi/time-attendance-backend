<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class CreateComapnyRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
            "name" => "required|string",
            "email" => "required|email",
            'longitude' => 'required',
            'latitude' => 'required',
            'radius' => 'required',
        ];
    }
}
