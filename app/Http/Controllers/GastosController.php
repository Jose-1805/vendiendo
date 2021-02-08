<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Caja;
use App\Models\GastoDiario;
use App\Models\OperacionCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\RequestGasto;

class GastosController extends Controller {

	public function __construct()
    {
        $this->middleware("auth");
        $this->middleware("modGastos");
        $this->middleware("modConfiguracion");
        $this->middleware("terminosCondiciones");
    }

    public function getIndex(){
        return view('gastos.index');
    }

    public function postStore(RequestGasto $request){
        $caja = Caja::abierta();
        if(Auth::user()->permitirFuncion("Crear","gastos diarios","inicio") && $caja) {
            if($caja->efectivo_final < $request->input('valor')){
                return response(['error'=>['No existe suficiente dinero en la caja']],422);
            }

            $gasto = new GastoDiario();
            $data = $request->all();
            $gasto->fill($data);
            $gasto->usuario_creador_id = Auth::user()->id;
            $gasto->usuario_id = Auth::user()->userAdminId();
            $gasto->caja_maestra_id = $caja->id;

            if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas =='no') {
                $almacen = Auth::user()->almacenActual();
                $gasto->almacen_id = $almacen->id;
            }

            $gasto->save();
            Caja::updateCaja("Reducir",$gasto->valor);
            Session::flash("mensaje", "El gasto ha sido registrado con éxito.");
            return ["success" => true];
        }
    }

    public function postEdicion(Request $request){
        if(Auth::user()->permitirFuncion("Editar","gastos diarios","inicio")) {
            $gasto = GastoDiario::permitidos()->where("id",$request->input("id"))->first();
            if(!$gasto)return response(["error"=>["La información enviada es incorrecta"]],422);

            $view = view("gastos.form.form")->with("gasto",$gasto);
            return $view->render();
        }
    }

    public function postFormAgregarEfectivoCaja(Request $request){
        if(Auth::user()->perfil->nombre == "administrador"){
            $caja = \App\Models\Caja::cajasPermitidas()->where('fecha',date("Y-m-d"))->first();
            return view("gastos.form.agregar_efectivo_caja")->with("caja",$caja)->render();
        }
    }

    public function postUpdate(RequestGasto $request){
        if(Auth::user()->permitirFuncion("Editar","gastos diarios","inicio")) {
            $gasto = GastoDiario::permitidos()->where("id",$request->input("id"))->first();

            if(strtotime(date("Y-m-d")) > strtotime(date("Y-m-d",strtotime($gasto->created_at))))
                return response(["error"=>["La información enviada es incorrecta"]],422);

            Caja::updateCaja("Adicionar",$gasto->valor);
            $gasto->fill($request->all());
            $gasto->save();
            Caja::updateCaja("Reducir",$gasto->valor);
            Session::flash("mensaje", "El gasto ha sido editado con éxito.");
            return ["success" => true];
        }
    }

    public function postDestroy(Request $request){
        if(Auth::user()->permitirFuncion("Eliminar","gastos diarios","inicio")) {
            $gasto = GastoDiario::permitidos()->where("id",$request->input("id"))->first();
            Caja::updateCaja("Reducir",$gasto->valor);
            $gasto->delete();
            Session::flash("mensaje", "El gasto ha sido eliminado con éxito.");
            return ["success" => true];
        }
    }

    public function getListGastos(Request $request)
    {
        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = $columna[$sortColumnIndex]['data'];//'facturas.id';

        $gastos = GastoDiario::permitidos();

        $gastos = $gastos->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $gastos->count();
        if ($search['value'] != null) {
            $f = "%".$search['value']."%";
            $gastos = $gastos->where(
                function($query) use ($f){
                    $query->where("valor","like",$f)
                        ->orWhere("descripcion","like",$f);
                }
            );
        }

        if($request->has("fecha_inicio") && $request->has("fecha_fin")){
            $gastos = $gastos->whereBetween("gastos_diarios.created_at",[$request->input("fecha_inicio"),date("Y-m-d G:i",strtotime("+1 minute",strtotime($request->input("fecha_fin"))))]);
        }

        $parcialRegistros = $gastos->count();
        $sql = $gastos->skip($start)->take($length)->toSql();
        $gastos = $gastos->skip($start)->take($length)->get();

        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($gastos as $value) {
                $editar = true;
                if(strtotime(date("Y-m-d")) > strtotime(date("Y-m-d",strtotime($value->created_at))))
                    $editar = false;
                $myArray[]=(object) array(
                    'id'=>$value->id,
                    'valor'=>'$ '.number_format($value->valor,'0','.','.'),
                    'descripcion'=>$value->descripcion,
                    'created_at'=>date("Y-m-d h:i:s",strtotime($value->created_at)),
                    'editar'=>$editar);
            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $sql,
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$gastos];

        return response()->json($data);

    }

    public function postAgregarEfectivoCaja(Request $request){
        if(Auth::user()->perfil->nombre == "administrador"){
            $caja =Caja::cajasPermitidas()->where('fecha',date("Y-m-d"))->first();
            if(!$caja)return response(["error"=>["No se ha iniciado la caja del día de hoy"]],422);
            if(!$request->has("cantidad"))return response(["error"=>["El campo cantidad es requerido"]],422);

            $cantidad = intval($request->input("cantidad"));
            $operacionCaja = new OperacionCaja();
            $operacionCaja->caja_id = $caja->id;
            $operacionCaja->fecha = date("Y-m-d h:i:s");
            $operacionCaja->valor = $cantidad;
            $operacionCaja->tipo_movimiento = "Adicionar";
            $operacionCaja->comentario = "Adiciona a caja";
            $operacionCaja->save();
            $caja->updateCaja("Adicionar",$cantidad);
            Session::flash("mensaje","La caja ha sido actualizada con éxito");
            return ["success"=>true];
        }
        return response(["error"=>["Unauthorized"]],401);
    }
}
