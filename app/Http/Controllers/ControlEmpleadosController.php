<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\Bodega;
use Illuminate\Http\Request;
use App\Models\ControlEmpleados;
use App\Models\ControlEmpleadosRegistros;
use App\Http\Requests\RequestControlEmpleados;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\User;


use Redirect;
use Session;

class ControlEmpleadosController extends Controller {

	public function __construct(){
		$this->middleware("auth");
	 	//$this->middleware('modControlEmpleados', ['except' => ['getIndex','getListControlEmpleados','postAccion','postForm','postCierraTodasLasSesiones','postCambiaEstadoEmpleado','postCierraIniciaSesion']]);
	 	//$this->middleware('modEmpleado', ['except' => ['getInicioSession','getListControlEmpleadosInicio','postCierraSesionEmpleado']]);

		 $this->middleware('modControlEmpleados');
		 $this->middleware('modEmpleado');
		$this->middleware("modConfiguracion");
		$this->middleware("terminosCondiciones");
	}
	
	/************************* CONFIGURACION *****************************/
	
	//index - configuracion
	public function getIndex(){
		return view("control_empleados.index");
	}

	//Lista tabla - configuracion
	public function getListControlEmpleados(Request $request){
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = 'control_empleados.id';//$columna[$sortColumnIndex]['data'];   
        $myArray=[];            
        $empleados = ControlEmpleados::permitidos()->select('control_empleados.id AS id','control_empleados.nombre AS nombre','control_empleados.cedula AS cedula','control_empleados.estado_empleado AS estado_empleado')
        								->orderBy('control_empleados.estado_empleado','ASC');

        $totalRegistros = $empleados->count();  

        if($search['value'] != null){
            $empleados = $empleados->whereRaw(
                " ( LOWER(control_empleados.nombre) LIKE '%".\strtolower($search["value"])."%' OR".
                " LOWER(control_empleados.cedula) LIKE '%".\strtolower($search["value"])."%' ".
                ")");
        }

        $parcialRegistros = $empleados->count();
        $empleados = $empleados->skip($start)->take($length);
        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($empleados->get() as $em) {            	
				$estado_sesion = 'N/A';
				$fecha_llegada = 'N/A';
				$fecha_salida = 'N/A';
            	$registros = ControlEmpleadosRegistros::select('control_empleados_registros.estado_sesion AS estado',
        									'control_empleados_registros.fecha_llegada AS fecha_llegada',
        									'control_empleados_registros.fecha_salida AS fecha_salida')->where('control_empleados_registros.control_empleado_id','=',$em->id)->orderBy('updated_at','DESC')->first();
            	if($registros != null){
            		$estado_sesion = $registros->estado;
					$fecha_llegada = $registros->fecha_llegada;
					$fecha_salida = $registros->fecha_salida;
            	}
                $myArray[]=(object) array('id' => $em->id,
                                          'nombre' => $em->nombre,
                                          'cedula' => $em->cedula,
                                          'estado_empleado' => $em->estado_empleado,
                                          'estado_sesion' => $estado_sesion,
                                          'fecha_llegada' => $fecha_llegada,
                                          'fecha_salida' => $fecha_salida);
            }
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $empleados->toSql(),              
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' =>$myArray,
            //'info'=>$empleados->get()
            ];        
        return response()->json($data);
    }

    //guardar (Crear - Editar) - configuracion
	public function postAccion(RequestControlEmpleados $request){
		if($request->has("accion")) {
			if($request->input("accion") == "Agregar") {
				if(Auth::user()->permitirFuncion("Crear","Empleados","configuracion")){
					$ControlEmpleados = new ControlEmpleados();
					$ControlEmpleados->fill($request->all());
					$ControlEmpleados->usuario_creador_id = Auth::user()->id;
                    $ControlEmpleados->usuario_id = Auth::user()->userAdminid();
					$ControlEmpleados->save();
					Session::flash("mensaje", "El empleado ha sido registrado con éxito.");
					return ["success" => true, "mensaje" => "El empleado ha sido registrado con éxito."];
				}else{
					return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
				}
			}else{
				if($request->input("accion") == "Editar") {
					if(Auth::user()->permitirFuncion("Editar","Empleados","configuracion")){
						$ControlEmpleados = ControlEmpleados::permitidos()->find($request->input("control-empleados"));
						if($ControlEmpleados) {
							$ControlEmpleados->fill($request->all());
							$ControlEmpleados->save();
							Session::flash("mensaje", "El empleado ha sido editado con éxito.");
							return ["success" => true, "mensaje" => "El empleado ha sido editado con éxito."];
						}else{
							return response(["Error"=>["La información enviada es incorrecta."]],422);
						}
					}else{
						return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
					}
				}
			}
		}
		return reponse(["error"=>["La información enviada es incorrecta"]],422);
	}
	
	//carga formulario Editar - configuracion
	public function postForm(Request $request,$id){
		if(Auth::user()->permitirFuncion("Editar","Empleados","configuracion")){
			$data_empleado = ControlEmpleados::permitidos()->where("id",$id)->first();
			if($data_empleado){
				return view("control_empleados.form")->with("data_empleado",$data_empleado)->with("accion","Editar");
			}else{
				return view("control_empleados.form")->with("data_empleado",new ControlEmpleados())->with("accion","Agregar");
			}
			return response(["Error"=>["La información enviada es incorrecta."]],422);
		}else{
			return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
		}
	}

	//cierra todas las sesiones - configuracion
	public function postCierraTodasLasSesiones(Request $request){
		if(Auth::user()->permitirFuncion("Cerrar sesion","Empleados","configuracion")){
			if($request->has("id") && $request->has('estado_check') && $request->has('fecha_inicio_sesion')) {
				$id = $request->input("id");
				$fecha_llegada =  $request->input("fecha_inicio_sesion");
				$ultimo_registro = ControlEmpleadosRegistros::where('control_empleado_id','=',$id)->where('estado_sesion','=','on')->orderBy('fecha_llegada','ASC')->get();
				if(count($ultimo_registro) > 0){
							$hora_servidor = date("Y-m-d H:i:s");
							foreach ($ultimo_registro as $registro) {
								$registro['estado_sesion'] = 'off';
								$registro['fecha_salida'] = date("Y-m-d H:i:s");
                                $registro['bodega_id'] = $registro->bodega_id;
                                $registro['almacen_id'] = $registro->almacen_id;
								$registro->save();
							}
					Session::flash("mensaje", "Se ha cerrado todas las sesiones del usuario con éxito.");
					return ["success" => true, "mensaje" => "Se ha cerrado todas las sesiones del usuario con éxito."];
				}else{
					Session::flash("mensaje", "Las sesiones abiertas ya fueron cerradas.");
					return response(["Error", "Las sesiones abiertas ya fueron cerradas."],422);
				}
			}else{
				return response(["Error"=>["La información enviada es incorrecta. Por favor intente de nuevo"]],422);
			}
		}else{
			return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
		}
	}


	//cambia estado de empleado - configuracion
	public function postCambiaEstadoEmpleado(Request $request){
		if(Auth::user()->permitirFuncion("Cambiar estado del empleado","Empleados","configuracion")){
			if($request->has("id") && $request->has("estado_empleado")) {
				$id = $request->input("id");
				$estado_actual = $request->input("estado_empleado");
				$ControlEmpleados = ControlEmpleados::permitidos()->find($id);
					if($ControlEmpleados) {
						if($estado_actual == "desactivo")
							$ControlEmpleados['estado_empleado'] = 'activo';
						else
							$ControlEmpleados['estado_empleado'] = 'desactivo';
						$ControlEmpleados->save();
						Session::flash("mensaje", "Se ha cambiado el estado del empleado con éxito");
						return ["success" => true, "mensaje" => "Se ha cambiado el estado del empleado con éxito."];
					}else{
						return response(["Error"=>["La información enviada es incorrecta."]],422);
					}
			}else{
				return response(["Error"=>["La información enviada es incorrecta. Por favor intente de nuevo"]],422);
			}
		}else{
			return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
		}
	}

	//carga formulario Editar - configuracion
	public function postView(Request $request,$id){
		$data_empleado = ControlEmpleados::permitidos()->where("id",$id)->first();
		if($data_empleado){
			$fecha_llegada = 'N/A';
			$fecha_salida = 'N/A';
        	$registros = ControlEmpleadosRegistros::select('control_empleados_registros.fecha_llegada AS fecha_llegada','control_empleados_registros.fecha_salida AS fecha_salida')->where('control_empleados_registros.control_empleado_id','=',$data_empleado->id)->orderBy('updated_at','DESC')->first();
			return view("control_empleados.view")->with("data_empleado",$data_empleado)->with("registros",$registros);
		}
		return response(["Error"=>["La información enviada es incorrecta."]],422);
	}
	/************************* INICIO ***********************************/

	// index - inicio
	public function getInicioSession(){
		return view("control_empleados.control.index");
	}

	//lista tabla - inicio
	public function getListControlEmpleadosInicio(Request $request){
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy =$columna[$sortColumnIndex]['data'];   
        $myArray=[];
        $empleados = ControlEmpleados::permitidos()
        								->select('control_empleados.id AS id',
        									'control_empleados.nombre AS nombre',
        									'control_empleados.cedula AS cedula',
        									'control_empleados.estado_empleado AS estado_empleado',
        									'control_empleados_registros.estado_sesion AS estado_sesion',
        									'control_empleados_registros.fecha_llegada AS fecha_llegada',
        									'control_empleados_registros.fecha_salida AS fecha_salida',
        									'control_empleados_registros.almacen_id',
        									'control_empleados_registros.bodega_id',
        									'control_empleados_registros.fecha_salida AS fecha_salida')
        								->join('control_empleados_registros','control_empleados_registros.control_empleado_id','=','control_empleados.id')
        								->where('control_empleados_registros.fecha_llegada','like','%'.date("Y-m-d").'%')
        								//->where('control_empleados_registros.estado_sesion','=','on')
        								->orderBy('control_empleados_registros.estado_sesion','DESC')
										->orderBy("fecha_salida","DESC")
        								->orderBy($orderBy,$sortColumnDir);
        if(Auth::user()->bodegas == 'si'){
            if(Auth::user()->admin_bodegas == 'no'){
                $almacen = Auth::user()->almacenActual();
                $empleados = $empleados->where('almacen_id',$almacen->id);
            }
        }

        $totalRegistros = $empleados->count();  
        if($search['value'] != null){
            $empleados = $empleados->whereRaw(
                " ( LOWER(control_empleados.nombre) LIKE '%".\strtolower($search["value"])."%' OR".
                " LOWER(control_empleados.cedula) LIKE '%".\strtolower($search["value"])."%' OR".
                " LOWER(control_empleados_registros.estado_sesion) LIKE '%".\strtolower($search["value"])."%' OR".
                " LOWER(control_empleados_registros.fecha_llegada) LIKE '%".\strtolower($search["value"])."%' OR".
                " LOWER(control_empleados_registros.fecha_salida) LIKE '%".\strtolower($search["value"])."%'".
                ")");
        }
        $parcialRegistros = $empleados->count();
        $empleados = $empleados->skip($start)->take($length);
        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($empleados->get() as $em) {
                $lugar = null;
                if(Auth::user()->bodegas == 'si'){
                    if($em->bodega_id){
                        $bodega = Bodega::find($em->bodega_id);
                        $lugar = $bodega->nombre;
                    }
                    if($em->almacen_id){
                        $almacen = Almacen::find($em->almacen_id);
                        $lugar = $almacen->nombre;
                    }
                }
                $myArray[]=(object) array('id' => $em->id,
                                          'nombre' => $em->nombre,
                                          'cedula' => $em->cedula,
                                          'estado_empleado' => $em->estado_empleado,
                                          'estado_sesion' => $em->estado_sesion,
                                          'fecha_llegada' => $em->fecha_llegada,
                                          'fecha_salida' => $em->fecha_salida,
                                          'lugar' => $lugar);
            }
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $empleados->toSql(),              
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' =>$myArray,
            //'info'=>$empleados->get()
            ];
        
        return response()->json($data);
    }

	//Cierra Sesion - inicio
	public function postCierraIniciaSesion(Request $request){
		if(Auth::user()->permitirFuncion("Cerrar sesion","Control de empleados","inicio")){
			if($request->has("id") && $request->has('estado_check') && $request->has('fecha_inicio_sesion')) {
				$id = $request->input("id");
				$fecha_llegada =  $request->input("fecha_inicio_sesion");
				$ultimo_registro = ControlEmpleadosRegistros::where('control_empleado_id','=',$id)->where("fecha_llegada","=",$fecha_llegada)->orderBy('fecha_llegada','DESC')->first();
				if($ultimo_registro){
					if($ultimo_registro['estado_sesion'] == 'off'){
						Session::flash("mensaje", "La sesión ya fue cerrada.");
						return ["success" => true, "mensaje" => "La sesión ya fue cerrada."];
					}else{
						$ultimo_registro['estado_sesion'] = 'off';
						$ultimo_registro['fecha_salida'] = date("Y-m-d H:i:s");
						$ultimo_registro->save();
						Session::flash("mensaje", "Se ha cerrado la sesión del usuario con éxito");
						return ["success" => true, "mensaje" => "Se ha cerrado la sesión del usuario con éxito. "];
					}
				}else{
					Session::flash("mensaje", "No es permitido iniciar sesiones a empleados");
					return response(["Error", "No es permitido iniciar sesiones a empleados"],422);
				}
			}else{
				return response(["Error"=>["La información enviada es incorrecta. Por favor intente de nuevo"]],422);
			}
		}else{
			return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
		}
	}

    //Cierra ultima sesion activa - inicio
	public function postCierraSesionEmpleado(Request $request){
		if(Auth::user()->permitirFuncion("Cerrar sesion","Control de empleados","inicio") && Auth::user()->permitirFuncion("Iniciar sesion","Control de empleados","inicio")){		
			if($request->has("barcode")) {
				$barcode = $request->input("barcode");
				$id_empleado = ControlEmpleados::permitidos()->where('codigo_barras','=',$barcode)->first();
				if($id_empleado){
					$ultimo_registro = ControlEmpleadosRegistros::where('control_empleado_id','=',$id_empleado->id)->where('estado_sesion','=','on')->orderBy('fecha_llegada','ASC')->get();
						if( count($ultimo_registro) > 0){
							$hora_servidor = date("Y-m-d H:i:s");
							foreach ($ultimo_registro as $registro) {
								$registro['estado_sesion'] = 'off';
								$registro['fecha_salida'] = date("Y-m-d H:i:s");
								$registro->save();
							}
						 	return ["success" => true, "mensaje" => "Se ha cerrado la sesión del usuario ".$id_empleado->nombre." con éxito. "];
						}else{
							$ControlEmpleadosRegistros = new ControlEmpleadosRegistros;
							$ControlEmpleadosRegistros['control_empleado_id'] = $id_empleado->id;
							$ControlEmpleadosRegistros['estado_sesion'] = 'on';
							$ControlEmpleadosRegistros['fecha_llegada'] = date("Y-m-d H:i:s");
							if(Auth::user()->bodegas == 'si'){
							    if(Auth::user()->admin_bodegas == 'si'){
							        $bodega = Bodega::permitidos()->first();
							        $ControlEmpleadosRegistros['bodega_id'] = $bodega->id;
                                }else{
							        $almacen = Auth::user()->almacenActual();
							        $ControlEmpleadosRegistros['almacen_id'] = $almacen->id;
                                }
                            }
							$ControlEmpleadosRegistros->save();
							return ["success" => true, "mensaje" => "Se ha iniciado la sesión del usuario ".$id_empleado->nombre." con éxito."];
						}
				}else{
					return response(["Error"=>["El usuario no se encuentra registrado"]],422);
				}
			}else{
				return response(["Error"=>["La información enviada es incorrecta. Por favor intente de nuevo"]],422);
			}
		}else{
			return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
		}
	}
	

}
