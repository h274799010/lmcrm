<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class AgentFormRequest extends Request
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
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
            'first_name' => 'required',
            'last_name' => 'required',
            'spheres' => 'required',
            'company' => 'required',
            //'lead_revenue_share' => 'required|numeric',
            //'payment_revenue_share' => 'required|numeric',
            'role' => 'required',
        ];
    }
}
