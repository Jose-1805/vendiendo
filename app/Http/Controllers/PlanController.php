<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Funcion;
use App\Models\Modulo;
use App\Models\Plan;
use App\Models\PlanFuncion;
use App\Models\Proveedor;
use App\Models\Reporte;
use App\Models\Tarea;
use Illuminate\Http\Request;
use App\Http\Requests\RequestNuevoProveedor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\RequestPlan;

class PlanController extends Controller {


    public function __construct(){
        $this->middleware("auth");
        $this->middleware("modPlan");
        $this->middleware("modConfiguracion");
        $this->middleware("terminosCondiciones");
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex(Request $request)
	{
        $planes = Plan::paginate(env("PAGINATE"));

        return view('plan.index')->with("planes",$planes);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function getCreate(Request $request)
	{
        if(Auth::user()->permitirFuncion("Crear","planes","configuracion")) {
            return view('plan.action');
        }

        return redirect("/");
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function postStore(RequestPlan $request)
	{
        if(Auth::user()->permitirFuncion("Crear","planes","configuracion")) {
            if($request->has("modulos") && is_array($request->input("modulos"))) {
                DB::beginTransaction();
                $plan = new Plan();
                $plan->fill($request->all());
                $plan->save();

                $guardarRelaciones = $this->guardarRelaciones($request,$plan);

                if($guardarRelaciones["respuesta"]) {
                    DB::commit();
                    Session::flash("mensaje", "El plan ha sido creado con éxito");
                    return ["success" => true];
                }else{
                    return $guardarRelaciones["Error"];
                }
            }else{
                return response(["Error"=>["Seleccione por lo menos un módulo"]],422);
            }

        }

        return response("Unauthorized",401);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getEdit($id)
	{
        if(Auth::user()->permitirFuncion("Editar","planes","configuracion")) {
            $plan = Plan::find($id);
            if ($plan && $plan->exists) {
                return view("plan.action")->with("plan", $plan)->with("accion","Editar");
            }
        }

        return redirect("/");
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function postUpdate(RequestPlan $request)
    {
        if(Auth::user()->permitirFuncion("Editar","planes","configuracion")) {
            if($request->has("modulos") && is_array($request->input("modulos"))) {
                if($request->has("plan")){
                    $plan = Plan::find($request->input("plan"));
                    if(!$plan)return response(["Error"=>["La información enviada es incorrecta"]],422);
                }else{
                    return response(["Error"=>["La información enviada es incorrecta"]],422);
                }
                DB::beginTransaction();
                $plan->fill($request->all());
                if(!$request->has("puntos"))
                    $plan->puntos = "no";
                if(!$request->has("cliente_predeterminado"))
                    $plan->cliente_predeterminado = "no";
                if(!$request->has("notificaciones"))
                    $plan->notificaciones = "no";
                if(!$request->has("objetivos_ventas"))
                    $plan->objetivos_ventas = "no";
                if(!$request->has("factura_abierta"))
                    $plan->factura_abierta = "no";
                if(!$request->has("importacion_productos"))
                    $plan->importacion_productos = "no";
                if(!$request->has("validacion_stock"))
                    $plan->validacion_stock = "no";
                $plan->save();
                $plan->eliminarRelaciones();
                $guardarRelaciones = $this->guardarRelaciones($request,$plan);

                if($guardarRelaciones["respuesta"]) {
                    DB::commit();
                    Session::flash("mensaje", "El plan ha sido editado con éxito");
                    return ["success" => true];
                }else{
                   return $guardarRelaciones["Error"];
                }

            }else{
                return response(["Error"=>["Seleccione por lo menos un módulo"]],422);
            }
        }
        return response("Unauthorized",401);
    }

    protected function guardarRelaciones(RequestPlan $request,$plan){
        for ($i = 0; $i < count($request->input("modulos"));$i++){
            $mod = Modulo::find($request->input("modulos")[$i]);
            if($mod){//se relacionan los módulos con el plan creado
                $plan->modulos()->save($mod);
                if(count($mod->funciones) && $request->has("funciones_".$mod->id) && is_array($request->input("funciones_".$mod->id))){
                    //si el módulo tiene funciones en la DB y se a seleccionado por lo menos 1,se relaciona la funcion con el plan
                    for ($f = 0; $f < count($request->input("funciones_".$mod->id));$f++){
                        $fun = Funcion::where("modulo_id",$mod->id)->where("id",$request->input("funciones_".$mod->id)[$f])->first();
                        if($fun){
                            $plan->funciones()->save($fun);
                            //si la función tiene tareas se relacionan las tareas con el plan
                            if(count($fun->tareas) && $request->has("tareas_".$fun->id) && is_array($request->input("tareas_".$fun->id))){
                                for ($t = 0; $t < count($request->input("tareas_".$fun->id));$t++) {
                                    $tar = $fun->tareas()->where("tareas.id",$request->input("tareas_".$fun->id)[$t])->first();
                                    if($tar){
                                        $planFuncion = PlanFuncion::where("funcion_id",$fun->id)->where("plan_id",$plan->id)->first();
                                        if($planFuncion){
                                            $planFuncion->tareas()->save($tar);
                                        }
                                    }else{
                                        return ["respuesta"=>false,"Error"=>response(["Error"=>["La información enviada es incorrecta"]],422)];
                                    }
                                }
                            }
                        }else{
                            return ["respuesta"=>false,"Error"=>response(["Error"=>["La información enviada es incorrecta"]],422)];
                        }
                    }
                }
            }else{
                return ["respuesta"=>false,"Error"=>response(["Error"=>["La información enviada es incorrecta"]],422)];
            }
        }

        if($request->has("reportes") && is_array($request->input("reportes"))){
            for ($i = 0;$i < count($request->input("reportes"));$i++){
                $reporte = Reporte::find($request->input("reportes")[$i]);
                if($reporte){
                    $plan->reportes()->save($reporte);
                }else{
                    return ["respuesta"=>false,"Error"=>response(["Error"=>["La información enviada es incorrecta"]],422)];
                }
            }
        }
        return ["respuesta"=>true];
    }
}
