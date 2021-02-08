<?php namespace App\Http\Controllers;

use App\General;
use App\Models\Cliente;

use Illuminate\Http\Request;
use App\Http\Requests\SmsCreateRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Sms;
use App\User;


class SmsController extends Controller {

	public function getIndex(){
        $sms = Sms::permitidos()->select('*')
            ->orderBy('updated_at','DESC')
            ->paginate(env('PAGINATE'));
        return view('sms.index');
    }

    public function getListSms(Request $request){

        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = $columna[$sortColumnIndex]['data'];   
        // if($orderBy == null){$orderBy= 'nombre';}
        $myArray=[];            
        $sms = Sms::permitidos()->select('*')->orderBy('updated_at','DESC');
        $totalRegistros = $sms->count();  
        if($search['value'] != null){
            $sms = $sms->whereRaw(
                " ( LOWER(titulo) LIKE '%".\strtolower($search["value"])."%' OR".
                " LOWER(mensaje) LIKE '%".\strtolower($search["value"])."%' OR".
                " LOWER(f_h_programacion) LIKE '%".\strtolower($search["value"])."%' OR".
                " LOWER(estado) LIKE '%".\strtolower($search["value"])."%' ".
                ")");
        }


        $parcialRegistros = $sms->count();
        $sms = $sms->skip($start)->take($length);
        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($sms->get() as $sm) {
                 $horas = General::calcula_tiempo(date('Y-m-d H:m:s'),$sm->f_h_programacion);
                    $aux="teléfono";
                    $cantidad_telefonos = explode('-',$sm->telefonos);
                    if (count($cantidad_telefonos)> 1)
                        $aux = "teléfonos";

                $myArray[]=(object) array('id' => $sm->id,
                                          'titulo'=>$sm->titulo,
                                          'mensaje'=>$sm->mensaje,
                                          'f_h_programacion'=>$sm->f_h_programacion,
                                          'estado'=>$sm->estado,
                                          'telefonos'=>$sm->telefonos,
                                          'total_telefonos'=> count($cantidad_telefonos)." ". $aux,
                                          'url_editar'=> url('/sms/edit/'.$sm->id));
            }
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $sms->toSql(),              
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' =>$myArray,
            'info'=>$sms->get()
            ];
        
        return response()->json($data);
    }


    public function getCreate(){
        if(Auth::user()->permitirFuncion("Crear","sms","inicio")) {
            $plan = Auth::user()->plan();
            $cantidad_enviada = Sms::countSmsSendOk();
            $cantidad_permitida = $plan->n_promociones_sms;
            if(!($cantidad_permitida == 0 || ($cantidad_enviada < $cantidad_permitida)))return redirect("/");
            $usuarios = null;
            $usuarios = Cliente::permitidos()->select('*')->get();
            return view('sms.create', compact('usuarios'));
        }else{
             return response(["Error","Usted no tiene permisos para realizar esta tarea."],401);
        }
    }
    public function postStore(SmsCreateRequest $request){
        if(Auth::user()->permitirFuncion("Crear","sms","inicio")) {

            $plan = Auth::user()->plan();
            $cantidad_enviada = Sms::countSmsSendOk();
            $cantidad_permitida = $plan->n_promociones_sms;
            if(!($cantidad_permitida == 0 || ($cantidad_enviada < $cantidad_permitida)))
                return response(["error"=>["No es posible crear más mensajes de texto, ha registrado la cantidad máxima permitida en su plan."]],422);
            $sms = new Sms();
            DB::beginTransaction();
            $sms->titulo = General::limpiarString($request->get('titulo'));
            $sms->mensaje = General::limpiarString($request->get('mensaje'));
            $sms->f_h_programacion = $request->get('f_h_programacion');
            $sms->telefonos = $request->get('telefonos');
            $admin = User::find(Auth::user()->userAdminId());

            $data_plan = $admin->planes()->select("planes_usuarios.id")->where("planes_usuarios.estado","activo")->first();
            if(!$data_plan)return response(["error"=>["Ocurrio un error al programar el mensaje."]],422);

            $sms->plan_usuario_id = $data_plan->id;

            $sms->usuario_creator_id = Auth::user()->id;
            if (Auth::user()->perfil->nombre == "usuario") {
                $sms->usuario_id = Auth::user()->usuario_creador_id;
            } else {
                $sms->usuario_id = Auth::user()->id;
            }

            $sms->save();
            $data = [
                "success" => true,
                "msm_id" => $sms->id,
                "mensaje" => "El mensaje ha sido registrado con éxito."];
            DB::commit();
            return $data;

        }else{
            return response(["Error","Usted no tiene permisos para realizar esta tarea."],401);
        }
    }
    public function getEdit($sms_id = ''){
        if(Auth::user()->permitirFuncion("Editar","sms","inicio")) {
            $sms = null;
            $usuarios = null;
            $usuarios = Cliente::permitidos()->select('*')->get();
            if ($sms_id != ''){
                $sms = Sms::permitidos()->select('*')->where('id',$sms_id)->first();
            }
            $hora_actual = date('Y-m-d H:m:s');
            //$hora_actual ="2016-09-29 11:55:00";
            if ($sms->estado == 'pendiente'){
                if (General::calcula_tiempo($hora_actual,$sms->f_h_programacion) >= 1 || General::calcula_tiempo($hora_actual,$sms->f_h_programacion) < 0){
                    return view('sms.edit',compact('sms','usuarios'));
                }else{
                    $mensaje = "No se puede actualizar el mensaje en este momento, está proximo a ser enviado. Intentelo mas tarde!";
                    Session::flash("mensaje_validacion_caja", $mensaje);
                    return $this->getIndex();
                }
            }else if($sms->estado == 'enviado'){
                return view('sms.edit',compact('sms','usuarios'));
            }

        }else{
            return response(["Error","Usted no tiene permisos para realizar esta tarea."],401);
        }
    }
    public function postUpdate(SmsCreateRequest $request,$sms_id){

        if(Auth::user()->permitirFuncion("Editar","sms","inicio")) {
            $sms = Sms::permitidos()->select('*')->where('id',$sms_id)->first();
            DB::beginTransaction();
            $sms->titulo = General::limpiarString($request->get('titulo'));
            $sms->mensaje = General::limpiarString($request->get('mensaje'));
            $sms->f_h_programacion = $request->get('f_h_programacion');
            $sms->telefonos = $request->get('telefonos');

            $sms->usuario_creator_id = Auth::user()->id;
            if (Auth::user()->perfil->nombre == "usuario") {
                $sms->usuario_id = Auth::user()->usuario_creador_id;
            } else {
                $sms->usuario_id = Auth::user()->id;
            }

            if ($request->estado == 'on')
                $estado = "enviado";
            else
                $estado = "pendiente";

            $sms->estado = $estado;

            $sms->save();
            $data = [
                "success" => true,
                "msm_id" => $sms->id,
                "mensaje" => "El mensaje ha sido actualizado con éxito."];
            DB::commit();
            return $data;

        }else{
            return response(["Error","Usted no tiene permisos para realizar esta tarea."],401);
        }
    }
    public function getViewDuplicarSms($sms_id){
        $sms = Sms::permitidos()->find($sms_id);
        $usuarios = Cliente::permitidos()->select('*')->get();
        return view('sms.duplicar.form', compact('sms','usuarios'));
    }
    public function postDestroy(Request $request){
        if(Auth::user()->permitirFuncion("Eliminar","sms","inicio")) {
            $sms = Sms::permitidos()->select('*')->where('id',$request->get('id'))->first();
            if ($sms && $sms->exists){
                $fecha_programacion = $sms->f_h_programacion;
                $horas = General::calcula_tiempo(date('Y-m-d H:m:s'),$fecha_programacion);
                if ($horas >= 24){
                    DB::beginTransaction();
                    $sms->delete();
                    DB::commit();
                    Session::flash("mensaje", "El mensaje ha sido eliminado con éxito");
                    return ["success" => true];
                }else{
                    return ["success" => false];
                }
            }else {
                return ["error" => "La información es incorrecta"];
            }
        }
        return response("Unauthorized",401);
    }

}
