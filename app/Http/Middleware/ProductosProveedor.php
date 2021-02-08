<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ProductosProveedor {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if(Auth::user()->permitirModulo("Productos proveedor","inicio") && Auth::user()->perfil->nombre == "proveedor") {
			return $next($request);
		}

		if ($request->ajax())
		{
			return response('Unauthorized.', 401);
		}
		else
		{
			return redirect('/');
		}
	}

}
