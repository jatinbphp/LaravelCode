<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResellerProfile extends FormRequest
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
            'user_Email'                        => 'required',
            'create_by'                         => 'required',
            'contract_start'                    => 'required',
            'contract_end'                      => 'required',
            'create_date'                       => 'required',
            'reseller_name'                     => 'required',
            'contract_currency_conversion_rate' => 'required|numeric',
            'commission_rate'                   => 'required|numeric',
            'low_rate_trigger'                  => 'required|numeric',
            'seat_license_fee'                  => 'required|numeric',
            'commission_rate_Low'               => 'required|numeric',

        ];
    }

    public function messages()
    {
        return [
            'user_Email.required' => 'username field is required.',
        ];
    }
}
