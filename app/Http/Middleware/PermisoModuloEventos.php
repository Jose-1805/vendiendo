<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class PermisoModuloEventos{

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if(Auth::user()->permitirModulo("Eventos","inicio")) {
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
