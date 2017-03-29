<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use View;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    /**
     * This will call from child controller to share common variables     *
     *
     */
    public function __construct()
    {

        $this->middleware(function ($request, $next) {
            if (Auth::check()) {
                $this->userName = Auth::user()->user_name;
                $this->userId   = Auth::user()->id;                
                $this->role     = Auth::User()->user_type;
                $this->share();
            }
            return $next($request);
        });

    }

    /**
     *  common view variable
     *
     * @return [type] [description]
     */
    public function share()
    {
        View::share('userName', $this->userName);
        View::share('userRole', $this->role);
    }
}
