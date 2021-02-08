<?php namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;
use Session;
use App\General;

class SessionDemoMiddleware {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if (config('options.version_proyecto') == 'DEMO'){
			$user = Auth::user();
			$date_time_actual = date('Y-m-d H:i:s');
			if($user){
				if (General::calcula_minutos($date_time_actual,$user->fecha_primer_logueo) <= 0){
					Auth::logout();
					Session::flush();
					return redirect()->guest('auth/login');
				}else{
					$mensaje = "Esté demo vencerá el ".General::fechaVencimientoDemo($user->fecha_primer_logueo);
					Session::flash("mensaje_validacion_session_demo", $mensaje);
				}
			}
			//return $next($request);
		}
		return $next($request);
	}

}

