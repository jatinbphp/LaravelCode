<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResellerPrice as RP;
use App\Http\Requests\ResellerProfile;
use App\Models\Countries;
use App\Models\Currency;
use App\Models\Languages;
use App\Models\MediaSize;
use App\Models\MediaType;
use App\Models\ResellerPricing;
use App\Models\ResellerSalesReport;
use App\Models\ResellerSalesReportItems;
use App\Models\ResellerStatus;
use App\Models\ResellerType;
use App\Models\ResellerUser;
use App\Models\SalesReportActions;
use App\Services\AjaxService;
use App\Services\DashboardService;
use App\User;
use DB;
use Illuminate\Http\Request;

class ResellerController extends Controller
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
     * list Reseler profile list
     * @return View file page
     */
    public function profileList()
    {
        return view('reseller.profileList');
    }

    /**
     * List of reseller Sales Report
     * @return view file
     */

    public function resellerSalesReport($id = '')
    {

        if ($id != '') {
            $data['id'] = DashboardService::fetchDataFromTable($id,
                'reseller_profile',
                'user_id', 'reseller_ID');
        } else {
            $data['id'] = '';
        }
        $data['resellerList'] = User::where('user_type', '=', 1)->get();
        return view('reseller.resellerSalesReport', $data);
    }

    /**
     * Fetch sales report for Reseller
     * @return json result
     */

    public function resellerSalesReportFetchAjax(Request $request)
    {
        $all = $request->all();
        return AjaxService::fetchResellerSalesCallReportAjax($all);
    }

    /**
     * View form and generate select boxes
     * @return view file     *
     */
    public function myProfile()
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
        $alreadyProfileAdded    = DB::table('reseller_profile')
            ->where('user_id', '=', $this->userId)
            ->first();
        if (count($alreadyProfileAdded) == 0) {
            $alreadyProfileAdded                                    = new \stdClass();
            $alreadyProfileAdded->user_Email                        = '';
            $alreadyProfileAdded->reseller_name                     = '';
            $alreadyProfileAdded->country_code                      = 'US';
            $alreadyProfileAdded->currency_code                     = 'USD';
            $alreadyProfileAdded->language_primary                  = 'en';
            $alreadyProfileAdded->languag_secondary                 = 'en';
            $alreadyProfileAdded->contract_start                    = '';
            $alreadyProfileAdded->contract_end                      = '';
            $alreadyProfileAdded->reseller_type                     = '';
            $alreadyProfileAdded->contract_currency_conversion_rate = '';
            $alreadyProfileAdded->commission_rate                   = '';
            $alreadyProfileAdded->commission_rate_Low               = '';
            $alreadyProfileAdded->low_rate_trigger                  = '';
            $alreadyProfileAdded->seat_license_fee                  = '';
            $alreadyProfileAdded->reseller_status                   = '';
            $alreadyProfileAdded->create_date                       = '';
            $alreadyProfileAdded->create_by                         = '';
            $alreadyProfileAdded->reduced_commission_rate           = '';

        }

        $data['profile'] = $alreadyProfileAdded;

        return view('reseller.myprofile', $data);

    }

    /**
     * Update records
     *  @param  object $request
     *  @return  redirect to my profile page
     */
    public function updateMyProfile(ResellerProfile $request)
    {

        $postData = $request->all();
        $result   = DashboardService::saveorUpdateResellerProfile($postData);
        return redirect($result['redirect'])->with($result['result'], $result['message']);
    }

    /**
     * To get data of sales call report and display as view
     * @param  int $id
     * @return view file
     */
    public function viewSalesCallReport($id)
    {
        $data['salesReport'] = ResellerSalesReport::find($id);

        if ($this->role == 1) {
            $data['resellerId']       = $this->userId;
            $data['resellerUserName'] = $this->userName;
            $data['readonly']         = 'readonly';
        } else {
            $data['resellerId']       = $data['salesReport']->reseller_ID;
            $data['resellerUserName'] = DashboardService::fetchDataFromTable($data['salesReport']->reseller_ID,
                'reseller_user',
                'user_name', 'id');

            $data['readonly'] = '';
            if ($data['salesReport']->open_for_edit == 1) {
                $data['readonly'] = 'readonly';
            }
        }

        $data['id']           = $id;
        $data['CurrencyCode'] = Currency::all();
        $data['deletReason']  = SalesReportActions::where('action_type', '=', '1')->get();
        $data['adjustReason'] = SalesReportActions::where('action_type', '=', '2')->get();

        return view('reseller.viewSalesReport', $data);
    }

    /**
     * List of reseller Sales Report Item
     * @return view file
     */

    public function resellerSalesReportItem($id = null)
    {
        $data['id'] = $id;
        return view('reseller.resellerSalesReportItem', $data);
    }
    /**
     * Fetch sales report for Reseller
     * @return json result
     */

    public function resellerSalesReportItemFetchAjax(Request $request)
    {
        return AjaxService::fetchResellerSalesCallItemReportAjax($request);
    }

    /**
     * Fetch Reseller Pricing
     * @return json result
     */
    public function resellerPricingFetchAjax(Request $request)
    {
        $all = $request->all();
        return AjaxService::fetchResellerPricingFetchAjax($all);
    }

    /**
     * To get data of sales call item report and display as view
     * @param  int $id
     * @return view file
     */
    public function viewSalesCallReportItem($id)
    {
        $data['salesReportItem'] = ResellerSalesReportItems::find($id);

        return view('reseller.viewSalesReportItem', $data);
    }

    /**
     * List of reseller Sales Pricing
     * @return view file
     */
    public function resellerPricing($id = '')
    {
        if ($id != '') {
            $data['id'] = DashboardService::fetchDataFromTable($id,
                'reseller_profile',
                'user_id', 'reseller_ID');
        } else {
            $data['id'] = '';
        }
        $data['resellerProfileId'] = $id;
        $data['resellerList']      = DB::table('reseller_user')
            ->select(array('reseller_user.*', 'reseller_profile.reseller_ID as ri'))
            ->leftJoin('reseller_profile', 'reseller_user.id', '=', 'reseller_profile.user_id')
            ->where('reseller_user.user_type', '=', 1)
            ->get();
        return view('reseller.resellerPricing', $data);
    }

    /**
     * To get data of reseller Pricing report and display as view
     * @param  int $id
     * @return view file
     */
    public function viewSellerPrice($id)
    {
        $data['resellerPricing'] = DashboardService::fetchResellerPricingById($id);

        return view('reseller.viewSellerPrice', $data);
    }

    /**
     * List of reseller content Restriction
     * @return view file
     */
    public function resellerContentRestriction()
    {
        $data['media_type'] = MediaType::all();
        return view('reseller.resellerContentRestriction', $data);
    }

    /**
     * Fetch Reseller Content Restrictions
     * @return json result
     */

    public function resellerContentRestrictionsFetchAjax(Request $request)
    {
        return AjaxService::fetchResellerContentRestrictionsFetchAjax($request);
    }

    /**
     * Update sales report
     * @param object $request
     * @return json array
     */
    public function updateSalesReportAjax(Request $request)
    {
        $data   = $request->all();
        $result = AjaxService::updateSalesReportAjax($data);
        return response()->json([
            'result'  => $result['result'],
            'message' => $result['message'],
        ]);
    }

    /**
     * Update open for edit status
     * @param objec $request
     *  @return json
     */
    public function changeOpenStatusAjax(Request $request)
    {
        $data   = $request->all();
        $result = AjaxService::updateSalesReportOpenForEditAjax($data);
        return response()->json([
            'result'  => $result['result'],
            'message' => $result['message'],
        ]);
    }
    /**
     * fetch Reseller Sales Item data
     * @param objec $request
     * @return json
     */
    public function fetchSaleReportItemDataAjax(Request $request)
    {
        $data            = $request->all();
        $id              = $data['id'];
        $salesReportItem = ResellerSalesReportItems::find($id);
        return response()->json(['result' => $salesReportItem]);
    }

    /**
     * Update sales report Item
     * @param object $request
     * @return json array
     */
    public function updateSalesReportItemAjax(Request $request)
    {
        $data   = $request->all();
        $result = AjaxService::updateSalesReportItemAjax($data);
        return response()->json([
            'result'  => $result['result'],
            'message' => $result['message'],
        ]);
    }

    /**
     * Expoert Sales report data into CVS
     * @param integer $id
     * @return csv data
     *
     */

    public function resellerSalesCsv($id = '')
    {
        DashboardService::expoertResellerSaleCsv($id);
    }

    /**
     * Expoert Sales report data into CVS For all employee
     * @param integer $id
     * @return csv data
     *
     */

    public function resellerSalesCsvAll($id = '')
    {
        DashboardService::resellerSalesCsvAll($id);
    }

    /**
     * add reseller Price form
     * @param integer $id
     * @return view file
     */

    public function resellerPriceAdd($id = '')
    {
        if (empty($id)) {
            return redirect('resellerpricing')->with('error', 'Please select Reseller First')->withInput();
        }
        $data['media_type'] = MediaType::all();
        $data['media_size'] = MediaSize::all();
        // We have to find reseller id from reseller profile id
        $data['id'] = DashboardService::fetchDataFromTable($id,
            'reseller_profile',
            'user_id', 'reseller_ID');
        $data['resellerProfileId'] = $id;
        return view('reseller.resellerAddPrice', $data);
    }

    /**
     * Store reseller price data into database table
     * @param array $request
     * @return will redirect to page.
     */

    public function storeResellerPrice(RP $request)
    {
        $data   = $request->all();
        $result = DashboardService::storeResellerPrice($data);
        return redirect($result['redirect'])->with($result['result'], $result['message'])->withInput();
    }

    /**
     * filled edit reseller price data form
     * @param  int $id
     * @return view file
     */
    public function editSellerPrice($id, $pid)
    {
        $data['data']       = ResellerPricing::find($id);
        $data['media_type'] = MediaType::all();
        $data['media_size'] = MediaSize::all();
        $data['pid']        = $pid;
        return view('reseller.resellerEditPrice', $data);
    }

    /**
     * Edit Reseller core function
     * @param  RP     $request
     * @param  int $id
     * @param  int $pid
     * @return redirection to page
     */
    public function updateResellerPrice(RP $request, $id, $pid)
    {
        $data   = $request->all();
        $result = DashboardService::UpdateResellerPrice($data, $id, $pid);
        return redirect($result['redirect'])->with($result['result'], $result['message'])->withInput();

    }

    /**
     * Add Credential of POND5 for each reseller
     * @param array $request
     *
     */
    public function pondCredentials(Request $request)
    {
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'pond_secret'   => 'required',
                'pond_api'      => 'required',
                'pond_username' => 'required',
                'pond_password' => 'required',
            ]);
            $result = DashboardService::updatePond5ApiCredentials($request);
            return redirect($result['redirect'])->with($result['result'], $result['message']);
        }

        $data['credentials'] = User::find($this->userId);
        return view('pages.pondCredentials', $data);
    }

}
