<?php namespace App\Http\Middleware;

use App\Models\UserDomicilio;
use Closure;
use Illuminate\Support\Facades\Auth;

class AuthApiDomicilio {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		// Obtenemos el api-key que el usuario envia
		if (isset(getallheaders()["api-key"])) {
			$api_key = getallheaders()["api-key"];
			$user = UserDomicilio::where('api_key',$api_key)->first();
			if($user) {
				if ($request->has('latitud') && $request->has('longitud'))
					return $next($request);
				else
					return response()->json(['error' => 'Se requiere datos de posicionamiento para hacer de este servicio un servicio más eficiente'], 401);
			}else{
				return response()->json(['error' => 'Código de activación inválido'], 401);
			}
		}
		return response()->json(['error' => 'unauthorized'], 401);
	}

}
