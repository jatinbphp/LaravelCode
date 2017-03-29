<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddUser;
use App\Http\Requests\ResellerProfile;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Requests\UserRequest;
use App\Models\Countries;
use App\Models\Currency;
use App\Models\Languages;
use App\Models\ApiCredentials;
use App\Models\ResellerProfile as RP;
use App\Models\ResellerStatus;
use App\Models\ResellerType;
use App\Models\ResellerUser;
use App\Services\AjaxService;
use App\Services\DashboardService;
use Auth;
use DB;
use Form;
use Illuminate\Http\Request;

class DashboardController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        //Auth::user();
        return view('pages.dashboard');
    }

    /**
     * Reseller Profiel view page.
     *
     * @return view blade file
     */
    public function resellerProfile()
    {
        /// Fetch data for various dropdown\\\\
        $resellerStatus = ResellerStatus::all();
        $reseller       = ResellerUser::all();
        $resellerType   = ResellerType::all();
        $CountriesCode  = Countries::all();
        $CurrencyCode   = Currency::all();
        $languages      = Languages::all();

        /// Assign fetched data into view variable \\\\\
        $data['resellerStatus'] = $resellerStatus;
        $data['reseller']       = $reseller;
        $data['resellerType']   = $resellerType;
        $data['CountriesCode']  = $CountriesCode;
        $data['CurrencyCode']   = $CurrencyCode;
        $data['languages']      = $languages;
        return view('pages.resellerProfile', $data);
    }

    /**
     * validate input and stored in table or throw errors
     *  @param object $request
     *  @return redirect to listing page
     */
    public function storereRellerProfile(ResellerProfile $request)
    {
        $postData = $request->all();
        $result   = DashboardService::saveResellerProfile($postData);
        return redirect($result['redirect'])->with($result['result'], $result['message']);
    }

    /**
     * View page of List for reseller profile
     *
     */
    public function resellerprofileList()
    {
        return view('pages.resellerprofilelist');
    }

    /**
     * fetch List for reseller profile
     *
     */
    public function resellerprofileListFetchAjax()
    {
        return AjaxService::resellerProfileList();
    }

    /**
     * Delete profile record
     *
     */
    public function resellerProfileDelete(Request $request)
    {
        try {

            $id = $request->only('id');

            DB::table('reseller_profile')
                ->where('reseller_ID', '=', $id)
                ->delete();

            return response()->json([
                'message' => 'Record Deleted Successfully.',
                'result'  => 'success',
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->geMessage(),
                'result'  => 'error',
            ]);

        }
    }

    /**
     * Edit Reseller Record
     * @param integer $id
     * @return view file
     */
    public function profileselleredit($id)
    {

        /// Fetch data for various dropdown\\\\
        $resellerStatus = ResellerStatus::pluck('reseller_status_description', 'reseller_status');
        $reseller       = ResellerUser::all();
        $resellerType   = ResellerType::pluck('reseller_type_description', 'reseller_type');
        $CountriesCode  = Countries::pluck('name', 'code');
        $CurrencyCode   = Currency::pluck('currency_code', 'currency_code');
        $languages      = Languages::all();

        $languag_secondary = Languages::pluck('name', 'code');

        /// Assign fetched data into view variable \\\\\
        $data['resellerStatus']    = $resellerStatus;
        $data['reseller']          = $reseller;
        $data['resellerType']      = $resellerType;
        $data['CountriesCode']     = $CountriesCode;
        $data['CurrencyCode']      = $CurrencyCode;
        $data['languages']         = $languages;
        $data['languag_secondary'] = $languag_secondary;
        $data['id']                = $id;
        $data['result']            = RP::find($id);
        return view('pages.resellerProfileView', $data);
    }

    /**
     * Edit Seller Profile
     * @param object Request $request
     * @param integer $id
     */
    public function resellerProfileUpdate(ResellerProfile $request, $id)
    {
        $postData = $request->all();
        $result   = DashboardService::updateResellerProfile($postData, $id);
        return redirect($result['redirect'])->with($result['result'], $result['message']);
    }
    /**
     * Create user
     * @return  view file fo create user
     */
    public function createUser()
    {
        return view('pages.createUser');
    }

    /**
     *
     * Display list of users to admin
     * @return  view of of user list to admin
     */
    public function userList()
    {
        return view('pages.userList');
    }

    /**
     * fetch List for reseller profile
     * @return json response for ajax
     */
    public function userListFetchAjax()
    {
        return AjaxService::userListFetchAjax();
    }

    /**
     * Change User status from admin to reseller and viseversa
     * @return json response
     */
    public function changeUseStatus(Request $request)
    {
        $all    = $request->all();
        $id     = $all['id'];
        $status = $all['status'];
        return DashboardService::changeStatus($id, $status);
    }

    /**
     * Profile update
     * @return view file
     */
    public function profile()
    {
        $data = Auth::user();
        return view('pages.profile')->with('data', $data);
    }

    /**
     * Update profile
     * @param Object $request
     * @return redirect to profile page
     */
    public function updateProfile(UpdateUserProfileRequest $request)
    {
        $data   = $request->all();
        $result = DashboardService::updateUserProfile($data);
        return redirect($result['redirect'])->with($result['result'], $result['message']);
    }

    /**
     * @param int $id
     * @return view
     *
     */
    public function userEdit(UserRequest $request)
    {
        return view('pages.userEdit')->with('data', $request->result());
    }

    /**
     * User Add
     * @return view file
     */
    public function userAdd()
    {
        return view('pages.userAdd');
    }

    /**
     * User Add
     * @return view file
     */
    public function addUserProfile(AddUser $request)
    {
        $data   = $request->all();
        $result = DashboardService::addUserProfile($data);
        return redirect($result['redirect'])->with($result['result'], $result['message']);

    }

    /**
     * Update profile by Admin
     * @param Object $request
     * @return redirect to userList page
     */
    public function updateUserProfile(UpdateUserProfileRequest $request, $id)
    {
        $data   = $request->all();
        $result = DashboardService::updateUserProfile($data, $id);
        return redirect($result['redirect'])->with($result['result'], $result['message']);
    }

    /**
     * store api credentials
     * @return view file
     */
    public function apiCredentials(Request $request)
    {
        if ($request->isMethod('post')) {     
            $this->validate($request, [
                'apisecrets' => 'required',
                'apikey'     => 'required',
            ]);               
            $result = DashboardService::updateApiCredentials($request);
            return redirect($result['redirect'])->with($result['result'], $result['message']);            
        }

        $data['credentials'] = ApiCredentials::find(1);
        return view('pages.apiCredentials',$data);
    }
}
