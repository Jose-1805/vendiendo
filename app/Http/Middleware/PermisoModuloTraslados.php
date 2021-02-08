<?php namespace App\Http\Middleware;

use App\Models\Modulo;
use App\Models\Producto;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PermisoModuloTraslados {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if(Auth::user()->permitirModulo("Traslados","inicio") && Auth::user()->bodegas == 'si') {
			$modulo = Modulo::where("nombre","Traslados")->where("seccion","inicio")->first();
			if($modulo) {
				$permitir = true;
				if ($modulo->privilegio_administrador_bodegas == "no")
					$permitir = false;

				if ($permitir)
					return $next($request);
			}
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
