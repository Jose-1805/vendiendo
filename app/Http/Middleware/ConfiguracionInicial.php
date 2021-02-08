<?php namespace App\Http\Middleware;

use Closure;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Resolucion;
use App\Models\Unidad;
use Illuminate\Support\Facades\Auth;

class ConfiguracionInicial {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$unidades = Unidad::unidadesPermitidas()->count();
		$categorias = Categoria::permitidos()->count();
		$proveedores = Proveedor::permitidos()->count();
		$resoluciones = Resolucion::permitidos()->count();
		$perfil = Auth::user()->perfil;

		if (($unidades > 0 && $categorias > 0 && $proveedores > 0 && $resoluciones > 0) || $perfil->nombre == 'superadministrador'){
			return $next($request);
		}else{
		    if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'no')
                return $next($request);

			return redirect('/');
		}
	}

}
