<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class AuthApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Obtenemos el api-key que el usuario envia

        //por defecto RN establece que las apis deben usar minusculas siempre, pero como ya está funcionando la app de inventario, se dejan conviviendo las dos opciones
        if ( (isset(getallheaders()["Api-Key"]) || isset(getallheaders()["api-key"])) &&  (isset(getallheaders()["User-Id"] ) || isset(getallheaders()["user-id"] )  ) )  {
            $key = isset(getallheaders()["api-key"])?getallheaders()["api-key"]:getallheaders()["Api-Key"];
            $user_id = isset(getallheaders()["user-id"])?getallheaders()["user-id"]:getallheaders()["User-Id"];

            $user = User::find(Crypt::decrypt($user_id));
            if($user && $user->exists) {
                if ($user->plan() && $key == $user->api_key) {
                    Auth::login($user);
                    return $next($request);
                }
            }
        }
        return response()->json(['error' => 'unauthorized'], 401);
    }
}