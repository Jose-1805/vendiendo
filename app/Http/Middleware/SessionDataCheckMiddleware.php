<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class SessionDataCheckMiddleware {

	/**
	 * Check session data, if role is not valid logout the request
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {

		Session::set('lastActive', date('U'));
		Session::forget('idleWarningDisplayed');
		Session::forget('logoutWarningDisplayed');

		return $next($request);
	}

}
