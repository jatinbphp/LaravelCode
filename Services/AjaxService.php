<?php

namespace App\Services;

use App\Models\ResellerSalesReport;
use App\Models\ResellerSalesReportItems;
use Auth;
use DB;
use Yajra\Datatables\Facades\Datatables;

class AjaxService
{

    /**
     * Service for fetch data from reseller_profile table to dispaly for admin and to reseller
     * @param array $data
     * @return true false based on result
     */

    public static function resellerProfileList()
    {
        try {

            $data = DB::table('reseller_profile')
                ->select(array('reseller_profile.*', 'countries.name', 'lp.name as primaryLanguage', 'ls.name as secondaryLanguage', 'reseller_status.reseller_status_description', 'reseller_type.reseller_type_description'))
                ->Leftjoin('countries', 'reseller_profile.country_code', '=', 'countries.code')
                ->Leftjoin('languages as lp', 'reseller_profile.language_primary', '=', 'lp.code')
                ->Leftjoin('languages as ls', 'reseller_profile.languag_secondary', '=', 'ls.code')
                ->Leftjoin('reseller_type', 'reseller_profile.reseller_type', '=', 'reseller_type.reseller_type')

                ->Leftjoin('reseller_status', 'reseller_profile.reseller_status', '=', 'reseller_status.reseller_status');
            return Datatables::of($data)->make(true);

        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }

    }

    /**
     * Service for fetch data from reseller_user table to dispaly for admin
     * @param array $data
     * @return true false based on result
     */

    public static function userListFetchAjax()
    {
        try {
            $data = DB::table('reseller_user')
                ->select(array('reseller_user.*', 'reseller_profile.reseller_ID'))
                ->leftJoin('reseller_profile', 'reseller_user.id', '=', 'reseller_profile.user_id')
                ->where('id', '!=', Auth::user()->id);
            return Datatables::of($data)->make(true);

        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    /**
     * Service for fetch data Reseller Sales Report
     * @param array $data
     * @return true false based on result
     */

    public static function fetchResellerSalesCallReportAjax($all)
    {
        try {
            $data =  DB::table('reseller_sales_report')
            ->select(array('reseller_sales_report.*', 'reseller_user.user_name'))
            ->leftJoin('reseller_user', 'reseller_sales_report.reseller_ID', '=', 'reseller_user.id');
             if ($all['id'] != '') {
                $data->where('reseller_sales_report.reseller_ID', '=', $all['id']);
            }
             if (Auth::User()->user_type == 1) {
                $data->where('reseller_sales_report.reseller_ID', '=', Auth::User()->id);
            }
            //$data->orderBy('sales_report_ID', 'desc');
            return Datatables::of($data)->make(true);

        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }

    }

    /**
     * Service for fetch data Reseller Sales Report Item
     * @param array $data
     * @return true false based on result
     */

    public static function fetchResellerSalesCallItemReportAjax($request)
    {
        try {
            $data = DB::table('reseller_sales_report_items')
                ->select('reseller_sales_report_items.*', 'sales_report_actions.sales_report_action_description as deleteReason', 'sra.sales_report_action_description as editReason')
                ->leftJoin('reseller_sales_report', 'reseller_sales_report_items.sales_report_ID', '=', 'reseller_sales_report.sales_report_ID')
                ->leftJoin('sales_report_actions', 'reseller_sales_report_items.delete_reason_ID', '=', 'sales_report_actions.sales_report_action_ID')
                ->leftJoin('sales_report_actions as sra', 'reseller_sales_report_items.price_edit_reason_ID', '=', 'sra.sales_report_action_ID');

            if ($request->has('itemId') && $request->get('itemId') != '') {
                $data->where('reseller_sales_report.sales_report_ID', '=', $request->get('itemId'));
            }

            if (Auth::User()->user_type == 1) {
                $data->where('reseller_sales_report.reseller_ID', '=', Auth::User()->id);
            }
            $data->orderBy('sales_report_item_ID', 'desc');

            return Datatables::of($data)->make(true);

        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }

    }

    /**
     * Service for fetch data Reseller Pricing
     * @param array $all
     * @return true false based on result
     */
    public static function fetchResellerPricingFetchAjax($all)
    {

        try {
            $data = DB::table('reseller_pricing')
                ->select(array('reseller_pricing.*', 'reseller_user.user_name', 'reseller_media_type.name as media_type_name','reseller_media_type_size.name as reseller_media_type_size_name','reseller_profile.reseller_ID as rsi'))
                ->leftJoin('reseller_user', 'reseller_pricing.reseller_ID', '=', 'reseller_user.id')
                ->leftJoin('reseller_media_type', 'reseller_pricing.media_type', '=', 'reseller_media_type.id')
                ->leftJoin('reseller_media_type_size', 'reseller_pricing.media_size', '=', 'reseller_media_type_size.id')
                ->leftJoin('reseller_profile', 'reseller_pricing.reseller_ID', '=', 'reseller_profile.user_id');

            if ($all['id'] != '') {
                $data->where('reseller_pricing.reseller_ID', '=', $all['id']);
            }
             if (Auth::User()->user_type == 1) {
                $data->where('reseller_pricing.reseller_ID', '=', Auth::User()->id);
            }
            //$data->orderBy('reseller_pricing_id', 'desc');

            return Datatables::of($data)->make(true);

        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }

    }

    /**
     * Service for fetch data Reseller Pricing
     * @param array $data
     * @return true false based on result
     */
    public static function fetchResellerContentRestrictionsFetchAjax($request)
    {
        try {
            $data = DB::table('reseller_content_restrictions')
                    ->select(array('reseller_content_restrictions.*','reseller_media_type.name as media_name'))
                    ->leftJoin('reseller_media_type', 'reseller_content_restrictions.media_type', '=', 'reseller_media_type.id');
            $val  = $request->all();

            if ($request->has('from') && $request->get('from') != '') {
                $from = $request->get('from');
                if ($request->has('to') && $request->get('to') != '') {
                    $to = $request->get('to');
                    $data->whereBetween('start_date', [$from, $to]);
                    //$data->where('start_date','<=',$to);
                }
            }
            if (Auth::User()->user_type == 1) {
                $data->where('reseller_content_restrictions.reseller_ID', '=', Auth::User()->id);
            }

            return Datatables::of($data)->make(true);

        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    /**
     * Update sales report table
     * @param array $data
     * @return json object
     */
    public static function updateSalesReportAjax($data)
    {

        unset($data['_token']);
        $id = $data['sales_report_ID'];
        unset($data['sales_report_ID']);
        try {
            $update                = ResellerSalesReport::find($id);
            $update->create_date   = date('Y-m-d', strtotime($data['create_date']));
            $update->currency_code = $data['currency_code'];
            $update->sales_month   = $data['sales_month'];
            $update->sales_year    = $data['sales_year'];

            if (isset($data['open_for_edit'])) {
                $update->open_for_edit = 1;
            } else {
                $update->open_for_edit = 0;
            }

            $update->record_status            = $data['record_status'];
            $update->totalsales_local         = $data['totalsales_local'];
            $update->reseller_local           = $data['reseller_local'];
            $update->pond5_local              = $data['pond5_local'];
            $update->totalsales_USD           = $data['totalsales_USD'];
            $update->reseller_USD             = $data['reseller_USD'];
            $update->pond5_USD                = $data['pond5_USD'];
            $update->date_exported_accounting = date('Y-m-d', strtotime($data['date_exported_accounting']));
            $update->update();
            $result = ['message' => 'Sales Report Updated Successfully', 'result' => 'success'];

        } catch (\Exception $e) {
            $result = ['message' => $e->getMessage(), 'result' => 'error'];
        }

        return $result;
    }
    /**
     * Update sales open for sale status
     * @param array $data
     * @return json object
     */
    public static function updateSalesReportOpenForEditAjax($data)
    {
        try {

            $update                = ResellerSalesReport::find($data['id']);
            $update->open_for_edit = $data['status'];
            $update->update();
            $result = ['message' => 'Open Status Updated Successfully', 'result' => 'success'];

        } catch (\Exception $e) {
            $result = ['message' => $e->getMessage(), 'result' => 'error'];
        }
        return $result;
    }

    /**
     * Update sales report item table
     * @param array $data
     * @return json object
     */
    public static function updateSalesReportItemAjax($data)
    {

        unset($data['_token']);
        $id = $data['sales_report_item_ID'];
        try {
            $update                           = ResellerSalesReportItems::find($id);
            $update->create_date              = date('Y-m-d', strtotime($data['create_date']));
            $update->sale_date                = date('Y-m-d', strtotime($data['sale_date']));
            $update->transaction_ID           = $data['transaction_ID'];
            $update->content_ID               = $data['content_ID'];
            $update->media_type               = $data['media_type'];
            $update->content_size             = $data['content_size'];
            $update->currency_conversion_rate = $data['currency_conversion_rate'];
            $update->price_standard_local     = $data['price_standard_local'];
            $update->price_edited_local       = $data['price_edited_local'];
            $update->price_edit_reason_ID     = $data['price_edit_reason_ID'];
            $update->delete_item              = $data['delete_item'];
            $update->delete_reason_ID         = $data['delete_reason_ID'];
            $update->reduced_commission       = $data['reduced_commission'];
            $update->commission_rate          = $data['commission_rate'];
            $update->updated_by               = $data['updated_by'];
            $update->update_date              = date('Y-m-d', strtotime($data['update_date']));
            $update->update();
            $result = ['message' => 'Sales Report Item Updated Successfully', 'result' => 'success'];

        } catch (\Exception $e) {
            $result = ['message' => $e->getMessage(), 'result' => 'error'];
        }

        return $result;
    }

     
}
