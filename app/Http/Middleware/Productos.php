<?php namespace App\Http\Middleware;

use App\Models\Categoria;
use App\Models\MateriaPrima;
use App\Models\Modulo;
use App\Models\Proveedor;
use App\Models\Unidad;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Productos {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if(Auth::user()->permitirModulo("Productos","inicio")) {
			$mensaje = "<p>Para la creación de un producto se requiere que haya registrado ";
			$permitir = true;
			if(count(Unidad::unidadesPermitidas()->get())){
				$mensaje .= "unidades y ";
			}else{
				$permitir = false;
				$mensaje .= "<a href='".url("/unidades")."'>unidades</a> y ";
			}

			if(count(Categoria::permitidos()->get())){
				$mensaje .= "categorias, además de ";
			}else{
				$permitir = false;
				$mensaje .= "<a href='".url("/categoria")."'>categorias</a>, además de ";
			}
			if(count(Proveedor::permitidos()->get())){
				$mensaje .= "proveedores y/o ";
			}else{
				$permitir = false;
				$mensaje .= "<a href='".url("/proveedor")."'>proveedores</a> y/o ";
			}

			//if(count(MateriaPrima::permitidos()->get())){
			//	$mensaje .= "materias primas según sea el caso.</p>";
			//}else{
			//	$permitir = false;
				$mensaje .= "<a href='".url("/materia-prima")."'>materias primas</a> según sea el caso.</p>";
			//}

			if(!$permitir){
				if ($request->ajax()) {
					return response(["Error"=>[$mensaje]], 422);
				}else{
					Session::flash("mensaje_validacion", $mensaje);
					return redirect()->back();
				}
			}

			$modulo = Modulo::where("nombre","Productos")->where("seccion","inicio")->first();
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
