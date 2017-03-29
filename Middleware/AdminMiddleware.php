<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }
        $role = Auth::User()->user_type;
        if ($role != 0) {
            return redirect('/')->with('error', 'You are not authorized to access this.');
        }
        return $next($request);
    }
}
