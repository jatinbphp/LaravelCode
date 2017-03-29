<?php

namespace App\Http\Requests;

use DB;
use Illuminate\Foundation\Http\FormRequest;

class ResellerPrice extends FormRequest
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
        $all = $_POST;

        \Validator::extend('unique_media_size_type', function ($attribute, $value, $parameters, $validator) use ($all) {

            if (isset($all['_method']) && $all['_method'] == 'PATCH') {


                $uniqueMediaSizeType = DB::table('reseller_pricing');
                $uniqueMediaSizeType->where('reseller_ID', '=', $all['reseller_ID'])
                    ->where('media_type', '=', $all['media_type'])
                    ->where('media_size', '=', $all['media_size'])
                    ->where('reseller_pricing_id', '!=', \Request::segment(2));
                $total = $uniqueMediaSizeType->count();

                if ($total == 0) {
                    return true;
                } else {
                    return false;
                }

            } else {

                $firstTimeRecordOrNot = DB::table('reseller_pricing')
                    ->where('reseller_ID', '=', $all['reseller_ID'])
                    ->count();

                if ($firstTimeRecordOrNot != 0) {

                    $uniqueMediaSizeType = DB::table('reseller_pricing')
                        ->where('reseller_ID', '=', $all['reseller_ID'])
                        ->where('media_type', '=', $all['media_type'])
                        ->where('media_size', '=', $all['media_size'])
                         ->count();

                    if ($uniqueMediaSizeType == 0) {
                        return true;
                    } else {
                        return false;
                    }

                } else {
                    return true;
                }
            }

        });

        return [
            'currency_contract_conversion_rate' => 'required',
            'license_fee_local'                 => 'required',
            'license_fee_USD'                   => 'required',
            'media_size'                        => 'unique_media_size_type',
            'minimum_local'                     => 'required',
            'minimum_USD'                       => 'required',
            'create_date'                       => 'required',
            'create_by'                         => 'required',
        ];

    }

    public function messages()
    {
        return [
            'media_size.unique_media_size_type' => 'You have already used this combination of Media size and Media type for this Reseller',
        ];
    }
}
