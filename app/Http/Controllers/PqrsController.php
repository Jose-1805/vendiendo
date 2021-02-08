<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Abono;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\FacturaProducto;
use App\Models\Notificacion;
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

class PqrsController extends Controller {

	public function __construct(){
		$this->middleware("auth");
		$this->middleware("modPqrs");
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
		return view('pqrs.index');//->with("pqrs",Pqrs::permitidos()->orderBy("created_at","DESC")->get());
	}

	public function getListPqr(Request $request){
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
			$pqrs = Pqrs::permitidos()->orderBy("created_at","DESC");
			$totalRegistros = $pqrs->count();  
		 	if($search['value'] != null){
	            $pqrs = $pqrs->whereRaw(
	                " ( tipo LIKE '%".$search["value"]."%' OR".
	                " queja LIKE '%".$search["value"]."%' OR".
	                " LOWER(CONCAT('(',tipo_identificacion,')',' ', identificacion)) LIKE '%".\strtolower($search["value"])."%' OR".
	                " LOWER(nombre) LIKE '%".\strtolower($search["value"])."%' OR".
	                " created_at LIKE '%".$search["value"]."%' ".
	                ")");
	        }


	        $parcialRegistros = $pqrs->count();
	        $pqrs = $pqrs->skip($start)->take($length);
	        $data = ['length'=> $length,
	            'start' => $start,
	            'buscar' => $search['value'],
	            'draw' => $request->get('draw'),
	            //'last_query' => $pqrs->toSql(),              
                'recordsTotal' =>$totalRegistros,
                'recordsFiltered' =>$parcialRegistros,
	            'data' =>$pqrs->get()
	            ];
		    
	        return response()->json($data);
	}
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function getCreate()
	{
		if(Auth::user()->permitirFuncion("Crear","facturas","inicio"))
			return view('pqrs.action')->with("accion","Crear")->with("pqrs",new Pqrs());

		return redirect("/");
	}

    public function postStore(Requests\RequestPqrs $request){
        if(Auth::user()->permitirFuncion("Crear","pqrs","inicio")) {
            $pqrs = new Pqrs();
            $pqrs->fill($request->all());
            $pqrs->usuario_id = Auth::user()->userAdminId();
            $pqrs->usuario_creador_id = Auth::user()->id;
            $pqrs->save();
            Session::flash("mensaje","El pqrs ha sido registrado con éxito");
            return ["success"=>true];
        }
        return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
    }

    public function postDetalle(Request $request){
        if($request->has("id")){
            $pqrs = Pqrs::find($request->input("id"));
            if($pqrs){
                return view("pqrs.detalle")->with("pqrs",$pqrs);
            }
        }
        return response(["Error"=>["La información enviada es incorrecta"]]);
    }
	
}
