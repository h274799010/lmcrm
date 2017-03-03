<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Models\User;
use App\Models\UserPhones;

class UserPhonesRequest extends Request
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
        $rules = array(
            'phone' => 'required|unique:user_phones,phone|max:15|regex:/^([0-9\s\-\+\(\)]*)$/',
            'comment' => 'max:255'
        );

        return $rules;
    }
}
