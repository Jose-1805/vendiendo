<?php namespace App\Http\Middleware;

use App\Models\Modulo;
use Closure;
use Illuminate\Support\Facades\Auth;

class PermisoModuloProveedor {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
        if(Auth::user()->permitirModulo("proveedores","configuracion")) {
            $modulo = Modulo::where("nombre","proveedores")->where("seccion","configuracion")->first();
            if($modulo) {
                $permitir = true;
                if (Auth::user()->bodegas == "si" && Auth::user()->admin_bodegas == "si" && $modulo->privilegio_administrador_bodegas == "no")
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
