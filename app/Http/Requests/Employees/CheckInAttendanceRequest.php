<?php

namespace App\Http\Requests\Employees;

use Illuminate\Foundation\Http\FormRequest;

class CheckInAttendanceRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'check_in' => 'required',
        ];
    }
}
