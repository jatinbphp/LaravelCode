<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ResellerProfile;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
     */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'user_name'  => 'required|max:255|unique:reseller_user',
            'user_email' => 'required|email|max:255|unique:reseller_user',
            'password'   => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {

        $user = User::create([
            'user_name'     => $data['user_name'],
            'user_email'    => $data['user_email'],
            'password'      => bcrypt($data['password']),
            'user_type'     => '1',
            'create_date'   => date('Y-m-d H:i:s'),
            'create_by'     => '1',
            'record_status' => '1',
            '_token'        => '1',
        ]);

        ////// Add data in profile table also, so when new user created, His profile is also ready \\\\\
        

        ResellerProfile::insert([            
            'user_id'                           => $user->id,
            'user_Email'                        => $data['user_email'],
            'reseller_name'                     => $data['user_name'],
            'country_code'                      => 'US',
            'currency_code'                     => 'USD',
            'language_primary'                  => 'en',
            'languag_secondary'                 => 'en',
            'contract_start'                    => date('Y-m-d'),
            'contract_end'                      => date('Y-m-d'),
            'reseller_type'                     => '1',
            'contract_currency_conversion_rate' => '0',
            'low_rate_trigger'                  => '0',
            'seat_license_fee'                  => '0',
            'reseller_status'                   => '1',
            'commission_rate'                   => '0',
            'commission_rate_Low'               => '0',
            'create_by'                         => 'admin',
            'create_date'                       => date('Y-m-d H:i:s'),
            'reduced_commission_rate'           => 1,
        ]);
        ////// Add data in profile table also, so when new user created, His profile is also ready End \\\\\

        return $user;
    }
}
