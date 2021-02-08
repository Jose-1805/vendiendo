<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Abono;
use App\Models\Almacen;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\FacturaProducto;
use App\Models\Notificacion;
use App\Models\ObjetivoVenta;
use App\Models\Pqrs;
use App\Models\Producto;
use App\Models\ProductoHistorial;
use App\Models\Resolucion;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\RequestNuevoCliente;
use App\User;
use Illuminate\Support\Facades\Validator;

class ObjetivosVentasController extends Controller {

	public function __construct(){
		$this->middleware("auth");
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
		if(
		    (Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'no')
		    || (Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            && Auth::user()->plan()->objetivos_ventas == "si") {
			return view('objetivos_ventas.index')->with("objetivosVentas", ObjetivoVenta::permitidos()->orderBy("anio", "DESC")->orderBy("mes", "DESC")->paginate(env("paginate")));
		}
		return redirect("/");
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
    public function postStore(Requests\RequestObjetivoVenta $request){
        if(
            (Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            && Auth::user()->plan()->objetivos_ventas == "si"){

			$hoy = strtotime(date("Y-m"));
			$fecha = strtotime($request->input('anio')."-".$request->input('mes'));
			/*if($hoy > $fecha){
				return response(["Error"=>["La fecha compuesta por el año y el mes seleccionado no debe ser inferior a la fecha actual."]],422);
			}*/

			$obj = ObjetivoVenta::permitidos()->where("mes",$request->input("mes"))->where("anio",$request->input("anio"));
			if(Auth::user()->bodegas == 'si'){
                $alm = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                $obj->where('almacen_id',$alm->id);
            }
            $obj = $obj->first();
			if($obj){
				return response(["Error"=>["Ya existe un objetivo de venta para la fecha seleccionada"]],422);
			}
            $objetivoVenta = new ObjetivoVenta();
            $objetivoVenta->fill($request->all());
            $objetivoVenta->usuario_id = Auth::user()->userAdminId();
            if(Auth::user()->bodegas == 'si'){
                $almacen = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                if($almacen)
                    $objetivoVenta->almacen_id = $almacen->id;
                else
                    return response(['error'=>['La información enviada es incorrecta']],422);
            }
            $objetivoVenta->save();
            Session::flash("mensaje","El objetivo de venta ha sido registrado con éxito");
            return ["success"=>true];
        }
        return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
    }

	public function postShowStore(){
        if(
            (Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            && Auth::user()->plan()->objetivos_ventas == "si"){
			return view("objetivos_ventas.form");
		}
		return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
	}


	public function postShowUpdate(Request $request){
        if(
            (Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            && Auth::user()->plan()->objetivos_ventas == "si"){
			if($request->has("id")) {
				$objetivo = ObjetivoVenta::permitidos()->where("id",$request->input("id"))->first();
				if($objetivo) {
					$hoy = strtotime(date("Y-m"));
					$fecha = strtotime($objetivo->anio."-".$objetivo->mes);
					if($hoy < $fecha)
						return view("objetivos_ventas.form")->with("o", $objetivo);
					else
						return response(["error"=>["EL objetivo de venta seleccionado no puede ser editado por que su rango de fecha ya a pasado o esta corriendo"]],422);
				}
			}
			return response(["Error"=>["La información enviada es incorrecta"]],422);
		}
		return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
	}

	public function postUpdate(Requests\RequestObjetivoVenta $request){
        if(
            (Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            && Auth::user()->plan()->objetivos_ventas == "si"){
			if(!$request->has("id")){
				return response(["Error"=>["La información enviada es incorrecta"]],422);
			}

			$objetivoVenta = ObjetivoVenta::find($request->input("id"));
			if(!$objetivoVenta){
				return response(["Error"=>["La información enviada es incorrecta"]],422);
			}

			$hoy = strtotime(date("Y-m"));
			$fecha = strtotime($request->input('anio')."-".$request->input('mes'));
			if($hoy > $fecha){
				return response(["Error"=>["La fecha compuesta por el año y el mes seleccionado no debe ser inferior a la fecha actual."]],422);
			}



			$obj = ObjetivoVenta::permitidos()->where("mes",$request->input("mes"))->where("anio",$request->input("anio"))->where("id","<>",$objetivoVenta->id)->first();
			if($obj){
				return response(["Error"=>["Ya existe un objetivo de venta para la fecha seleccionada"]],422);
			}

			$objetivoVenta->fill($request->all());
            if(Auth::user()->bodegas == 'si'){
                $almacen = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                if($almacen)
                    $objetivoVenta->almacen_id = $almacen->id;
                else
                    return response(['error'=>['La información enviada es incorrecta']],422);
            }
			$objetivoVenta->save();
			Session::flash("mensaje","El objetivo de venta ha sido editado con éxito");
			return ["success"=>true];
		}
		return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
	}

	public function getListObjetivosVentas(Request $request)
	{
        if(
            (Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            && Auth::user()->plan()->objetivos_ventas == "si") {
            // Datos de DATATABLE
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = $columna[$sortColumnIndex]['data'];//'facturas.id';

            $objetivos_ventas = ObjetivoVenta::permitidos();

            $objetivos_ventas = $objetivos_ventas->orderBy("anio", "DESC")->orderBy("mes", "DESC");
            $totalRegistros = $objetivos_ventas->count();
            if ($search['value'] != null) {
                $f = "%" . $search['value'] . "%";
                $objetivos_ventas = $objetivos_ventas->where(
                    function ($query) use ($f) {
                        $query->where("valor", "like", $f)
                            ->orWhere("anio", "like", $f)
                            ->orWhere("mes", "like", $f);
                    }
                );
            }

            $parcialRegistros = $objetivos_ventas->count();
            $objetivos_ventas = $objetivos_ventas->skip($start)->take($length)->get();

            $object = new \stdClass();
            $meses = [
                1 => "Enero",
                2 => "Febrero",
                3 => "Marzo",
                4 => "Abril",
                5 => "Mayo",
                6 => "Junio",
                7 => "Julio",
                8 => "Agosto",
                9 => "Septiembre",
                10 => "Octubre",
                11 => "Noviembre",
                12 => "Diciembre",
            ];
            if ($parcialRegistros > 0) {
                foreach ($objetivos_ventas as $value) {
                    $hoy = strtotime(date("Y-m"));
                    $fecha = strtotime($value->anio . "-" . $value->mes);
                    $editar = 0;
                    if ($hoy < $fecha) $editar = 1;
                    $almacen = '';
                    if(Auth::user()->bodegas == 'si')
                        $almacen = $value->almacen->nombre;
                    $myArray[] = (object)array(
                        'id' => $value->id,
                        'fecha' => $meses[$value->mes] . "/" . $value->anio,
                        'valor' => number_format($value->valor, 0, ',', '.'),
                        'almacen'=>$almacen,
                        'editar' => $editar);
                }
            } else {
                $myArray = [];
            }

            $data = ['length' => $length,
                'start' => $start,
                'buscar' => $search['value'],
                'draw' => $request->get('draw'),
                //'last_query' => $objetivos_ventas->toSql(),
                'recordsTotal' => $totalRegistros,
                'recordsFiltered' => $parcialRegistros,
                'data' => $myArray,
                'info' => $objetivos_ventas];

            return response()->json($data);

        }
	}

	public function postDestroy(Request $request){
        if(
            (Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            && Auth::user()->plan()->objetivos_ventas == "si"){
			$objetivo = ObjetivoVenta::permitidos()->where("id",$request->input("id"))->first();

			$objetivo->delete();
			Session::flash("mensaje", "El objetivo de venta ha sido eliminado con éxito.");
			return ["success" => true];
		}
		return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
	}

}
