<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class PermisoModuloReportes{

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if((Auth::user()->perfil->nombre == "usuario" || Auth::user()->perfil->nombre == "administrador") && Auth::user()->permiso_reportes == 'si') {
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
