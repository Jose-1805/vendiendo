<?php namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class TerminosCondiciones {

	public function handle($request, Closure $next)
	{
		$user = Auth::user();
		$perfil = $user->perfil;
		if ($perfil->nombre != "superadministrador" ){
			$admin = User::find(Auth::user()->userAdminId());
			if ($admin->terminos_condiciones != "si"){
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
		return $next($request);

	}

}
