<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddUser extends FormRequest
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
            'user_name'             => 'required|max:255|unique:reseller_user',
            'user_email'            => 'required|email|max:255|unique:reseller_user',
            'password'              => 'required|min:3|confirmed',
            'password_confirmation' => 'required|min:3',
        ];
    }
}
