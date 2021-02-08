<?php namespace App\Http\Controllers;

use App\Http\Requests\RequestPreguntaFrecuente;
use App\Models\ActualizacionSistema;
use App\Models\Categoria;
use App\Models\PreguntaFrecuente;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Resolucion;
use App\Models\Unidad;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Caja;
use Illuminate\Support\Facades\Session;
use App\Models\PlanUsuario;
class InicioController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth',["except"=>["getPreguntasFrecuentes","getShow"]]);
	}

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
        $user = Auth::user();
		$perfil = $user->perfil;
		if ($perfil->nombre != "superadministrador" ){
			$admin = User::find(Auth::user()->userAdminId());
			if ($admin->terminos_condiciones != "si"){
				return view('terminos_condiciones');
			}
		}
		$modulos = $user->modulosActivos();
		$aux_facturacion = $aux_producto = 0;

		foreach ($modulos as $key => $value){
			$modulo = $modulos[$key];
			//echo $modulo->nombre . "<br>";
			if ($modulo->nombre == "facturacion" && $modulo->seccion == "configuracion")
				$aux_facturacion = 1;
			if ($modulo->nombre == "Productos")
				$aux_producto = 1;
		}

		$url = url('/caja/create');

		$reportesPermitidos = Auth::user()->permitirReportes($user->userAdminId(), $user->plan()->id, 'activo');

        if(Auth::user()->permitirFuncion("Iniciar mayor","caja","configuracion") &&
            (
                Auth::user()->bodegas == 'no'
                || (Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            )) {

            if(Auth::user()->bodegas == 'si') {
                if( Auth::user()->admin_bodegas == 'si') {
                    if (count(Caja::cajasPermitidas()->where('principal_ab', 'si')->where('estado', 'abierta')->get()) == 0) {
                        $mensaje = "Para poder realizar compras inicie la caja maestra en el sistema. <a href='" . $url . "'>(Iniciar caja maestra)</a>";
                        Session::flash("mensaje_validacion_caja", $mensaje);
                    }
                }
            }else{
                if (count(Caja::cajasPermitidas()->where('estado', 'abierta')->get()) == 0) {
                    $mensaje = "Para poder facturar, comprar o asignar cajas, inicie la caja maestra en el sistema. <a href='" . $url . "'>(Iniciar caja maestra)</a>";
                    Session::flash("mensaje_validacion_caja", $mensaje);
                }
            }

            $unidades = Unidad::unidadesPermitidas()->count();
            $categorias = Categoria::permitidos()->count();
            $proveedores = Proveedor::permitidos()->count();
            $resoluciones = Resolucion::permitidos()->count();
            //return view('inicio');
            $perfil_log = $perfil->nombre;

			$opciones = [];
			$validate = 0;

			if ($aux_facturacion == 1 && $aux_producto == 1){
				//echo "los dos";
				$opciones = ['unidades'=>$unidades, 'categorias'=>$categorias, 'proveedores'=>$proveedores, 'resoluciones'=>$resoluciones,'perfil_log'=>$perfil_log];
				if ($unidades > 0 && $categorias > 0 && $proveedores > 0 && $resoluciones > 0) {
					$validate = 1;
				}
			}else if ($aux_facturacion == 1 && $aux_producto == 0){
				//echo "Facturacion";
				$opciones = ['resoluciones'=>$resoluciones,'perfil_log'=>$perfil_log];
				if ($resoluciones > 0) {
					$validate = 1;
				}
			}else if ($aux_facturacion == 0 && $aux_producto == 1){
				//echo "productos";
				$opciones = ['unidades'=>$unidades, 'categorias'=>$categorias, 'proveedores'=>$proveedores,'perfil_log'=>$perfil_log];
				if ($unidades > 0 && $categorias > 0 && $proveedores > 0) {
					$validate = 1;
				}
			}else{
				$opciones = ['perfil_log'=>$perfil_log];
			}

            if ($validate == 1) {
                return view('inicio')->with('reportesPermitidos', $reportesPermitidos);
            } else {
                return view('configuracion_inicial', compact('opciones'))->with("render_cuadre_caja",false);
            }
        }else /*if($perfil->nombre == "")*/{
            return view('inicio')->with('reportesPermitidos', $reportesPermitidos);
        }
	}

	public function postGuardarUsuarioActualizacion(){
        $ultima_actualizacion = ActualizacionSistema::ultimaActualizacion();
        if($ultima_actualizacion && !$ultima_actualizacion->usuarioTieneActualizacion()){
            Auth::user()->actualizacionesSoftware()->save($ultima_actualizacion);
        }
        return ['success'=>true];
    }

	public function getPreguntasFrecuentes(){

		$preguntas = PreguntaFrecuente::all();
		return view("preguntas_frecuentes.index")->with("preguntas",$preguntas);
	}
	public function getShow($pregunta_id){

		if ($pregunta_id != 0)
			$preguntas = PreguntaFrecuente::where('id',$pregunta_id)->get();
		else
			$preguntas = PreguntaFrecuente::all();

		return view("preguntas_frecuentes.lista")->with("preguntas",$preguntas);
	}

	public function postPreguntaFrecuenteStore(RequestPreguntaFrecuente $request){
		if(Auth::user()->perfil->nombre == "superadministrador"){
				$pregunta = new PreguntaFrecuente();
				$pregunta->fill($request->all());
				$pregunta->save();
				Session::flash("mensaje","La pregunta ha sido registrada con éxito");
				return ["success"=>true];
		}else{
			return response("Unauthorized.",401);
		}
	}

	public function postPreguntaFrecuenteShowEditar(Request $request){
		if(Auth::user()->perfil->nombre == "superadministrador"){
			if($request->has("id")){
				$pregunta = PreguntaFrecuente::find($request->input("id"));
				if($pregunta){
					return view("preguntas_frecuentes.form")->with("pregunta",$pregunta);
				}
			}
			return response(["Error"=>["La información enviada es incorrecta."]]);
		}else{
			return response("Unauthorized.",401);
		}
	}

	public function postPreguntaFrecuenteUpdate(RequestPreguntaFrecuente $request){
		if(Auth::user()->perfil->nombre == "superadministrador"){
				if($request->has("id")) {
					$pregunta = PreguntaFrecuente::find($request->input("id"));
					if($pregunta) {
						$pregunta->fill($request->all());
						$pregunta->save();
						Session::flash("mensaje", "La pregunta ha sido editada con éxito");
						return ["success" => true];
					}
				}
				return response(["Error"=>["La información enviada es incorrecta."]]);
		}else{
			return response("Unauthorized.",401);
		}
	}

	public function postPreguntaFrecuenteDelete(Request $request){
		if(Auth::user()->perfil->nombre == "superadministrador"){
			if($request->has("id")) {
				$pregunta = PreguntaFrecuente::find($request->input("id"));
				if($pregunta) {
					$pregunta->delete();
					Session::flash("mensaje", "La pregunta ha sido eliminada con éxito");
					return ["success" => true];
				}
			}
			return response(["Error"=>["La información enviada es incorrecta."]]);
		}else{
			return response("Unauthorized.",401);
		}
	}

	public function postAceptarTerminosCondiciones(Request $request){
		$user = Auth::user();
		if($user->perfil->nombre == "administrador" || $user->perfil->nombre == "proveedor"){
			$user->terminos_condiciones = "si";
			$user->save();
			return ["success"=>true];
		}else{
			return ["success"=>false,"mensaje"=>"Usted no tiene permisos para realizar esta acción"];

		}
	}
}
