<?php

namespace App\Http\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateUserProfileRequest extends FormRequest
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

        switch ($this->getMethod()) {

            case 'POST':{
                    
                    return [
                        'user_name'        => 'required|max:255|unique:reseller_user,user_name,' . Auth::user()->id,
                        'user_email'       => 'required|email|max:255|unique:reseller_user,user_email,' . Auth::user()->id,
                        'password'         => 'required_with:confirm_password|same:confirm_password|min:6',
                        'confirm_password' => 'required_with:password|min:6',
                    ];

                }
            case 'PATCH':{
                    
                    return [
                        'user_name'        => 'required|max:255|unique:reseller_user,user_name,' . Request::segment(2),
                        'user_email'       => 'required|email|max:255|unique:reseller_user,user_email,' . Request::segment(2),
                        'password'         => 'required_with:confirm_password|same:confirm_password|min:6',
                        'confirm_password' => 'required_with:password|min:6',
                    ];

                }

            default:break;

        }

    }
}
