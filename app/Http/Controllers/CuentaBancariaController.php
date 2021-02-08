<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Categoria;
use App\Models\Consignacion;
use App\Models\CuentaBancaria;
use Illuminate\Http\Request;
use App\Http\Requests\RequestCategoria;
use App\Http\Requests\RequestCuentaBancaria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\User;
use Illuminate\Support\Facades\URL;

class CuentaBancariaController extends Controller {

	public function __construct()
	{
		$this->middleware("auth");
		$this->middleware("modCuentaBancaria");
		$this->middleware("modConfiguracion");
		$this->middleware("terminosCondiciones");
	}

	/**
	 * Lista todas las categorias relacionadas con el usuario
	 *
	 * @return Response
	 */
	public function getIndex(Request $request)
	{
		return view("cuentas_bancarias.index");
	}

	public function postStore(RequestCuentaBancaria $request){
		if(Auth::user()->permitirFuncion("Crear","cuentas bancarias","configuracion")) {
			$cuenta = new CuentaBancaria();
			$data = $request->all();
			$data["usuario_id"] = Auth::user()->userAdminId();
			$data["usuario_creador_id"] = Auth::user()->id;
			$data["banco_id"] = $data["banco"];
			$cuenta->fill($data);

			$cuenta->save();

			Session::flash("mensaje", "La cuenta bancaria ha sido registrada con éxito");
			return ["success"=>true];
		}else{
			return response(["Error","Usted no tiene permisos para realizar esta tarea."],401);
		}
	}

	function postForm(Request $reques, $id){
		if(Auth::user()->permitirFuncion("Editar","Categorias","configuracion")) {
			$categoria = Categoria::permitidos()->where("id",$id)->first();
			if($categoria){
				return view("categoria.form")->with("categoria",$categoria)->with("accion","Editar");
			}
			return response(["Error","La información enviada es incorrecta."],422);
		}else{
			return response(["Error","Usted no tiene permisos para realizar esta tarea."],401);
		}
	}


	public function getListCuentaBancaria(Request $request)
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

		if($orderBy == "banco")$orderBy = "bancos.nombre";
		if($orderBy == "usuario")$orderBy = "usuarios.nombres";


		$cuentas = CuentaBancaria::permitidos()->select("cuentas_bancos.*","bancos.nombre","usuarios.nombres","usuarios.apellidos")
        ->join("bancos","cuentas_bancos.banco_id","=","bancos.id")
		->join("usuarios","cuentas_bancos.usuario_creador_id","=","usuarios.id");

		$cuentas = $cuentas->orderBy($orderBy, $sortColumnDir);
		$totalRegistros = $cuentas->count();
		if ($search['value'] != null) {
			$f = "%".$search['value']."%";
			$cuentas = $cuentas->where(
				function($query) use ($f){
					$query->where("bancos.nombre","like",$f)
						->orWhere("titular","like",$f)
						->orWhere("numero","like",$f)
						->orWhere("saldo","like",$f)
						->orWhere("usuarios.nombres","like",$f)
						->orWhere("usuarios.apellidos","like",$f);
				}
			);
		}

		$parcialRegistros = $cuentas->count();
		$cuentas = $cuentas->skip($start)->take($length)->get();

		$object = new \stdClass();
		if($parcialRegistros > 0){
			foreach ($cuentas as $value) {
				$myArray[]=(object) array(
					'id'=>$value->id,
					'banco'=>$value->nombre,
					'titular'=>$value->titular,
					'numero'=>$value->numero,
					'saldo'=>'$ '.number_format($value->saldo,0,',','.'),
					'usuario'=>$value->nombres." ".$value->apellidos);
			}
		}else{
			$myArray=[];
		}

		$data = ['length'=> $length,
			'start' => $start,
			'buscar' => $search['value'],
			'draw' => $request->get('draw'),
			//'last_query' => $cuentas->toSql(),
			'recordsTotal' =>$totalRegistros,
			'recordsFiltered' =>$parcialRegistros,
			'data' => $myArray,
			'info' =>$cuentas];

		return response()->json($data);

	}

	public function postConsignar(Request $request){
        if(Auth::user()->permitirFuncion("Consignar","cuentas bancarias","configuracion")) {
            if($request->has("cuenta")){
                $cuenta = CuentaBancaria::permitidos()->where("id",$request->input("cuenta"))->first();
                if($cuenta){
                    if($request->has("valor")){
                        if(is_numeric($request->input("valor"))){
                            DB::beginTransaction();
                            $cuenta->saldo += $request->input("valor");
                            $cuenta->save();
                            $consignacion = new Consignacion();
                            $consignacion->valor = $request->input("valor");
                            $consignacion->saldo = $cuenta->saldo;
                            $consignacion->cuenta_id = $cuenta->id;
                            $consignacion->usuario_creador_id = Auth::user()->id;
                            $consignacion->save();
                            DB::commit();
                            Session::flash("mensaje","La consignación ha sido registrada con éxito");
                            return ["success"=>true];
                        }else{
                            return response(["error" => ["El campo valor debe ser de tipo numerico"]], 422);
                        }
                    }else{
                        return response(["error" => ["El campo valor es requerido"]], 422);
                    }
                }
            }
        }
        return response(["error" => ["La información enviada es incorrecta"]], 422);
    }

    public function getListConsignaciones(Request $request,$id)
    {
        $cuenta = CuentaBancaria::permitidos()->find($id);
        if(!$cuenta)return response(["error"=>["La información enviada es incorrecta"]],422);
        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = $columna[$sortColumnIndex]['data'];//'facturas.id';

        if($sortColumnDir == "asc")$sortColumnDir = "desc";
        else $sortColumnDir = "asc";
        if($orderBy == "usuario")$orderBy = "usuarios.nombres";
        else $orderBy = "consignaciones.".$orderBy;


        $consignaciones = $cuenta->consignaciones()->select("consignaciones.*","usuarios.nombres","usuarios.apellidos")
            ->join("usuarios","consignaciones.usuario_creador_id","=","usuarios.id");

        $consignaciones = $consignaciones->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $consignaciones->count();
        if ($search['value'] != null) {
            $f = "%".$search['value']."%";
            $consignaciones = $consignaciones->where(
                function($query) use ($f){
                    $query->where("consignaciones.created_at","like",$f)
                        ->orWhere("consignaciones.valor","like",$f)
                        ->orWhere("usuarios.nombres","like",$f)
                        ->orWhere("usuarios.apellidos","like",$f);
                }
            );
        }

        $parcialRegistros = $consignaciones->count();
        $consignaciones = $consignaciones->skip($start)->take($length)->get();

        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($consignaciones as $value) {
                $myArray[]=(object) array(
                    'id'=>$value->id,
                    'created_at'=>''.$value->created_at,
                    'valor'=>'$ '.number_format($value->valor,0,'','.'),
                    'usuario'=>$value->nombres." ".$value->apellidos);
            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $consignaciones->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$consignaciones];

        return response()->json($data);

    }

}
