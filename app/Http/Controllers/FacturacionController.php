<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Factura;
use App\Models\Resolucion;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RequestNuevaResolucion;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\RequestEditarResolucion;

class FacturacionController extends Controller {


	public function __construct()
	{
		$this->middleware("modConfiguracion",["except"=>["postStoreResolucion"]]);
		$this->middleware("modFacturacion");
		$this->middleware("terminosCondiciones");
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		return view("facturacion.index");
	}

	public function postUploadLogo(Request $request){

		$usuario = null;
		if(Auth::user()->perfil->nombre == "administrador"){
			$usuario = Auth::user();
		}else{
			$usuario = User::find(Auth::user()->usuario_creador_id);
		}
		if($usuario) {
			if ($request->hasFile("logo")) {
				if ($request->file('logo')->isValid()) {
					$data = [];
					$img = $request->file("logo");
					$peso = $img->getClientSize();
					$tamanos = getimagesize($img);
					$with = intval($tamanos[0]);
					$height = intval($tamanos[1]);

					$proporcionHeight = ($with / 3)*2;
					if($proporcionHeight != $height){
						$data ["mensaje"] = "Para una mejor visualización de la imagen en las facturas, utilice imágenes con relación de aspecto 2:3";
					}

					if(($peso/1000) > 400){
						return response(['error' => ['La imagen debe pesar máximo 400 KB']], 422);
					}
					$ruta = public_path("img/users/logo/".$usuario->id);
					$nombre = str_replace(' ','_',$img->getClientOriginalName());
					$img->move($ruta, $nombre);
					if($usuario->logo){
					    @unlink($ruta.'/'.$usuario->logo);
                    }
					$usuario->logo = $nombre;
					$usuario->save();
					$data["success"]=true;
					return $data;
				} else {
					return response(['error' => ['Ocurrio un error al subir la imagen, intente nuevamente']], 422);
				}
			}else{
				return response(['error' => ['Seleccione una imagen']], 422);
			}
		}else{
			return response(['error' => ['La información enviada es incorrecta']], 422);
		}
	}

	public function postGuardarDatosFacturacion(Request $request){
		$usuario = null;
		if(Auth::user()->perfil->nombre == "administrador"){
			$usuario = Auth::user();
		}else{
			$usuario = User::find(Auth::user()->usuario_creador_id);
		}
		if($usuario) {
			if($request->has("encabezado_factura")){
				$usuario->encabezado_factura = $request->input("encabezado_factura");
			}

			if($request->has("pie_factura")){
				$usuario->pie_factura = $request->input("pie_factura");
			}

			if($request->has("datos_cliente_vendedor")){
				$usuario->datos_cliente_vendedor = $request->input("datos_cliente_vendedor");
			}else{
				$usuario->datos_cliente_vendedor = "no";
			}

			if($request->has("observaciones_bn")){
				$usuario->observaciones_bn = $request->input("observaciones_bn");
			}else{
				$usuario->observaciones_bn = "no";
			}

			if($request->has("observaciones_color")){
				$usuario->observaciones_color = $request->input("observaciones_color");
			}else{
				$usuario->observaciones_color = "no";
			}

			$usuario->save();
			return ["success"=>true];
		}else{
			return response(['error' => ['La información enviada es incorrecta']], 422);
		}
	}

	public function postStoreResolucion(RequestNuevaResolucion $request){
		$resolucion = new Resolucion();
		$data = $request->all();
		$usuario = null;
		if(Auth::user()->perfil->nombre == "administrador"){
			$usuario = Auth::user();
		}else{
			$usuario = User::find(Auth::user()->usuario_creador_id);
		}

		if($usuario) {
			//validar que la fecha sea igual o menor a la actual
			$fechaInicio = strtotime($data["fecha"]);
			$fechaVencimiento = strtotime($data["fecha_vencimiento"]);
			$hoy = strtotime(date("Y-m-d"));
			if($fechaInicio > $hoy){
				return response(["error"=>["La fecha de emisión seleccionada debe ser menor o igual a la fecha actual."]],422);
			}

			if($fechaVencimiento <= $hoy){
				return response(["error"=>["La fecha de vencimiento seleccionada debe ser mayor a la fecha actual."]],422);
			}

			if($request->has("fecha_notificacion")){
				$fechaNotificacion = strtotime($data["fecha_notificacion"]);
				if($fechaVencimiento < $fechaNotificacion){
					return response(["error"=>["La fecha de notificación seleccionada debe ser menor a la fecha de vencimiento."]],422);
				}
				if($fechaNotificacion <= $hoy){
					return response(["error"=>["La fecha de notificación seleccionada debe ser mayor a la fecha actual."]],422);
				}
			}

			if($request->has("numero_notificacion")){
				if($request->input("numero_notificacion") == 0){
					$data["numero_notificacion"] = null;
				}else {
					$n = $request->input("numero_notificacion");
					if ($n < $data["inicio"] || $n > $data["fin"]) {
						return response(["error" => ["El número de notificación ingresado debe estar entre " . $data['inicio'] . " y " . $data['fin'] . "."]], 422);
					}
				}
			}

			//Se valida que el usuario pueda registrar una sola resolucion por dia
			$resoluciones_fecha = Resolucion::where("fecha",$data["fecha"])->where("usuario_id",$usuario->id)->get();
			if(count($resoluciones_fecha)){
				return response(["error"=>["Ya existe una resolución registrada en la fecha seleccionada"]],422);
			}

			//el numero de inicio debe ser menor al numero final
			if($data['inicio'] >= $data['fin']){
				return response(["error"=>["El valor del campo número inicio factura debe ser menor al valor del campo número fin factura"]],422);
			}

			//las resoluciones pueden iniciar con cualquier numero de consecutivo
			//siempre y cuando cumplan las siguientes condiciones
			//
			//El numero de inicio no puede ser igual a 0
			//Si existe mas de una resolución:
			//***** El número de inicio no puede ser mayor de 1 de el numero de fin de la resolución que tenga el número de inicio mayor (la última resolución)
			//***** El número de inicio no puede ser menor o igual al número de inicio de la resolución que tenga el número de inicio mayor (la última resolución)
			//***** El numero de inicio no puede ser menor o igual al número de consecutivo que se haya asignado a la última factura (si existe)

			if($data['inicio'] == 0){
				return response(["error"=>["El valor del campo número inicio factura debe ser mayor de 0"]],422);
			}

			$resoluciones_ant = Resolucion::where("usuario_id", $usuario->id)->get()->count();
			if($resoluciones_ant){
				$ultima = Resolucion::where("usuario_id", $usuario->id)->orderBy("inicio","DESC")->first();
				if($data['inicio'] > ($ultima->fin + 1)){
					return response(["error"=>["El valor del campo número inicio factura no puede ser mayor a ".($ultima->fin + 1)]],422);
				}
				if($data['inicio'] <= $ultima->inicio){
					return response(["error"=>["El valor del campo número inicio factura no puede ser menor a ".($ultima->inicio + 1)]],422);
				}

				$ultima_factura = Factura::ultimaFactura();
				if($ultima_factura){
					if($data['inicio'] <= intval($ultima_factura->numero)){
						return response(["error"=>["El valor del campo número inicio factura no puede ser menor a ".(intval($ultima_factura->numero) + 1).", ya han sido registradas facturas con consecutivos menores."]],422);
					}
				}
			}

			$data["estado"] = "en espera";
			$data["usuario_creador_id"] = Auth::user()->id;
			$data["usuario_id"] = $usuario->id;

			$resolucion->fill($data);
			$resolucion->save();
			Session::flash("posicion", "Resoluciones");
			return ["success" => true];
		}
		return response(["error"=>["La información enviada es incorrecta"]],422);
	}


	public function getResoluciones(Request $request){
		$filtro = "";
		if($request->has("filtro")){
			$resoluciones = $this->listaFiltroResoluciones($request->get("filtro"));
			$filtro = $request->get("filtro");
		}else {
			$resoluciones = Resolucion::permitidos()->orderBy("updated_at", "DESC")->orderBy("id","DESC")->paginate(10);
		}
		$resoluciones->setPath(url('/facturacion/resoluciones'));
		return view("facturacion.resoluciones")->with("resoluciones",$resoluciones)->with("filtro",$filtro);
	}


	public function postFiltroResoluciones(Request $request){
		if($request->has("filtro")){
			$resoluciones = $this->listaFiltroResoluciones($request->get("filtro"));
		}else{
			$resoluciones = Resolucion::permitidos()->orderBy("updated_at","DESC")->orderBy("id","DESC")->paginate(10);
		}
		$resoluciones->setPath(url('/facturacion/resoluciones'));
		return view("facturacion.lista_resoluciones")->with("resoluciones",$resoluciones);
	}

	public function listaFiltroResoluciones($filtro){
		$f = "%".$filtro."%";
		return $resoluciones = Resolucion::permitidos()->where(
			function($query) use ($f){
				$query->where("numero","like",$f)
					->orWhere("fecha","like",$f);
			}
		)->orderBy("updated_at","DESC")->orderBy("id","DESC")->paginate(10);
	}

	public function postFormEditarResolucion(Request $request){
		if(Auth::user()->permitirFuncion("EditarResolucion","facturacion","configuracion")){
			if($request->has("id")){
				$resolucion = Resolucion::permitidos()->where("id",$request->input("id"))->first();
				if($resolucion){
					if(!count($resolucion->facturas)) {
						return view("facturacion.form_accion_resolucion")->with("resolucion", $resolucion);
					}else{
						return response(["error"=>["Esta resolución no puede ser editada, ya se han relacionado facturas con sus consecutivos."]],422);
					}
				}
			}else{
				return response(["error"=>["La información enviada es incorrecta"]],422);
			}
		}else{
			return response(["error"=>["Usted no tiene permisos para realizar esta acción"]],401);
		}
	}

	public function postEditarResolucion(RequestEditarResolucion $request){
		if(Auth::user()->permitirFuncion("EditarResolucion","facturacion","configuracion")){
			$resolucion = Resolucion::permitidos()->where("id",$request->input("id"))->first();
			if($resolucion) {
				if (!count($resolucion->facturas)) {
					$usuario = User::find($resolucion->usuario_id);
					$validNumero = Resolucion::where("numero", $request->input("numero"))->where("id", "<>", $resolucion->id)->get();
					if (!count($validNumero)) {
						//si no existe mas de una resolucion, el campo inicio es requerido
						if (Resolucion::permitidos()->get()->count() < 2) {
							if (!$request->has("inicio")) {
								return response(["error" => ["El campo inicio es requerido"]], 422);
							}

							if (!$request->has("fin")) {
								return response(["error" => ["El campo fin es requerido"]], 422);
							}
						}

						$fechaInicio = strtotime($request->input("fecha"));
						$fechaVencimiento = strtotime($request->input("fecha_vencimiento"));
						$hoy = strtotime(date("Y-m-d"));

						if ($fechaInicio > $hoy) {
							return response(["error" => ["La fecha de emisión seleccionada debe ser menor o igual a la fecha actual."]], 422);
						}

						if($fechaVencimiento <= $hoy){
							return response(["error"=>["La fecha de vencimiento seleccionada debe ser mayor a la fecha actual."]],422);
						}

						if($request->has("fecha_notificacion")){
							$fechaNotificacion = strtotime($request->input("fecha_notificacion"));
							if($fechaVencimiento < $fechaNotificacion){
								return response(["error"=>["La fecha de notificación seleccionada debe ser menor a la fecha de vencimiento."]],422);
							}
							if($fechaNotificacion <= $hoy){
								return response(["error"=>["La fecha de notificación seleccionada debe ser mayor a la fecha actual."]],422);
							}
						}

						if($request->has("numero_notificacion")){
							$n = $request->input("numero_notificacion");
							if (Resolucion::permitidos()->get()->count() < 2) {
								if ($n < $request->input("inicio") || $n > $request->input("fin")) {
									return response(["error" => ["El número de notificación ingresado debe estar entre " . $request->input('inicio') . " y " . $request->input('fin') . "."]], 422);
								}
							}else{
								if ($n < $resolucion->inicio || $n > $resolucion->fin) {
									return response(["error" => ["El número de notificación ingresado debe estar entre " . $resolucion->inicio . " y " . $resolucion->fin . "."]], 422);
								}
							}
						}

						//Se valida que el usuario pueda registrar una sola resolucion por dia
						$resoluciones_fecha = Resolucion::where("fecha", $request->input("fecha"))->where("usuario_id", $usuario->id)->where("id","<>",$resolucion->id)->get();
						if (count($resoluciones_fecha)) {
							return response(["error" => ["Ya existe una resolución registrada en la fecha seleccionada"]], 422);
						}

						if (Resolucion::permitidos()->get()->count() < 2) {
							//el numero de inicio debe ser menor al numero final
							if ($request->input('inicio') >= $request->input('fin')) {
								return response(["error" => ["El valor del campo número inicio factura debe ser menor al valor del campo número fin factura"]], 422);
							}
							$resolucion->inicio = $request->input("inicio");
							$resolucion->fin = $request->input("fin");
						}


						if($request->has('prefijo'))
						    $resolucion->prefijo = $request->input("prefijo");

						$resolucion->numero = $request->input("numero");
						$resolucion->fecha = $request->input("fecha");
						$resolucion->fecha_vencimiento = $request->input("fecha_vencimiento");
						$resolucion->fecha_notificacion = $request->input("fecha_notificacion");
						if($request->has("numero_notificacion")){
							if($request->input("numero_notificacion") == 0) {
								$resolucion->numero_notificacion = null;
							}else {
								$resolucion->numero_notificacion = $request->input("numero_notificacion");
							}
						}
						$resolucion->save();
						Session::flash("mensaje", "La resolución ha sido editada con éxito");
						return ["success" => true];
					} else {
						return response(["error" => ["Ya existe una resolución con el numero ingresado"]], 422);
					}
				}else{
					return response(["error"=>["Esta resolución no puede ser editada, ya se han relacionado facturas con sus consecutivos."]],422);
				}
			}else{
				return response(["error"=>["La información enviada es incorrecta"]],422);
			}
		}else{
			return response(["error"=>["Usted no tiene permisos para realizar esta acción"]],401);
		}
	}

	public function postDestroyResolucion(Request $request){
		if(Auth::user()->permitirFuncion("EditarResolucion","facturacion","configuracion")){
			if($request->has("id")){
				$resolucion = Resolucion::permitidos()->where("id",$request->input("id"))->first();
				if($resolucion){
					if($resolucion->isLast()){
						if(!count($resolucion->facturas)){
							$resolucion->delete();
							Session::flash("mensaje","La resolución ha sido eliminada con éxito.");
							return ["success"=>true];
						}else{
							return response(["error"=>["No es posible eliminar esta resolución, solo puede  eliminar resoluciones que no tengan facturas relacionadas."]],422);
						}
					}else{
						return response(["error"=>["No es posible eliminar esta resolución, solo puede eliminar la última resolución que haya registrado."]],422);
					}
				}else{
					return response(["error"=>["La información enviada es incorrecta"]],422);
				}
			}else{
				return response(["error"=>["La información enviada es incorrecta"]],422);
			}
		}else{
			return response(["error"=>["Usted no tiene permisos para realizar esta acción"]],401);
		}
	}
}
