<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Sede;
use App\Models\Departamento;
use App\Models\Municipio;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SedeController extends Controller {

	public function __construct()
	{
		$this->middleware("auth");
		$this->middleware("modConfiguracion");
		$this->middleware("terminosCondiciones");
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		$sedes = Sede::paginate(env('PAGINATE'));
		return view('sedes.index',compact('sedes'));
	}
	public function getAction($id=''){
		$sede = Sede::firstOrNew(['id'=>$id]);
		$departamentos = Departamento::select('nombre','id')->lists('nombre','id');
		$negocios = User::select('nombre_negocio','id')->lists('nombre_negocio','id');
		$titulo = "Crear nueva sede";
		if ($sede->exists){
			$titulo = "Editar sede";
		}

		return view('sedes.action', compact('titulo','sede','departamentos','negocios'));

	}
	public function postStore(Requests\CreateSedeRequest $request){
		if (Auth::user()->permitirFuncion("Crear","Sedes","inicio")){
			$sede = new Sede();
			DB::beginTransaction();
			$sede->fill($request->all());
			$sede->save();
			$data = [
				"success" => true,
				"mensaje" => "La sede ha sido registrada con éxito."];
			DB::commit();
			return $data;
		}else{
			return response(["Error","Usted no tiene permisos para realizar esta tarea."],401);
		}
	}
	public function postUpdate(Requests\CreateSedeRequest $request,$id){
		if (Auth::user()->permitirFuncion("Editar","Sedes","inicio")){
			$sede = Sede::where('id',$id)->first();
			if ($sede){
				DB::beginTransaction();
				$sede->nombre = $request->get('nombre');
				$sede->direccion = $request->get('direccion');
				$sede->descripcion = $request->get('descripcion');
				$sede->latitud = $request->get('latitud');
				$sede->longitud = $request->get('longitud');
				$sede->municipio_id = $request->get('municipio_id');
				$sede->usuario_id = $request->get('usuario_id');
				$sede->save();
			}
			$data = [
				"success" => true,
				"mensaje" => "La sede ha sido actualizada con éxito."];
			DB::commit();
			return $data;
		}else{
			return response(["Error","Usted no tiene permisos para realizar esta tarea."],401);
		}
	}
	public function getEstado($id_sede,$estado)
	{
		$nuevo_estado = "";
		if($estado=="Activo")
			$nuevo_estado ="Inactivo";
		if($estado=="Inactivo")
			$nuevo_estado = "Activo";
		$sede = Sede::where('id',$id_sede)
			->update([
				"estado"=>$nuevo_estado
			]);

		if ($sede)
			return response()->json(['response' => 'Se cambio satisfactoriamente el estado de la sede']);
		else
			return response()->json(['response' => 'Ocurrio un error']);

	}
	public function getMunicipios($cod_depto){
		$municipios = Municipio::select('*')->where('departamento_id',$cod_depto)->get();
		return response()->json(['response' => $municipios]);
	}
	public function getMapa(){
		return view('mapa');
	}
}
