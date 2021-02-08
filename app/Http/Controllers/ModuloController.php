<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Almacen;
use App\Models\Funcion;
use App\Models\Modulo;
use App\Models\PlanFuncion;
use App\Models\PlanUsuario;
use App\Models\Reporte;
use App\Models\ReporteHabilitado;
use App\Models\Tarea;
use App\Models\UsuarioFuncion;
use App\Models\UsuarioFuncionTarea;
use App\Models\UsuarioModulo;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class ModuloController extends Controller {

	public function __construct()
	{
		$this->middleware("auth");
		$this->middleware("modConfiguracion");
		$this->middleware("modModulo");
		$this->middleware("terminosCondiciones");
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
			if(Auth::user()->perfil->nombre != "superadministrador")
				$modulos = Auth::user()->modulosActivos();
			else
				$modulos = Modulo::orderBy("seccion","ASC")->orderBy("nombre","ASC")->get();
			/*for ($i = 0; $i < count($modulos); $i++) {
				echo "Modulo: " . $modulos[$i]->nombre . " - " . $modulos[$i]->seccion . "<br>";
			}*/
            $reportesPermitidos = Auth::user()->permitirReportes(Auth::user()->userAdminId(), Auth::user()->plan()->id, 'activo');
			return view("modulo/index")->with("modulos",$modulos)->with('reportesPermitidos',$reportesPermitidos);

		return redirect("/");

	}

	public function postAdministrar(Request $request){
		$id = $request->input("id");
		if(Auth::user()->permitirFuncion("administrar","modulos","configuracion")){
			$modulo = Modulo::find($id);
			if($modulo && $modulo->exists){
			    if(Auth::user()->bodegas == 'no' || (Auth::user()->bodegas == 'si' && $modulo->asignable_ab == 'si')) {
                    $ids = [];
                    foreach (Auth::user()->modulosActivos() as $m) {
                        $ids[] = $m->id;
                    }

                    if (in_array($modulo->id, $ids)) {
                        return view("modulo.administrar")->with("modulo", $modulo);
                    } else {
                        if (Auth::user()->perfil->nombre == "superadministrador")
                            return view("modulo.administrar")->with("modulo", $modulo);
                    }
                }
			}
		}
		return response("Unauthorized.",401);
	}

	public function getEditarPermisos($id_modulo,$id_usuario){
		if(Auth::user()->permitirFuncion("administrar","modulos","configuracion")) {
			$usuario = User::permitidos()->where("usuarios.id",$id_usuario)->first();
			if($usuario && $usuario->exists) {
				$modulo = Modulo::find($id_modulo);
				if($modulo->nombre == "modulo")
					return redirect('/');
				if($modulo && $modulo->exists){
                    if(Auth::user()->bodegas == 'no' || (Auth::user()->bodegas == 'si' && $modulo->asignable_ab == 'si')) {
                        $ids = [];
                        foreach (Auth::user()->modulosActivos() as $m) {
                            $ids[] = $m->id;
                        }

                        if (in_array($modulo->id, $ids) || Auth::user()->perfil->nombre == "superadministrador") {
                            if (UsuarioModulo::where("modulo_id", $id_modulo)->where("usuario_id", $id_usuario)->first()) {
                                return view("modulo.editar_permisos")->with("usuario", $usuario)->with("modulo", $modulo);
                            }
                        }
                    }
				}

			}
		}
		return redirect("/");
	}

	public function postEditarPermisos(Request $request){
		if(Auth::user()->permitirFuncion("administrar","modulos","configuracion")) {
			//dd($request->all());

			$modulo = Modulo::find($request->get("modulo"));
			if($modulo->nombre == "modulo")
				return response(['error' => ['La información enviada es incorrecta, recargue la página.']], 422);
            if(Auth::user()->bodegas == 'no' || (Auth::user()->bodegas == 'si' && $modulo->asignable_ab == 'si')) {
                $usuario_relacion = User::find($request->get("usuario"));

                $fechaCaducidad = $request->input('caducidad_usuario');
                $maxFechaCaducidad = $fechaCaducidad;
                if (Auth::user()->perfil->nombre == "administrador") {
                    $maxFechaCaducidad = $modulo->fechaCaducidad(Auth::user()->id);
                }

                if (strtotime($fechaCaducidad) > strtotime($maxFechaCaducidad)) {
                    return response(['error' => ['La fecha de caducidad del modulo no debe ser mayor a ' . $maxFechaCaducidad . '.']], 422);
                }

                if (!$modulo) {
                    return response(['error' => ['La información enviada es incorrecta, recargue la página.']], 422);
                }
                //dd($modulo->nombre."_".$modulo->seccion);
                if (Auth::user()->perfil->nombre != "superadministrador" && !Auth::user()->permitirModulo($modulo->nombre, $modulo->seccion)) {
                    return response(['error' => ['usted no tiene permisos para realizar esta tarea.']], 401);
                }

                if (!$usuario_relacion) {
                    return response(['error' => ['La información enviada es incorrecta, recargue la página.']], 422);
                }

                //se consulta el almacen del usuario por si es requerido en la condicion
                $almacen = null;
                if (Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'no') {
                    $almacen = Almacen::where('administrador', Auth::user()->id)->first();
                }

                //es un superadministrador o es el creador del usuario de la relaición
                //o es un usuario del almacen
                if (Auth::user()->perfil->nombre != "superadministrador" && $usuario_relacion->usuario_creador_id != Auth::user()->id
                    && !(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'no' && $almacen && $usuario_relacion->almacen_id == $almacen->id)
                ) {
                    return response(['error' => ['usted no tiene permisos para realizar esta tarea.']], 401);
                }
                //se valida si las funciones que llegan son permitidas por el usuario que inicio la sesion
                //y se agregarn todas las funciones a un array
                $array_funciones = [];
                $array_caducidad = [];

                foreach ($request->all() as $key => $value) {
                    $datos = explode('_', $key);
                    if (count($datos) > 1 && $datos[0] == "funcion") {
                        $funcion = Funcion::find($datos[1]);
                        if ($funcion) {
                            if (Auth::user()->perfil->nombre != "superadministrador" && !Auth::user()->permitirFuncion($funcion->nombre, $modulo->nombre, $modulo->seccion)) {
                                return response(['error' => ['usted no tiene permisos para realizar esta tarea.']], 401);
                            }

                            if ($request->has("hasta_" . $datos[1]))
                                $caducidad = $request->input("hasta_" . $datos[1]);
                            else
                                return response(['error' => ['Todas las funciones deben tener una fecha de caducidad.']], 422);

                            if (strtotime($caducidad) > strtotime($fechaCaducidad)) {
                                return response(['error' => ['La fecha de caducidad de una función no debe ser mayor a la fecha de caducidad del modulo para el usuario.']], 422);
                            }

                            $array_funciones[] = $datos[1];
                            $array_caducidad[] = [$caducidad, $datos[1]];
                        } else {
                            return response(['error' => ['La información enviada es incorrecta, recargue la página.']], 422);
                        }
                    }
                }

                //se valida si las tareas que llegan son permitidas por el usuario que inicio la sesion
                //y se agregan todas las tareas a un array y si la tarea se relaciona con una de las  funciones recibidas
                $array_tareas = [];
                foreach ($request->all() as $key => $value) {
                    $datos = explode('_', $key);
                    if (count($datos) > 2 && $datos[0] == "tarea") {
                        $tarea = Tarea::find($datos[1]);
                        $funcion = Funcion::find($datos[2]);
                        //la información de la tarea no se relaciona con la información de las funciones
                        if (!in_array($datos[2], $array_funciones)) {
                            return response(['error' => ['La información enviada es incorrecta, recargue la página.']], 422);
                        }
                        if ($tarea) {
                            if (Auth::user()->perfil->nombre != "superadministrador" && !Auth::user()->permitirTarea($tarea->nombre, $funcion->nombre, $modulo->nombre, $modulo->seccion)) {
                                return response(['error' => ['usted no tiene permisos para realizar esta tarea.']], 401);
                            }
                            $array_tareas[] = [$datos[1], $datos[2]];
                        } else {
                            return response(['error' => ['La información enviada es incorrecta, recargue la página.']], 422);
                        }
                    }
                }

                DB::beginTransaction();
                $funcionesModulo = Funcion::where("modulo_id", $modulo->id)->get();
                $usu_mod = UsuarioModulo::where("usuario_id", $usuario_relacion->id)->where("modulo_id", $modulo->id)->first();
                $usu_mod->hasta = $fechaCaducidad;
                $usu_mod->save();
                foreach ($funcionesModulo as $f) {
                    $usuario_funcion = UsuarioFuncion::where("funcion_id", $f->id)->where("usuario_id", $usuario_relacion->id)->get();
                    foreach ($usuario_funcion as $uf) {
                        $uf->delete();
                    }
                }

                if (count($array_funciones)) {
                    foreach ($array_funciones as $data) {
                        $usuario_funcion = new UsuarioFuncion();
                        $usuario_funcion->usuario_id = $usuario_relacion->id;
                        $usuario_funcion->funcion_id = $data;
                        $usuario_funcion->estado = "activo";
                        foreach ($array_caducidad as $data_caducidad) {
                            if ($data == $data_caducidad[1]) {
                                $usuario_funcion->hasta = $data_caducidad[0];
                                break;
                            }
                        }
                        $usuario_funcion->save();

                        foreach ($array_tareas as $data_tarea) {
                            if ($data == $data_tarea[1]) {
                                $uft = new UsuarioFuncionTarea();
                                $uft->usuario_funcion_id = $usuario_funcion->id;
                                $uft->tarea_id = $data_tarea[0];
                                $uft->estado = "activo";
                                $uft->save();
                            }
                        }
                    }
                }
                DB::commit();
                Session::flash("mensaje", "Los cambios han sido almacenados con éxito.");
                return ["success" => true];
            }
		}
	}

	public function getAgregarPermisos($id_modulo){
		if(Auth::user()->permitirFuncion("administrar","modulos","configuracion")) {
				$modulo = Modulo::find($id_modulo);
				if($modulo && $modulo->exists){
					if($modulo->nombre == "modulo")
						return redirect('/');

                    if(Auth::user()->bodegas == 'no' || (Auth::user()->bodegas == 'si' && $modulo->asignable_ab == 'si')) {
                        $ids = [];
                        foreach (Auth::user()->modulosActivos() as $m) {
                            $ids[] = $m->id;
                        }
                        if (in_array($modulo->id, $ids) || Auth::user()->perfil->nombre == "superadministrador") {
                            return view("modulo.agregar_permisos")->with("modulo", $modulo);
                        }
                    }
				}
		}
		return redirect("/");
	}

	public function postAgregarPermisos(Request $request){
		if(Auth::user()->permitirFuncion("administrar","modulos","configuracion")) {

			$modulo = Modulo::find($request->get("modulo"));
			if($modulo->nombre == "modulo")
				return response(['error' => ['La información enviada es incorrecta, recargue la página.']], 422);
            if(Auth::user()->bodegas == 'no' || (Auth::user()->bodegas == 'si' && $modulo->asignable_ab == 'si')) {
                $fechaCaducidad = $request->input('caducidad_usuario');
                $maxFechaCaducidad = $fechaCaducidad;
                if (Auth::user()->perfil->nombre == "administrador") {
                    $maxFechaCaducidad = $modulo->fechaCaducidad(Auth::user()->id);
                }

                if (strtotime($fechaCaducidad) > strtotime($maxFechaCaducidad)) {
                    return response(['error' => ['La fecha de caducidad del modulo no debe ser mayor a ' . $maxFechaCaducidad . '.']], 422);
                }

                if (!$modulo) {
                    return response(['error' => ['La información enviada es incorrecta, recargue la página.']], 422);
                }
                //dd($modulo->nombre."_".$modulo->seccion);
                if (Auth::user()->perfil->nombre != "superadministrador" && !Auth::user()->permitirModulo($modulo->nombre, $modulo->seccion)) {
                    return response(['error' => ['usted no tiene permisos para realizar esta tarea.']], 401);
                }


                //se valida si las funciones que llegan son permitidas por el usuario que inicio la sesion
                //y se agregarn todas las funciones a un array
                $array_funciones = [];
                $array_caducidad = [];
                $array_usuarios = [];

                //se valida la seleccion de usuarios y que al mismo tiempo sean usuarios permitidos
                foreach ($request->all() as $key => $value) {
                    $datos = explode('_', $key);
                    if (count($datos) > 1 && $datos[0] == "usuario") {
                        $usuario = User::find($datos[1]);
                        if ($usuario) {
                            if (Auth::user()->perfil->nombre != "superadministrador" && !User::permitidos()->where("usuario_creador_id", Auth::user()->id)) {
                                return response(['error' => ['usted no tiene permisos para realizar esta tarea.']], 401);
                            }
                            $array_usuarios[] = $usuario->id;
                        } else {
                            return response(['error' => ['La información enviada es incorrecta, recargue la página.']], 422);
                        }
                    }
                }

                if (!count($array_usuarios) > 0) {
                    return response(['error' => ['Debe seleccionar por lo menos un usuario para asignar los permisos.']], 422);
                }

                foreach ($request->all() as $key => $value) {
                    $datos = explode('_', $key);
                    if (count($datos) > 1 && $datos[0] == "funcion") {
                        $funcion = Funcion::find($datos[1]);
                        if ($funcion) {
                            if (Auth::user()->perfil->nombre != "superadministrador" && !Auth::user()->permitirFuncion($funcion->nombre, $modulo->nombre, $modulo->seccion)) {
                                return response(['error' => ['usted no tiene permisos para realizar esta tarea.']], 401);
                            }

                            if ($request->has("hasta_" . $datos[1]))
                                $caducidad = $request->input("hasta_" . $datos[1]);
                            else
                                return response(['error' => ['Todas las funciones deben tener una fecha de caducidad.']], 422);

                            if (strtotime($caducidad) > strtotime($fechaCaducidad)) {
                                return response(['error' => ['La fecha de caducidad de una función no debe ser mayor a la fecha de caducidad del modulo para el usuario.']], 422);
                            }

                            $array_funciones[] = $datos[1];
                            $array_caducidad[] = [$caducidad, $datos[1]];
                        } else {
                            return response(['error' => ['La información enviada es incorrecta, recargue la página.']], 422);
                        }
                    }
                }

                //se valida si las tareas que llegan son permitidas por el usuario que inicio la sesion
                //y se agregan todas las tareas a un array y si la tarea se relaciona con una de las  funciones recibidas
                $array_tareas = [];
                foreach ($request->all() as $key => $value) {
                    $datos = explode('_', $key);
                    if (count($datos) > 2 && $datos[0] == "tarea") {
                        $tarea = Tarea::find($datos[1]);
                        $funcion = Funcion::find($datos[2]);
                        //la información de la tarea no se relaciona con la información de las funciones
                        if (!in_array($datos[2], $array_funciones)) {
                            return response(['error' => ['La información enviada es incorrecta, recargue la página.']], 422);
                        }
                        if ($tarea) {
                            if (Auth::user()->perfil->nombre != "superadministrador" && !Auth::user()->permitirTarea($tarea->nombre, $funcion->nombre, $modulo->nombre, $modulo->seccion)) {
                                return response(['error' => ['usted no tiene permisos para realizar esta tarea.']], 401);
                            }
                            $array_tareas[] = [$datos[1], $datos[2]];
                        } else {
                            return response(['error' => ['La información enviada es incorrecta, recargue la página.']], 422);
                        }
                    }
                }

                DB::beginTransaction();
                $funcionesModulo = Funcion::where("modulo_id", $modulo->id)->get();
                foreach ($array_usuarios as $id_user) {
                    $usuario_relacion = User::find($id_user);
                    $usu_mod = UsuarioModulo::where("usuario_id", $usuario_relacion->id)->where("modulo_id", $modulo->id)->first();
                    if ($usu_mod) {
                        return response(['error' => ['El usuario ' . $usuario_relacion->nombres . ' ' . $usuario_relacion->apellidos . ' ya se encuentra relacionado con este modulo, para editar los permisos del usuario diríjase al menú modulos.']], 422);
                    }

                    $usu_mod = new UsuarioModulo();
                    $usu_mod->usuario_id = $id_user;
                    $usu_mod->modulo_id = $modulo->id;
                    $usu_mod->estado = "activo";
                    $usu_mod->hasta = $fechaCaducidad;
                    $usu_mod->save();
                    foreach ($funcionesModulo as $f) {
                        $usuario_funcion = UsuarioFuncion::where("funcion_id", $f->id)->where("usuario_id", $usuario_relacion->id)->get();
                        foreach ($usuario_funcion as $uf) {
                            $uf->delete();
                        }
                    }

                    if (count($array_funciones)) {
                        foreach ($array_funciones as $data) {
                            $usuario_funcion = new UsuarioFuncion();
                            $usuario_funcion->usuario_id = $usuario_relacion->id;
                            $usuario_funcion->funcion_id = $data;
                            $usuario_funcion->estado = "activo";
                            foreach ($array_caducidad as $data_caducidad) {
                                if ($data == $data_caducidad[1]) {
                                    $usuario_funcion->hasta = $data_caducidad[0];
                                    break;
                                }
                            }
                            $usuario_funcion->save();

                            foreach ($array_tareas as $data_tarea) {
                                if ($data == $data_tarea[1]) {
                                    $uft = new UsuarioFuncionTarea();
                                    $uft->usuario_funcion_id = $usuario_funcion->id;
                                    $uft->tarea_id = $data_tarea[0];
                                    $uft->estado = "activo";
                                    $uft->save();
                                }
                            }
                        }
                    }
                }
                DB::commit();
                Session::flash("mensaje", "Los permisos han sido asignados y almacenados con éxito.");
                return ["success" => true];
            }
		}
	}

	public function postDestroyPermisos($id_modulo,$id_usuario){
		if(Auth::user()->permitirFuncion("administrar","modulos","configuracion")){
			$this->deleteCollection(UsuarioModulo::where("usuario_id",$id_usuario)->where("modulo_id",$id_modulo)->get());
			$obj = UsuarioFuncion::select("usuarios_funciones.*")->join("funciones","usuarios_funciones.funcion_id","=","funciones.id")
				->join("modulos","funciones.modulo_id","=","modulos.id")
				->where("usuarios_funciones.usuario_id",$id_usuario)
				->where("modulos.id",$id_modulo)->get();
			$this->deleteCollection($obj);
			Session::flash("mensaje","La relación entre el modulo y el usuario ha sido eliminada con éxito");
			return ["success"=>true];
		}
	}

	function deleteCollection($obj){
		foreach ($obj as $o){
			$o->delete();
		}
	}

	public function postPermisoReportes(Request $request)
    {
        $usuarios_permitidos = User::permitidos()->get();
        foreach ($usuarios_permitidos as $u){
            $u->permiso_reportes = 'no';
            $u->save();
        }

        if($request->has('permiso_reportes') && is_array($request->input('permiso_reportes'))){
            DB::beginTransaction();
            foreach ($request->input('permiso_reportes') as $id){
                foreach($usuarios_permitidos as $u){
                    if($u->id == $id) {
                        $u->permiso_reportes = 'si';
                        $u->save();
                    }
                }
            }
            DB::commit();
        }
        Session::flash("mensaje","Los permisos para el módulo de reportes han sido actualizados con éxito");
        return ['success'=>true];
    }


	public function postReportesHabilitados(Request $request)
    {
        ReporteHabilitado::where('usuario_id',Auth::user()->userAdminId())->delete();

        if($request->has('reportes_habilitados') && is_array($request->input('reportes_habilitados'))){
            DB::beginTransaction();
            foreach ($request->input('reportes_habilitados') as $r){
                $reporte = Reporte::find($r);
                $reporte_habilitado = new ReporteHabilitado();
                $reporte_habilitado->usuario_id = Auth::user()->userAdminId();
                $reporte_habilitado->reporte_id = $reporte->id;
                $reporte_habilitado->save();
            }
            DB::commit();
        }
        Session::flash("mensaje","Los permisos para el módulo de reportes han sido actualizados con éxito");
        return ['success'=>true];
    }

    function postActualizarCaducidad(Request $request){
        if((Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
        ) {
            if ($request->has('usuarios')) {
                $usuarios = User::permitidos()->whereIn('id', $request->input('usuarios'))->get();
                if (!$usuarios) {
                    return response(['error' => ['La información enviada es incorrecta']], 422);
                }

                $fecha_time_caducidad = strtotime($request->input('fecha_caducidad'));
                $fecha_time_hoy = strtotime(date('Y-m-d'));
                $f_c_p = Auth::user()->caducidadPlan();
                $fecha_time_caducidad_plan = strtotime($f_c_p);

                if($fecha_time_hoy > $fecha_time_caducidad)
                    return response(['error' => ['Seleccione una fecha mayor o igual a la actual']], 422);

                if($fecha_time_caducidad_plan < $fecha_time_caducidad)
                    return response(['error' => ['Seleccione una fecha menor o igual a '.date('Y-m-d',strtotime($f_c_p))]], 422);

                DB::beginTransaction();
                foreach ($usuarios as $u) {
                    UsuarioModulo::where('usuario_id', $u->id)
                        ->where('estado', 'activo')
                        ->update(['hasta' => $request->input('fecha_caducidad')]);

                    UsuarioFuncion::where('usuario_id', $u->id)
                        ->where('estado', 'activo')
                        ->update(['hasta' => $request->input('fecha_caducidad')]);
                }
                DB::commit();
                Session::flash('mensaje','La fecha de caducidad ha sido actualizada');
                return ['success'=>true];
            } else {
                return response(['error' => ['Seleccione por lo menos un usuario']], 422);
            }
        }else{
            return response(['error' => ['Unauthorized.']], 401);
        }
    }
}
