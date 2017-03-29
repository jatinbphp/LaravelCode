<?php

namespace App\Http\Requests;

use DB;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class ApiRequest extends FormRequest
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
    public function rules(Request $request)
    {

        $all = $request->all();

        if (!isset($all['secret'])) {
            return ['a'];
            return response()->json([
                'error'   => true,
                'message' => 'API Secret is missing',
            ]);
        }
        $credentials = DB::table('api_credentials')->where('id', '=', 1)->get()->first();

        return [
            //
        ];
    }
}
