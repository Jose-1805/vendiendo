<?php namespace App\Http\Middleware;

use App\Models\ABProducto;
use App\Models\Modulo;
use App\Models\Producto;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PermisoModuloFacturas {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if(Auth::user()->permitirModulo("Facturas","inicio")) {
			if(
                (count(Producto::permitidos()->get()) && Auth::user()->bodegas == 'no')
                || (count(ABProducto::permitidos()->get()) && Auth::user()->bodegas == 'si')
            ) {
				
				$modulo = Modulo::where("nombre","Facturas")->where("seccion","inicio")->first();
				if($modulo) {
					$permitir = true;
					if (Auth::user()->bodegas == "si" && Auth::user()->admin_bodegas == "si" && $modulo->privilegio_administrador_bodegas == "no")
						$permitir = false;

					if ($permitir)
						return $next($request);
				}
			}else{
				if ($request->ajax()) {
					return response(["Error"=>["<p>Para crear facturas es necesario tener <a href='".url("/productos")."'>productos</a> creados.</p>"]], 422);
				}else{
					Session::flash("mensaje_validacion", "<p>Para crear facturas es necesario tener <a href='".url("/productos")."'>productos</a> creados.</p>");
					return redirect()->back();
				}
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
