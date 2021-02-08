<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Cliente;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RequestNuevoCliente;
use Illuminate\Support\Facades\Session;

class ClientesController extends Controller {


	public function __construct(){
		$this->middleware("auth");
		$this->middleware("modClientes");
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
		return view('clientes.index');
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function postEdit(Request $r, $id)
	{
		if(Auth::user()->permitirFuncion("Editar","clientes","inicio")) {
			$cliente = Cliente::permitidos()->where("clientes.id", $id)->first();
			if ($cliente && $cliente->exists)
				return view('clientes.form')->with("cliente", $cliente);
		}
		return response(["error"=>["Información incorrecta"]],422);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function postUpdate(RequestNuevoCliente $request)
	{
		if($request->has("id_cliente")){
			if(Auth::user()->perfil->nombre == "administrador"){
				$administrador = Auth::user();
			}else{
				$administrador = User::find(Auth::user()->usuario__creador_id);
			}

			$clienteIdentificacion = Cliente::where("identificacion",$request->input("identificacion"))->where("id","<>",$request->input("id_cliente"))->where("usuario_id",$administrador->id)->first();
			if(!$clienteIdentificacion){
				$cliente = Cliente::permitidos()->where("id",$request->input("id_cliente"))->first();
				if($cliente){
					$cliente->fill($request->all());
					$cliente->save();
					Session::flash("mensaje","La información del cliente ha sido editada con éxito");
					return ["success"=>true];
				}
			}else{
				return response(["Ya existe un cliente con el número de identificaión ingresado"],422);
			}
		}
		return response(["La información enviada es incorrecta"],422);
	}

	public function getListClientes(Request $request)
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

		$clientes = Cliente::permitidos();

		$clientes = $clientes->orderBy($orderBy, $sortColumnDir);
		$totalRegistros = $clientes->count();
		if ($search['value'] != null) {
			$f = "%".$search['value']."%";
			$clientes = $clientes->where(
				function($query) use ($f){
					$query->where("nombre","like",$f)
						->orWhere("identificacion","like",$f)
						->orWhere("telefono","like",$f)
						->orWhere("correo","like",$f)
						->orWhere("direccion","like",$f);
				}
			);
		}

		$clientes = $clientes->where("predeterminado","<>","si");
		$parcialRegistros = $clientes->count();
		$clientes = $clientes->skip($start)->take($length)->get();

		$object = new \stdClass();
		if($parcialRegistros > 0){
			foreach ($clientes as $value) {
				$myArray[]=(object) array(
					'id'=>$value->id,
					'nombre'=>$value->nombre,
					'identificacion'=>"(".$value->tipo_identificacion .") ".$value->identificacion,
					'telefono'=>$value->telefono,
					'correo'=>$value->correo,
					'direccion'=>$value->direccion,
					'eliminar'=>$value->permitirEliminar());
			}
		}else{
			$myArray=[];
		}

		$data = ['length'=> $length,
			'start' => $start,
			'buscar' => $search['value'],
			'draw' => $request->get('draw'),
			//'last_query' => $clientes->toSql(),
			'recordsTotal' =>$totalRegistros,
			'recordsFiltered' =>$parcialRegistros,
			'data' => $myArray,
			'info' =>$clientes];

		return response()->json($data);

	}

	public function postDestroy(Request $request){
		if($request->has("cliente")){
			$cliente = Cliente::permitidos()->where("id",$request->input("cliente"))->first();
			if($cliente && $cliente->permitirEliminar()){
				$cliente->delete();
				//Session::flash("mensaje","Cliente eliminado con éxito");
				return ["success"=>true];
			}
		}
		return response(["error"=>["La información enviada es incorrecta"]],422);
	}

	public function postStore(RequestNuevoCliente $request){
		if(Auth::user()->permitirFuncion("Crear","clientes","inicio") && (Auth::user()->plan()->n_clientes == 0 || Auth::user()->plan()->n_clientes > Auth::user()->countClientesAdministrador())) {
			if (Auth::user()->perfil->nombre == "administrador") {
			    if(Auth::user()->bodegas == 'si')
				    $administrador = User::find(Auth::user()->userAdminId());
			    else
				    $administrador = Auth::user();
			} else {
				$administrador = User::find(Auth::user()->usuario_creador_id);
			}
			$clienteIdentificacion = Cliente::where("identificacion", $request->input("identificacion"))->where("usuario_id", $administrador->id)->first();
			if ($clienteIdentificacion) {
				return response(["error" => ["Ya existe un cliente con el número de identificaión ingresado"]], 422);
			}

			$cliente = new Cliente();
			$cliente->fill($request->all());
			$cliente->usuario_id = $administrador->id;
			$cliente->usuario_creador_id = Auth::user()->id;
			$cliente->save();
			return ["success" => true, "cliente" => $cliente];
		}else{
			return response(["error"=>["Usted no tiene permisos para realizar esta acción"]],422);
		}
	}

}
