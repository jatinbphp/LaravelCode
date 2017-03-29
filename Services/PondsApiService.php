<?php

namespace App\Services;

use App\Models\ResellerContentRestrictions;
use App\Models\ResellerSalesReport;
use App\Models\ResellerSalesReportItems;
use Config;
use DB;
use Illuminate\Http\Request;

class PondsApiService
{

    public static $apiKey;

    public static $apiSecret;

    public static $url;

    public static $username;

    public static $password;

    /**
     * set static varialbe for credentials
     * it must be call in each method of this class
     *
     */

    public static function getData()
    {
        PondsApiService::$apiKey    = Config::get('pond5.apiKey');
        PondsApiService::$apiSecret = Config::get('pond5.apiSecret');
        PondsApiService::$url       = Config::get('pond5.url');
        PondsApiService::$username  = Config::get('pond5.userName');
        PondsApiService::$password  = Config::get('pond5.passWord');
    }

    /**
     * Login Api for Pond5
     * @return array with cm and cx value which we can use in
     *  other commands
     */
    public static function loginApi()
    {
        self::getData(); // Must be call in each method which required login
        $login_cmd             = array();
        $login_cmd['command']  = 'login';
        $login_cmd['username'] = self::$username; //REPLACE WITH A POND5 USERNAME
        $login_cmd['password'] = self::$password; //REPLACE WITH THE PASSWORD
        $result                = self::curlCall($login_cmd);
        $finalOutput['cx']     = $result->commands['0']->cx;
        $finalOutput['cm']     = $result->commands['0']->cm;
        return $finalOutput;
    }

    /**
     * Login API for POND5 Client URL
     * @return  array with response
     */
    public static function downloadAPI()
    {
        self::getData(); // Must be call in each method
        $loginDetails            = self::loginApi();
        $download_cmd            = array();
        $download_cmd['command'] = 'download';
        $download_cmd['bid']     = '11498920';
        $download_cmd['v']       = '1';
        $download_cmd['tr']      = '7458952214587';

        $result = self::curlCall($download_cmd, $loginDetails);
        return $result;
    }

    /**
     * Fetch User information
     * @return json 
     */
    public static function userInfo()
    {

        $loginDetails         = self::loginApi();
        $login_cmd            = array();
        $login_cmd['command'] = 'userinfo';
        $result               = self::curlCall($login_cmd, $loginDetails); // if login required pass LoginDetails array
        return $result;
    }

    /**
     * Call curl function
     * @param  [array] $login_cmd     [description]
     * @param  [array] $loginDetails true/false
     * @return [array]                [description]
     */
    public static function curlCall($login_cmd, $loginDetails = null)
    {
        $json_object                  = array();
        $json_object["api_key"]       = self::$apiKey;
        $json_object["ver"]           = 1;
        $json_object["commands_json"] = json_encode(array($login_cmd));

        //NOTE: the commands_hash must always have the string 'dragspel' appended
        $json_object["commands_hash"] = md5($json_object["commands_json"] . self::$apiSecret . 'dragspel');
        $data_req                     = json_encode($json_object);

        //the post argument
        $post_val = "api=" . urlencode($data_req);

        if ($loginDetails != null) {
            $post_val .= '&apicx=' . $loginDetails['cx'];
            $post_val .= '&apicm=' . $loginDetails['cm'];
        }

        //perform the url request
        $ch       = curl_init();
        $username = 'pond5';
        $password = 'dumbo';
        curl_setopt($ch, CURLOPT_URL, self::$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_val);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        $mydata = curl_exec($ch);

        if (curl_errno($ch)) {
            echo "curl error [" . curl_error($ch) . "]\n";
            exit;
        } else {
            curl_close($ch);
        }

        return json_decode($mydata);
    }

    /**
     * validate date
     * @param date $date date which need to validate
     * @return true/false
     */
    public static function validateDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * insert entry into restriction content table
     * @param  array $all request data
     * @return json
     */
    public static function insertRestrictionContentAPI($all)
    {

        try {

            // Mandatory data must be there.
            if (!isset($all['RID'])) {

                return response()->json([
                    'SC'      => 3,
                    'message' => 'Mandatory data RID not passed',
                ]);
            }
            // Mandatory data must be there.
            if (!isset($all['CID'])) {

                return response()->json([
                    'SC'      => 3,
                    'message' => 'Mandatory data CID not passed',
                ]);
            }
            // Mandatory data must be there.
            if (!isset($all['SD'])) {

                return response()->json([
                    'SC'      => 3,
                    'message' => 'Mandatory data SD not passed',
                ]);
            }
            // Mandatory data must be there.
            if (!isset($all['GD'])) {

                return response()->json([
                    'SC'      => 3,
                    'message' => 'Mandatory data GD not passed',
                ]);
            }
            $RID = $all['RID'];
            $CID = $all['CID'];
            $SD  = $all['SD'];
            $GD  = $all['GD'];
            $ED  = $all['ED'];
            $AI  = $all['AI'];

            // Mandatory data must be there.
            if (empty($RID) || empty($CID) || empty($SD) || empty($GD)) {

                return response()->json([
                    'SC'      => 3,
                    'message' => 'Mandatory data not passed',
                ]);
            }

            // RID must be greater than 0
            if ($RID <= 0) {

                return response()->json([
                    'SC'      => 1,
                    'message' => 'RID mut be greater than 0',
                ]);
            }

            // CID must be greater than 0
            if ($CID <= 0) {

                return response()->json([
                    'SC'      => 2,
                    'message' => 'CID mut be greater than 0',
                ]);
            }
            // SD Must be in Y-m-d Format
            if (!PondsApiService::validateDate($SD)) {
                return response()->json([
                    'SC'      => 2,
                    'message' => 'SD must be in Y-m-d i.e ' . \date('Y-m-d') . ' format',
                ]);
            }

            // GD Must be in Y-m-d Format
            if (!PondsApiService::validateDate($GD)) {
                return response()->json([
                    'SC'      => 2,
                    'message' => 'GD must be in Y-m-d i.e ' . \date('Y-m-d') . ' format',
                ]);
            }

            // ED Must be in Y-m-d Format if it is passed
            if (!empty($ED)) {
                if (!PondsApiService::validateDate($ED)) {
                    return response()->json([
                        'error'   => true,
                        'SC'      => 2,
                        'message' => 'ED must be in Y-m-d i.e ' . \date('Y-m-d') . ' format',
                    ]);
                }
            }
            // insert data into table
            $ResellerContentRestrictions->reseller_ID = $RID;
            $ResellerContentRestrictions->content_ID  = $CID;

            if (!empty($AI) && $AI > 0) {
                $ResellerContentRestrictions->artist_ID = $AI;
            }

            $ResellerContentRestrictions->create_date       = date('Y-m-d');
            $ResellerContentRestrictions->start_date        = $SD;
            $ResellerContentRestrictions->grace_period_date = $GD;
            if (!empty($ED)) {
                $ResellerContentRestrictions->end_date = $ED;
            }
            $ResellerContentRestrictions->save();
            // return success response
            return response()->json([
                'SC'          => 0,
                'Restriction' => [
                    'ResID'   => $ResellerContentRestrictions->res_ID,
                    'CID'     => $CID,
                    'SC'      => 0,
                    'Message' => 'Success',
                ],
            ], 200);

        } catch (\Exception $e) {
            // return error response
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update entry into restriction content table
     * @param  array $all request data
     * @return json
     */
    public static function updateRestrictionContentAPI($all)
    {
        try {

            if (!isset($all['CID']) && !isset($all['AI'])) {
                return response()->json([
                    'SC'      => 2,
                    'message' => 'CID or AI must be passed in argument',
                ]);
            }

            $RID = $all['RID'];
            $SD  = $all['SD'];
            $GD  = $all['GD'];
            $ED  = $all['ED'];

            // CID must be greater than 0
            if (isset($all['CID'])) {

                if (!is_numeric($all['CID'])) {
                    return response()->json([
                        'SC'      => 2,
                        'message' => 'CID must Integer',
                    ]);
                }
            }

            // CID must be greater than 0
            if (isset($all['CID'])) {
                if ($all['CID'] < 0) {
                    return response()->json([
                        'SC'      => 2,
                        'message' => 'CID mut be greater than 0',
                    ]);
                }
            }

            // ED Must be in Y-m-d Format if it is passed
            if (!PondsApiService::validateDate($ED)) {
                return response()->json([
                    'error'   => true,
                    'SC'      => 2,
                    'message' => 'ED must be in Y-m-d i.e ' . \date('Y-m-d') . ' format',
                ]);
            }

            // if CID is set and pass then, data will updated as per CID
            if (isset($all['CID'])) {
                $update = ResellerContentRestrictions::where('content_ID', $all['CID'])->update(['end_date' => $ED]);
                $CID    = $all['CID'];
            } else {
                $CID = 0;
            }

            // if AI is set and pass then, data will updated as per AI
            if (isset($all['AI'])) {
                $update = ResellerContentRestrictions::where('artist_ID', $all['AI'])->update(['end_date' => $ED]);
            }

            // return success response
            return response()->json([
                'SC'          => 0,
                'Restriction' => [
                    'RecCnt'  => $update,
                    'CID'     => $CID,
                    'SC'      => 0,
                    'Message' => 'Success',
                ],
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete entry into restriction content table
     * @param  array $all request data
     * @return json
     */
    public static function deleteRestrictionContentAPI($all)
    {
        try {

            if (!isset($all['CID']) && !isset($all['AI'])) {
                return response()->json([
                    'SC'      => 2,
                    'message' => 'CID or AI must be passed in argument',
                ]);
            }

            $RID = $all['RID'];
            $SD  = $all['SD'];
            $GD  = $all['GD'];
            $ED  = $all['ED'];

            // CID must be greater than 0
            if (isset($all['CID'])) {

                if (!is_numeric($all['CID'])) {
                    return response()->json([
                        'SC'      => 2,
                        'message' => 'CID must Integer',
                    ]);
                }
            }

            // CID must be greater than 0
            if (isset($all['CID'])) {
                if ($all['CID'] < 0) {
                    return response()->json([
                        'SC'      => 2,
                        'message' => 'CID mut be greater than 0',
                    ]);
                }
            }

            // ED Must be in Y-m-d Format if it is passed
            if (!PondsApiService::validateDate($ED)) {
                return response()->json([
                    'error'   => true,
                    'SC'      => 2,
                    'message' => 'ED must be in Y-m-d i.e ' . \date('Y-m-d') . ' format',
                ]);
            }

            // if CID is set and pass then, data will updated as per CID
            if (isset($all['CID'])) {
                $delete = ResellerContentRestrictions::where('content_ID', $all['CID'])->update(['end_date' => $ED]);
                $CID    = $all['CID'];
            } else {
                $CID = 0;
            }

            // if AI is set and pass then, data will updated as per AI
            if (isset($all['AI'])) {
                $delete = ResellerContentRestrictions::where('artist_ID', $all['AI'])->update(['end_date' => $ED]);
            }

            // return success response
            return response()->json([
                'SC'          => 0,
                'Restriction' => [
                    'RecCnt'  => $delete,
                    'CID'     => $CID,
                    'SC'      => 0,
                    'Message' => 'Success',
                ],
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * sales service
     * @param array $all
     * @return json
     *
     */
    public static function sales($all)
    {

        try {

            if (!isset($all['CID']) || !isset($all['UN']) || !isset($all['TID'])) {
                return response()->json([
                    'SC'      => 2,
                    'message' => 'All arguments not passed',
                ]);
            }

            // CID must be greater than 0

            if (!is_numeric($all['CID'])) {
                return response()->json([
                    'SC'      => 2,
                    'message' => 'CID must Integer',
                ]);
            }

            // CID must be greater than 0

            if (empty($all['UN'])) {
                return response()->json([
                    'SC'      => 2,
                    'message' => 'UN is mandatory to pass',
                ]);
            }

            // TID must be greater than 0

            if (!is_numeric($all['TID'])) {
                return response()->json([
                    'SC'      => 2,
                    'message' => 'TID must Integer',
                ]);
            }

            $fetchResellerIdFromUN = DB::table('reseller_user')
                ->select(['reseller_user.id', 'reseller_profile.reseller_ID'])
                ->leftJoin('reseller_profile', 'reseller_profile.user_id', '=', 'reseller_user.id')
                ->where('reseller_user.user_name', '=', $all['UN'])
                ->first();

            if (count($fetchResellerIdFromUN) == 0) {
                return response()->json([
                    'SC'      => 2,
                    'message' => 'UN does not match with our records',
                ]);
            }

            /***************Gathering data ****************/
            $resellerId    = 14; //$fetchResellerIdFromUN->reseller_ID;
            $saleDate      = date('Y-m-d');
            $contentId     = 1254;
            $mediaType     = 1;
            $mediaSize     = 3;
            $contentSize   = 1;
            $customPricing = 1;

            /*
             *Retrieve Price from ResellerPricing Table
             *using the ResellerID, Media Type and Media Size.
             */
            $resellerPrice = DB::table('reseller_pricing')
                ->where('reseller_ID', '=', $resellerId)
                ->where('media_type', '=', $mediaType)
                ->where('media_size', '=', $mediaSize)
                ->first();
            if (count($resellerPrice) == 0) {
                return response()->json([
                    'SC'      => 2,
                    'message' => 'ResellerPrice Data not found',
                ]);
            }

            /**** Search data from reseller sales report table *******/
            $currMonth           = 9;
            $currYear            = 2017;
            $resellerSalesReport = DB::table('reseller_sales_report')
                ->where('reseller_ID', '=', $resellerId)
                ->where('sales_month', '=', $currMonth)
                ->where('sales_year', '=', $currYear)
                ->first();

            /****** If record exist pass sales report id *****/
            if (count($resellerSalesReport) == 1) {
                return response()->json([
                    'SC'            => 0,
                    'message'       => 'Success',
                    'SalesReportID' => $resellerSalesReport->sales_report_ID,
                ]);
            } else {
                /****** If record not exist pass sales report data into table *****/
                //$lastAddedRecord                                    = ResellerSalesReport::orderBy('sales_report_ID', 'desc')->first();
                //$salesReport_ID                                     = $lastAddedRecord->sales_report_ID + 1;
                $resellerSalesReportTalbe = new ResellerSalesReport();
                //$resellerSalesReportTalbe->sales_report_ID          = $salesReport_ID;
                $resellerSalesReportTalbe->reseller_ID              = $resellerId;
                $resellerSalesReportTalbe->create_date              = date('Y-m-d');
                $resellerSalesReportTalbe->sales_month              = $currMonth;
                $resellerSalesReportTalbe->sales_year               = $currYear;
                $resellerSalesReportTalbe->currency_code            = 'USD';
                $resellerSalesReportTalbe->totalsales_local         = '0';
                $resellerSalesReportTalbe->reseller_local           = '0';
                $resellerSalesReportTalbe->pond5_local              = '0';
                $resellerSalesReportTalbe->totalsales_USD           = '0';
                $resellerSalesReportTalbe->reseller_USD             = '0';
                $resellerSalesReportTalbe->pond5_USD                = '0';
                $resellerSalesReportTalbe->open_for_edit            = '1';
                $resellerSalesReportTalbe->date_exported_accounting = date('Y-m-d');
                $resellerSalesReportTalbe->record_status            = '1';
                $resellerSalesReportTalbe->save();
                $resellerSalesId = $resellerSalesReportTalbe->sales_report_ID;

                /****** Add data into sales report item table *****/

                //  $lastAddedRecordItem                                = ResellerSalesReportItems::orderBy('sales_report_item_ID', 'desc')->first();
                //$salesReportItem_ID                                 = $lastAddedRecordItem->sales_report_item_ID + 1;
                $resellerSalesReportItems = new ResellerSalesReportItems();
                //$resellerSalesReportItems->sales_report_item_ID     = $salesReportItem_ID;
                $resellerSalesReportItems->sales_report_ID          = $resellerSalesId;
                $resellerSalesReportItems->create_date              = date('Y-m-d');
                $resellerSalesReportItems->sale_date                = date('Y-m-d');
                $resellerSalesReportItems->transaction_ID           = $all['TID'];
                $resellerSalesReportItems->content_ID               = $all['CID'];
                $resellerSalesReportItems->media_type               = $mediaType;
                $resellerSalesReportItems->content_size             = $contentSize;
                $resellerSalesReportItems->currency_conversion_rate = 0;
                $resellerSalesReportItems->price_standard_local     = 0;
                $resellerSalesReportItems->price_edited_local       = 0;
                $resellerSalesReportItems->price_edit_reason_ID     = 5;
                $resellerSalesReportItems->delete_item              = 1;
                $resellerSalesReportItems->delete_reason_ID         = 5;
                $resellerSalesReportItems->reduced_commission       = 1;
                $resellerSalesReportItems->commission_rate          = 0;
                $resellerSalesReportItems->updated_by               = $all['UN'];
                $resellerSalesReportItems->update_date              = date('Y-m-d');
                $resellerSalesReportItems->record_status            = 1;
                $resellerSalesReportItems->save();
                $resellerSalesReportItems->sales_report_item_ID;
                return response()->json([
                    'SC'  => 0,
                    'url' => 'http://pond5.mcbridedev.com/',
                    'bid' => $all['bid'],
                    'tr'  => $all['TID'],
                    'v'   => $all['CID'],
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public static function download($all)
    {
        try {

            if (!isset($all['bid']) || !isset($all['tr']) || !isset($all['v'])) {
                return response()->json([
                    'SC'      => 2,
                    'message' => 'All arguments not passed',
                ]);
            }

            // bid must be greater than 0
            if (!is_numeric($all['bid'])) {
                return response()->json([
                    'SC'      => 2,
                    'message' => 'bid must Integer',
                ]);
            }

            // tr must be greater than 0
            if (!is_numeric($all['tr'])) {
                return response()->json([
                    'SC'      => 2,
                    'message' => 'tr must be valid value',
                ]);
            }

            // tr must be greater than 0
            if (!is_numeric($all['v'])) {
                return response()->json([
                    'SC'      => 2,
                    'message' => 'v must be valid value',
                ]);
            }
            /** We using static reseller id at the moment later , we will get it from cm/cx */
            $resellerId = 14;
            $userName   = 'bmcbride2';

            /*** calling sales service which will generate data in ResellerSalesReportItems***/
            $data['CID'] = $all['v'];
            $data['UN']  = $userName;
            $data['TID'] = $all['tr'];
            $data['bid'] = $all['bid'];
            return self::sales($data);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
            ]);
        }

    }
}
