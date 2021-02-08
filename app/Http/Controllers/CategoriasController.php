<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Categoria;
use Illuminate\Http\Request;
use App\Http\Requests\RequestCategoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\User;
use Illuminate\Support\Facades\URL;

class CategoriasController extends Controller {

	public function __construct()
	{
		$this->middleware("auth");
		if(Auth::user()->perfil->nombre != "superadministrador")
		$this->middleware("modConfiguracion",["except"=>["postStore"]]);
		$this->middleware("modCategoria");
		$this->middleware("terminosCondiciones");
	}

	/**
	 * Lista todas las categorias relacionadas con el usuario
	 *
	 * @return Response
	 */
	public function getIndex(Request $request)
	{
		$filtro = "";
		if($request->has("filtro")){
			$categorias = $this->listaFiltro($request->get("filtro"));
			$filtro = $request->get("filtro");
		}else {
			$categorias = Categoria::permitidos()->groupby('categorias.nombre')->orderBy("updated_at", "DESC")->paginate(10);
		}
		return view("categoria.index")->with("categorias",$categorias)->with("filtro",$filtro);
	}


	/**
	 * Crea o edita una categoria
	 *
	 * @return Response
	 */
	public function postAccion(RequestCategoria $request)
	{
		if($request->has("accion")) {
			if($request->input("accion") == "Agregar") {
				if(Auth::user()->permitirFuncion("Crear","Categorias","configuracion") || Auth::user()->perfil->nombre == "superadministrador") {
					$categoria = new Categoria();
					$categoria->fill($request->all());

					$categoria->usuario_creador_id = Auth::user()->id;

					if (Auth::user()->perfil->nombre == "superadministrador")$categoria->negocio = "si";
					else $categoria->negocio = "no";
					
					if (Auth::user()->perfil->nombre == "usuario") {
						$categoria->usuario_id = Auth::user()->usuario_creador_id;
					} else {
						$categoria->usuario_id = Auth::user()->id;
					}

					$categoria->save();

					$data_url = explode("/",explode($_SERVER['SERVER_NAME'], URL::previous())[1]);
					$data = ["success" => true,"location"=>$data_url[1], "mensaje" => "La categoria ha sido registrada con éxito."];
					if($data_url[1] == "productos"){
						$data["id_anterior"]=$categoria->id;
						$data["valores"] = Categoria::permitidos()->get();
					}else{
						Session::flash("mensaje", "La categoria ha sido creada con éxito");
					}
					return $data;
				}else{
					return response(["Error","Usted no tiene permisos para realizar esta tarea."],401);
				}
			}else{
				if($request->input("accion") == "Editar") {
					if(Auth::user()->permitirFuncion("Editar","Categorias","configuracion") || Auth::user()->perfil->nombre == "superadministrador") {
						$categoria = Categoria::find($request->input("categoria"));
						if($categoria) {
							$categoria->fill($request->all());
							$categoria->save();
							Session::flash("mensaje", "La categoria ha sido editada con éxito");
							return ["success" => true, "mensaje" => "La categoria ha sido editada con éxito."];
						}else{
							return response(["Error","La información enviada es incorrecta."],422);
						}
					}else{
						return response(["Error","Usted no tiene permisos para realizar esta tarea."],401);
					}
				}
			}
		}
		return reponse(["error"=>["La información enviada es incorrecta"]],422);
	}
	public function postStore(RequestCategoria $request){
		if(Auth::user()->permitirFuncion("Crear","Categorias","configuracion")) {
			$categoria = new Categoria();
			$categoria->fill($request->all());
			$categoria->usuario_creador_id = Auth::user()->id;
			if (Auth::user()->perfil->nombre == "usuario") {
				$categoria->usuario_id = Auth::user()->usuario_creador_id;
			} else {
				$categoria->usuario_id = Auth::user()->id;
			}

			if (Auth::user()->perfil->nombre == "superadministrador")$categoria->negocio = "si";
			else $categoria->negocio = "no";
			$categoria->save();

			$data = ["success" => true];
			$data["location"] = "inicio";

			Session::flash("posicion", "Categorias");
			Session::flash("mensaje", "La categoria se registro con éxito");
			return $data;
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

	public function postFiltro(Request $request){
		if($request->has("filtro")){
			$categorias = $this->listaFiltro($request->get("filtro"));
		}else{
			$categorias = Categoria::permitidos()->groupby('categorias.nombre')->orderBy("updated_at","DESC")->paginate(10);
		}
		$categorias->setPath(url('/categoria'));
		return view("categoria.lista")->with("categorias",$categorias);
	}

	public function listaFiltro($filtro){
		$f = "%".$filtro."%";
		return $categorias = Categoria::permitidos()->where(
			function($query) use ($f){
				$query->where("categorias.nombre","like",$f)
					->orWhere("descripcion","like",$f);
			}
		)->groupby('categorias.nombre')->orderBy("updated_at","DESC")->paginate(10);
	}

	public function postDestroy($id)
	{
		if(Auth::user()->permitirFuncion("Eliminar","Categorias","configuracion")) {
			$categoria = Categoria::permitidos()->where("id",$id)->first();
			if ($categoria && $categoria->exists) {
				if(count($categoria->productos)){
					return response(["error" => "Esta categoría no puede ser eliminada porque existen productos relacionados con ella"],422);
				}
				$categoria->delete();
				Session::flash("mensaje", "La categoria ha sido eliminada con éxito");
				return ["success" => true];
			} else {
				return response(["error" => "La información es incorrecta"],422);
			}
		}
		return response("Unauthorized",401);
	}

	public function getListCategorias(Request $request)
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

		$categorias = Categoria::permitidos();

		$categorias = $categorias->orderBy($orderBy, $sortColumnDir);
		$totalRegistros = $categorias->count();
		if ($search['value'] != null) {
			$f = "%".$search['value']."%";
			$categorias = $categorias->where(
				function($query) use ($f){
					$query->where("nombre","like",$f)
						->orWhere("descripcion","like",$f);
				}
			);
		}

		$parcialRegistros = $categorias->count();
		$categorias = $categorias->skip($start)->take($length)->get();

		$object = new \stdClass();
		if($parcialRegistros > 0){
			foreach ($categorias as $value) {
				$myArray[]=(object) array(
					'id'=>$value->id,
					'nombre'=>$value->nombre,
					'descripcion'=>$value->descripcion);
			}
		}else{
			$myArray=[];
		}

		$data = ['length'=> $length,
			'start' => $start,
			'buscar' => $search['value'],
			'draw' => $request->get('draw'),
			//'last_query' => $categorias->toSql(),
			'recordsTotal' =>$totalRegistros,
			'recordsFiltered' =>$parcialRegistros,
			'data' => $myArray,
			'info' =>$categorias];

		return response()->json($data);

	}

}
