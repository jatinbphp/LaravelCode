<?php

namespace App\Services;

use App\Models\ApiCredentials;
use App\Models\ResellerPricing;
use App\Models\ResellerProfile;
use App\Models\ResellerUser as RU;
use App\User;
use Auth;
use DB;
use URL;

class DashboardService
{

    /**
     * Service for add data into seller profile table
     * @param array $data
     * @return true false based on result
     */
    public static function saveResellerProfile($data)
    {
        try {
            $currentUser = Auth::User();
            unset($data['_token']);
            //$lastAddedRecord     = ResellerProfile::orderBy('reseller_ID', 'desc')->first();
            //$reseller_ID         = $lastAddedRecord->reseller_ID + 1;
            //$data['reseller_ID'] = $reseller_ID;
            $data['user_id'] = $currentUser->id;
            //$data['user_Email']  = $currentUser->user_email;
            $data['create_date'] = date('Y-m-d', strtotime($data['create_date']));
            $data['created_at']  = date('Y-m-d');

            ResellerProfile::insert($data);
            $result['result']   = 'success';
            $result['redirect'] = 'resellerprofilelist';
            $result['message']  = 'Reseller Profile Created Successfully!';

        } catch (\Exception $e) {
            $result['result']   = 'error';
            $result['message']  = $e->getMessage();
            $result['redirect'] = 'resellerprofile';
        }
        return $result;

    }

    /**
     * Update Reseller Profile data into table
     * @param array $data
     * @param int $id
     * @return $data varialbe as result
     */
    public static function updateResellerProfile($data, $id)
    {
        try {
            $currentUser = Auth::User();
            //$data['user_id']                       = $currentUser->id;
            $data['create_date']                   = date('Y-m-d', strtotime($data['create_date']));
            $data['updated_at']                    = date('Y-m-d');
            $up                                    = ResellerProfile::find($id);
            $up->user_Email                        = $data['user_Email'];
            $up->reseller_status                   = $data['reseller_status'];
            $up->reseller_name                     = $data['reseller_name'];
            $up->country_code                      = $data['country_code'];
            $up->currency_code                     = $data['currency_code'];
            $up->contract_currency_conversion_rate = $data['contract_currency_conversion_rate'];
            $up->language_primary                  = trim($data['language_primary']);
            $up->languag_secondary                 = trim($data['languag_secondary']);
            $up->contract_start                    = $data['contract_start'];
            $up->contract_end                      = $data['contract_end'];
            $up->reseller_type                     = $data['reseller_type'];
            $up->low_rate_trigger                  = $data['low_rate_trigger'];
            $up->commission_rate                   = $data['commission_rate'];
            $up->commission_rate_Low               = $data['commission_rate_Low'];
            $up->seat_license_fee                  = $data['seat_license_fee'];
            $up->reduced_commission_rate           = $data['reduced_commission_rate'];
            $up->create_by                         = $data['create_by'];
            $up->create_date                       = $data['create_date'];
            // $up->user_id                           = $data['user_id'];
            $up->save();
            $result['result']   = 'success';
            $result['redirect'] = 'userlist';
            $result['message']  = 'Reseller Profile Updated Successfully!';
        } catch (\Exception $e) {
            echo $e->getMessage();exit;
            $result['result']   = 'error';
            $result['message']  = $e->getMessage();
            $result['redirect'] = 'profileselleredit/' . $id;
        }
        return $result;

    }

    /**
     *
     * @param  array $data
     * @param  int $id
     * @return array with result
     */
    public static function updateUserProfile($data, $id = null)
    {
        if ($id == null) {
            $id       = Auth::user()->id;
            $redirect = 'account';
        } else {
            $redirect = 'userlist';
        }

        try {
            $result = [
                'user_name'  => $data['user_name'],
                'user_email' => $data['user_email'],
                'password'   => bcrypt($data['password']),
            ];
            if ($data['password'] == "") {
                unset($result['password']);
            }

            User::find($id)->update($result);
            $return['redirect'] = $redirect;
            $return['result']   = 'success';
            $return['message']  = 'Profile Updated Successfully!';

        } catch (\Exception $e) {
            $return['redirect'] = $redirect;
            $return['result']   = 'error';
            $return['message']  = $e->getMessage();
        }
        return $return;
    }

    /*
     * Update / Save reseller Profile
     * @param array $data
     * @return array of result
     *
     */
    public static function saveorUpdateResellerProfile($data)
    {
        try {
            $currentUser = Auth::User();
            unset($data['_token']);
            //$lastAddedRecord     = ResellerProfile::orderBy('reseller_ID', 'desc')->first();
            //$reseller_ID         = $lastAddedRecord->reseller_ID + 1;
            //$data['reseller_ID'] = $reseller_ID;
            $data['user_id'] = $currentUser->id;
            //$data['user_Email']  = $currentUser->user_email;
            $data['create_date'] = date('Y-m-d', strtotime($data['create_date']));
            $data['created_at']  = date('Y-m-d');

            $alreadyProfileAdded = DB::table('reseller_profile')
                ->where('user_id', '=', $currentUser->id)
                ->first();

            if (count($alreadyProfileAdded) == 0) {
                ResellerProfile::insert($data);
            } else {

                $up                                    = ResellerProfile::find($alreadyProfileAdded->reseller_ID);
                $up->user_Email                        = $data['user_Email'];
                $up->reseller_status                   = $data['reseller_status'];
                $up->reseller_name                     = $data['reseller_name'];
                $up->country_code                      = $data['country_code'];
                $up->currency_code                     = $data['currency_code'];
                $up->contract_currency_conversion_rate = $data['contract_currency_conversion_rate'];
                $up->language_primary                  = trim($data['language_primary']);
                $up->languag_secondary                 = trim($data['languag_secondary']);
                $up->contract_start                    = $data['contract_start'];
                $up->contract_end                      = $data['contract_end'];
                $up->reseller_type                     = $data['reseller_type'];
                $up->low_rate_trigger                  = $data['low_rate_trigger'];
                $up->commission_rate                   = $data['commission_rate'];
                $up->commission_rate_Low               = $data['commission_rate_Low'];
                $up->seat_license_fee                  = $data['seat_license_fee'];
                $up->reduced_commission_rate           = $data['reduced_commission_rate'];
                $up->create_by                         = $data['create_by'];
                $up->save();
                //ResellerProfile::find($alreadyProfileAdded->reseller_ID)->update($data);
            }

            $result['result']   = 'success';
            $result['redirect'] = 'myprofile';
            $result['message']  = 'Profile Updated Successfully!';

        } catch (\Exception $e) {
            $result['result']   = 'error';
            $result['message']  = $e->getMessage();
            $result['redirect'] = 'myprofile';
        }

        return $result;
    }

    /**
     * fetch particular column from given id with given primaray key
     * @param int $id id which we will use in where
     * @param string $table name of table
     * @param string $column column which we want in return
     * @param string $searchColumn comparision column against which we will search
     * @return result of column
     */
    public static function fetchDataFromTable($id, $table, $column, $searchColumn)
    {
        try {
            return DB::table($table)->select(array($column))
                ->where($searchColumn, '=', $id)
                ->first()->$column;
        } catch (\Exception $e) {
            return redirect(URL::previous())->with('error', $e->getMessage());
        }
    }

    /**
     * send out put as csv
     * @param  string $filename name of file which need to be send as output
     * @return csv
     */
    public static function download_send_headers($filename)
    {

        // disable caching
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");

    }

    /**
     * manage data for sending to csv
     * @param  array  &$array dataase data
     * @return [type]         [description]
     */
    public static function array2csv($array)
    {
        $array = json_decode(json_encode($array), true);

        foreach ($array as $element) {
            $hash                = $element['sales_report_ID'];
            $unique_array[$hash] = $element;
        }
        $array = $unique_array;
        $df    = fopen("php://output", 'w');
        fputcsv($df, array_keys(reset($array)));
        foreach ($array as $row) {
            fputcsv($df, $row);
        }
        fclose($df);
        exit;
    }

    /**
     * download sales report data with
     * @param int $id
     * @return it will call array2csv function
     */
    public static function expoertResellerSaleCsv($id)
    {
        $data = DB::table('reseller_sales_report');
        $data->select(['reseller_sales_report.*', 'reseller_sales_report_items.*']);
        $data->Leftjoin('reseller_sales_report_items', 'reseller_sales_report_items.sales_report_ID'
            , '=', 'reseller_sales_report.sales_report_ID');
        if ($id != "") {
            $data->where('reseller_sales_report.sales_report_ID', '=', $id);
        }
        $a = $data->get();
        self::download_send_headers("sales_report_data_export_" . date("Y-m-d") . ".csv");
        $result = $a->toArray();
        self::array2csv($result);
        exit;
    }

    /**
     * download sales report data with
     * @param int @id
     */
    public static function resellerSalesCsvAll($id)
    {
        $data = DB::table('reseller_sales_report');
        $data->select(['reseller_sales_report.*', 'reseller_sales_report_items.*']);
        $data->Leftjoin('reseller_sales_report_items', 'reseller_sales_report_items.sales_report_ID'
            , '=', 'reseller_sales_report.sales_report_ID');
        if ($id != "") {
            $data->where('reseller_sales_report.reseller_ID', '=', $id);
        }
        $a = $data->get();
        self::download_send_headers("sales_report_data_export_" . date("Y-m-d") . ".csv");
        $result = $a->toArray();
        self::array2csv($result);
        exit;
    }

    /**
     * Store Reseller Price into reseller_pricing table
     * @param array $data
     * @return json data
     */
    public static function storeResellerPrice($data)
    {
        try {
            $resellerPrice                                    = new ResellerPricing();
            $resellerPrice->reseller_ID                       = $data['reseller_ID'];
            $resellerPrice->media_type                        = $data['media_type'];
            $resellerPrice->media_size                        = $data['media_size'];
            $resellerPrice->currency_contract_conversion_rate = $data['currency_contract_conversion_rate'];
            $resellerPrice->license_fee_local                 = $data['license_fee_local'];
            $resellerPrice->license_fee_USD                   = $data['license_fee_USD'];
            $resellerPrice->minimum_local                     = $data['minimum_local'];
            $resellerPrice->minimum_USD                       = $data['minimum_USD'];
            $resellerPrice->create_date                       = date('Y-m-d', strtotime($data['create_date']));
            $resellerPrice->created_by                        = $data['create_by'];
            $resellerPrice->save();
            $result['result']  = 'success';
            $result['message'] = 'Price Saved Successfully.';
            //$result['redirect'] = 'resellerpricing/'.$data['resellerProfileId'];
            $result['redirect'] = 'resellerpricing';
        } catch (\Exception $e) {
            $result['result']   = 'error';
            $result['message']  = $e->getMessage();
            $result['redirect'] = URL::previous();
        }

        return $result;
    }

    /**
     * Update reseller Price in database table
     * @param array $data
     * @param integer $id
     * @return result array
     */
    public static function UpdateResellerPrice($data, $id, $pid)
    {
        try {

            $resellerPrice = ResellerPricing::find($id);

            $resellerPrice->media_type                        = $data['media_type'];
            $resellerPrice->media_size                        = $data['media_size'];
            $resellerPrice->currency_contract_conversion_rate = $data['currency_contract_conversion_rate'];
            $resellerPrice->license_fee_local                 = $data['license_fee_local'];
            $resellerPrice->license_fee_USD                   = $data['license_fee_USD'];
            $resellerPrice->minimum_local                     = $data['minimum_local'];
            $resellerPrice->minimum_USD                       = $data['minimum_USD'];
            $resellerPrice->create_date                       = date('Y-m-d', strtotime($data['create_date']));
            $resellerPrice->created_by                        = $data['create_by'];
            $resellerPrice->save();
            $result['result']   = 'success';
            $result['message']  = 'Price Updated Successfully.';
            $result['redirect'] = 'resellerpricing';
        } catch (\Exception $e) {
            $result['result']   = 'error';
            $result['message']  = $e->getMessage();
            $result['redirect'] = URL::previous();
        }

        return $result;
    }

    /**
     * change status of user as give parameter
     * @param int $id
     * @param int @status
     * @return true false
     */
    public static function changeStatus($id, $status)
    {
        $user            = User::find($id);
        $user->user_type = $status;
        $user->save();
    }

    /**
     * Service for fetch data Reseller Pricing for particular ID
     * @param int $id
     * @return array
     */
    public static function fetchResellerPricingById($id)
    {

        try {
            $data = DB::table('reseller_pricing')
                ->select(array('reseller_pricing.*', 'reseller_user.user_name', 'reseller_media_type.name as media_type_name', 'reseller_media_type_size.name as reseller_media_type_size_name', 'reseller_profile.reseller_ID as rsi'))
                ->leftJoin('reseller_user', 'reseller_pricing.reseller_ID', '=', 'reseller_user.id')
                ->leftJoin('reseller_media_type', 'reseller_pricing.media_type', '=', 'reseller_media_type.id')
                ->leftJoin('reseller_media_type_size', 'reseller_pricing.media_size', '=', 'reseller_media_type_size.id')
                ->leftJoin('reseller_profile', 'reseller_pricing.reseller_ID', '=', 'reseller_profile.user_id');

            $data->where('reseller_pricing.reseller_pricing_id', '=', $id);
            return $data->first();

        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }

    }

    /**
     * Add New User by Admin
     * @data Request Data
     * @return json
     */
    public static function addUserProfile($data)
    {
        try {
            $id = User::create([
                'user_name'     => $data['user_name'],
                'user_email'    => $data['user_email'],
                'password'      => bcrypt($data['password']),
                'user_type'     => '1',
                'create_date'   => date('Y-m-d H:i:s'),
                'create_by'     => '1',
                'record_status' => '1',
                '_token'        => '1',
            ])->id;

            ////// Add data in profile table also, so when new user created, His profile is also ready \\\\\

            ResellerProfile::insert([
                'user_id'                           => $id,
                'user_Email'                        => $data['user_email'],
                'reseller_name'                     => $data['user_name'],
                'country_code'                      => 'US',
                'currency_code'                     => 'USD',
                'contract_start'                    => date('Y-m-d'),
                'contract_end'                      => date('Y-m-d'),
                'create_date'                       => date('Y-m-d H:i:s'),
                'language_primary'                  => 'en',
                'languag_secondary'                 => 'en',
                'reseller_type'                     => 1,
                'contract_currency_conversion_rate' => '1',
                'commission_rate'                   => '1',
                'commission_rate_Low'               => '1',
                'low_rate_trigger'                  => '1',
                'seat_license_fee'                  => '1',
                'reseller_status'                   => '1',
                'create_by'                         => 'admin',
                'reduced_commission_rate'           => '1',
                'reduced_commission_rate'           => 1,
            ]);
            ////// Add data in profile table also, so when new user created, His profile is also ready End \\\\\
            $result['result']   = 'success';
            $result['message']  = 'User Added Successfully.';
            $result['redirect'] = 'userlist';
        } catch (\Exception $e) {

            $result['result']   = 'error';
            $result['message']  = $e->getMessage();
            $result['redirect'] = 'useradd';
        }
        return $result;
    }

    /**
     * updateApiCredentials For Admin
     * @param  request $request [description]
     * @return array
     */
    public static function updateApiCredentials($request)
    {
        try {

            $all    = $request->all();
            $update = ApiCredentials::where('id', 1)
                ->update([
                    'secret' => $all['apisecrets'],
                    'key'    => $all['apikey'],
                ]);
            $result['result']   = 'success';
            $result['message']  = 'Credentials Updated Successfully';
            $result['redirect'] = 'apicredentials';
        } catch (\Exception $e) {

            $result['result']   = 'error';
            $result['message']  = $e->getMessage();
            $result['redirect'] = 'useradd';
        }
        return $result;
    }

    /**
     * updatePond5ApiCredentials for Reseller LIVE POND account
     * @param  object $request [description]
     * @return array          [description]
     */
    public static function updatePond5ApiCredentials($request)
    {
        try {
            
            $all    = $request->all();
            $update = User::where('id', Auth::User()->id)
                ->update([
                    'pond_api'      => $all['pond_api'],
                    'pond_secret'   => $all['pond_secret'],
                    'pond_username' => $all['pond_username'],
                    'pond_password' => $all['pond_password'],
                ]);
            $result['result']   = 'success';
            $result['message']  = 'Credentials Updated Successfully';
            $result['redirect'] = 'pondcredentials';
        } catch (\Exception $e) {

            $result['result']   = 'error';
            $result['message']  = $e->getMessage();
            $result['redirect'] = 'pondcredentials';
        }
        return $result;
    }

}
