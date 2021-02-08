<?php namespace App\Http\Middleware;

use App\Models\Almacen;
use App\Models\Caja;
use App\Models\Modulo;
use Illuminate\Support\Facades\Auth;
use Closure;
use Session;

class CajaPermiso {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$user = Auth::user();
		$perfil = $user->perfil;
		/*if ($perfil->nombre != "superadministrador" ){
			$fecha_actual = date("Y"). "-". date("m") . "-" . date("d");
			$permitir = count(Caja::cajasPermitidas()->where('fecha',$fecha_actual)->get());
			//dd($permitir);
			if ($permitir != 0){
				return $next($request);
			}else{
				$mensaje = "Recuerde cuadrar la caja para el dia de hoy";
				Session::flash("mensaje_validacion_caja", $mensaje);				
				return redirect('/');
			}
		}else{

			//dd("ok");
			return redirect('/');
		}*/

		if(Auth::user()->permitirModulo("caja","configuracion")) {

			$modulo = Modulo::where("nombre","caja")->where("seccion","configuracion")->first();
			if($modulo) {
				$permitir = true;
				if (Auth::user()->bodegas == "si" && Auth::user()->admin_bodegas == "si" && $modulo->privilegio_administrador_bodegas == "no")
					$permitir = false;

				//si tiene almacenes asignados puede continuar un administrador de almacén
                if (Auth::user()->bodegas == "si" && Auth::user()->admin_bodegas == 'no'){
                    $almacen = Almacen::permitidos()->where('administrador',Auth::user()->id)->first();
                    if(!$almacen)$permitir = false;
                }

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
