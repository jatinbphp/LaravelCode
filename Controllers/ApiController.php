<?php

namespace App\Http\Controllers;

use App\Services\PondsApiService;
use DB;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * Call parent construct to perform common action
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Login API calling
     * @return array of cm and cx
     */
    public function login()
    {        
        $resultLogin = PondsApiService::loginApi();
        echo "<pre>";
        print_r($resultLogin);exit;
    }


    /**
     * Download API calling of LIVE SERVER
     * @return array of cm and cx
     */
    public function downloadAPI()
    {        
        $resultdownload = PondsApiService::downloadAPI();
        echo "<pre>";
        print_r($resultdownload);exit;
    }

    /**
     * get User Info of current Login user
     * @return array user info
     */
    public function userInfo()
    {
        $userInfo = PondsApiService::userInfo();
        echo "<pre>";
        print_r($userInfo);exit;
    }

    /**
     * get get active bin details
     * @return array user info
     */
    public function getActiveBin()
    {
        $userInfo = PondsApiService::getActiveBin();
        echo "<pre>";
        print_r($userInfo);exit;
    }

    /**
     * The Content Restriction Service will be
     * the method in which the ContentRestrictions
     * table is populated.
     * @param [object] $request post data
     * @return array response
     */
    public function restriction(Request $request)
    {

        $all = $request->all();
        /********** API Key and SECRET Key Validation ************************/
        if (!isset($all['secret'])) {

            return response()->json([
                'error'   => true,
                'message' => 'API Secret is missing',
            ]);
        }

        if (!isset($all['key'])) {

            return response()->json([
                'error'   => true,
                'message' => 'API Key is missing',
            ]);
        }
        $credentials = DB::table('api_credentials')
            ->where('secret', '=', $all['secret'])
            ->where('key', '=', $all['key'])
            ->get()->first();
        if (count($credentials) == 0) {
            return response()->json([
                'error'   => true,
                'message' => 'API or Secret Key not matched',
            ]);
        }

        /********** API Key and SECRET Key Validation End ************************/
        if ($request->isMethod('post')) {
            return PondsApiService::insertRestrictionContentAPI($all);
        }

        // For Update
        if ($request->isMethod('put')) {
            return PondsApiService::updateRestrictionContentAPI($all);
        }

        // For Delete
        if ($request->isMethod('delete')) {
            return PondsApiService::deleteRestrictionContentAPI($all);
        }

    }

    /**
     * The Reseller Sales Recognition Service
     * will record a Sales Report Items
     * row when a Reseller Requests a high-resolution
     * download from the Pond5 API Download Command.
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function sales(Request $request)
    {
        $all = $request->all();
        /********** API Key and SECRET Key Validation ************************/
        if (!isset($all['secret'])) {

            return response()->json([
                'error'   => true,
                'message' => 'API Secret is missing',
            ]);
        }

        if (!isset($all['key'])) {

            return response()->json([
                'error'   => true,
                'message' => 'API Key is missing',
            ]);
        }
        $credentials = DB::table('api_credentials')
            ->where('secret', '=', $all['secret'])
            ->where('key', '=', $all['key'])
            ->get()->first();
        if (count($credentials) == 0) {
            return response()->json([
                'error'   => true,
                'message' => 'API or Secret Key not matched',
            ]);
        }

        /********** API Key and SECRET Key Validation End ************************/

        if ($request->isMethod('post')) {
            return PondsApiService::sales($all);
        }
    }

    /**
     * Download API 4.2.5.5. Login Required
     * @param  Request $request [description]
     * @return json
     */
    public function download(Request $request)
    {
        $all = $request->all();

        /********** API Key and SECRET Key Validation ************************/
        if (!isset($all['secret'])) {

            return response()->json([
                'error'   => true,
                'message' => 'API Secret is missing',
            ]);
        }

        if (!isset($all['key'])) {

            return response()->json([
                'error'   => true,
                'message' => 'API Key is missing',
            ]);
        }

        $credentials = DB::table('api_credentials')
            ->where('secret', '=', $all['secret'])
            ->where('key', '=', $all['key'])
            ->get()->first();

        if (count($credentials) == 0) {
            return response()->json([
                'error'   => true,
                'message' => 'API or Secret Key not matched',
            ]);
        }
        /********** API Key and SECRET Key Validation End ************************/
        if ($request->isMethod('post')) {
            return PondsApiService::download($all);
        }
    }
}
