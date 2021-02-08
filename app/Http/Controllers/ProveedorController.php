<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use App\Http\Requests\RequestNuevoProveedor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

class ProveedorController extends Controller {


    public function __construct(){
        $this->middleware("auth");
        $this->middleware("modConfiguracion",["except"=>["postStore"]]);
        $this->middleware("modProveedor",["except"=>["postSelect","postCount"]]);
        $this->middleware("terminosCondiciones");
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex(Request $request)
	{
        $filtro = "";
        if($request->has("filtro")){
            $proveedores = $this->listaFiltro($request->get("filtro"));
            $filtro = $request->get("filtro");
        }else {
            $proveedores = Proveedor::permitidos()->orderBy("updated_at", "DESC")->paginate(10);
        }
        return view('proveedor.index')->with("proveedores",$proveedores)->with("filtro",$filtro);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function getCreate(Request $request)
	{
        if(Auth::user()->permitirFuncion("Crear","proveedores","configuracion") && (Auth::user()->plan()->n_proveedores == 0 || Auth::user()->plan()->n_proveedores > Auth::user()->countProveedoresAdministrador())) {
            $view = view('proveedor.create')->with("proveedor", new Proveedor());
            if($request->has("noPrOpc")){
                $view = $view->with("noPrOpc",true);
            }
            return $view;
        }

        return redirect("/");
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function postStore(RequestNuevoProveedor $request)
	{
        if(Auth::user()->permitirFuncion("Crear","proveedores","configuracion") && (Auth::user()->plan()->n_proveedores == 0 || Auth::user()->plan()->n_proveedores > Auth::user()->countProveedoresAdministrador())) {
            $proveedorNit = Proveedor::permitidos()->where("nit",$request->input("nit"))->first();
            if($proveedorNit)return response(["error"=>["Ya ha registrado un proveedor con el NIT ingresado"]],422);
            $proveedor = new Proveedor();
            $proveedor->fill($request->all());
            $proveedor->usuario_creador_id = Auth::user()->id;
            $proveedor->usuario_id = Auth::user()->userAdminId();
            $proveedor->save();
            Session::flash("posicion", "Proveedores");
            Session::flash("mensaje", "El proveedor ha sido registrado con éxito");
            return ["success" => true,"proveedor"=>$proveedor->id,"proveedor_data"=>$proveedor];
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
        if(Auth::user()->permitirFuncion("Editar","proveedores","configuracion")) {
            $proveedor = Proveedor::permitidos()->where("id",$id)->first();
            if ($proveedor && $proveedor->exists) {
                return view("proveedor.edit")->with("proveedor", $proveedor);
            }
            return redirect("/");
        }

        return redirect("/");
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function postUpdate($id,RequestNuevoProveedor $request)
	{
        if(Auth::user()->permitirFuncion("Editar","proveedores","configuracion")) {
            $proveedorNit = Proveedor::permitidos()->where("nit",$request->input("nit"))->where("id","<>",$id)->first();
            if($proveedorNit)return response(["error"=>["Ya ha registrado un proveedor con el NIT ingresado"]],422);
            $proveedor = Proveedor::permitidos()->where("id",$id)->first();

            if ($proveedor && $proveedor->exists) {
                $proveedor->fill($request->all());
                $proveedor->save();
                Session::flash("mensaje", "El proveedor ha sido editado con éxito");
                return ["success" => true,"href"=>url('/proveedor')];
            }
            return ["succes" => false];
        }

        return response("Unauthorized",401);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function postDestroy(Request $request)
	{
        if(Auth::user()->permitirFuncion("Eliminar","proveedores","configuracion") && $request->has("proveedor")) {
            $proveedor = Proveedor::permitidos()->where("id",$request->input("proveedor"))->first();
            if ($proveedor && $proveedor->permitirEliminar()) {
                $proveedor->delete();
                //Session::flash("mensaje", "El proveedor ha sido eliminado con éxito");
                return ["success" => true];
            }
        }
        return response(["error" => ["La información es incorrecta"]],422);
	}

    public function postFiltro(Request $request){
        if($request->has("filtro")){
            $proveedores = $this->listaFiltro($request->get("filtro"));
        }else{
            $proveedores = Proveedor::permitidos()->orderBy("updated_at","DESC")->paginate(10);
        }
        $proveedores->setPath(url('/proveedor'));
        return view("proveedor.lista")->with("proveedores",$proveedores);
    }

    public function listaFiltro($filtro){
        $f = "%".$filtro."%";
        return $proveedores = Proveedor::permitidos()->where(
            function($query) use ($f){
                $query->where("nombre","like",$f)
                    ->orWhere("contacto","like",$f)
                    ->orWhere("direccion","like",$f)
                    ->orWhere("telefono","like",$f)
                    ->orWhere("correo","like",$f);
            }
        )->orderBy("updated_at","DESC")->paginate(10);
    }

    public function postSelect(Request $request){
        $id = $request->get("id");
        $html = "<select id='".$id."' name='".$id."'><option disabled selected>Seleccione un proveedor</option>";

        foreach(Proveedor::permitidos()->get() as $pr) {
            $html .= "<option value='$pr->id'>" . $pr->nombre . "</option>";
        }
        $html .= "</select>";
        return $html;
    }

    public function postCount(){
        return Proveedor::permitidos()->get()->count();
    }


    public function getListProvedores(Request $request){
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = $columna[$sortColumnIndex]['data'];              
        $proveedores = Proveedor::permitidos()->select('id','nombre','nit','contacto','direccion','telefono','correo');
        $proveedores = $proveedores->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $proveedores->count();

        if($search['value'] != null){
            $proveedores = $proveedores->whereRaw(
                " ( LOWER(nombre) LIKE '%".\strtolower($search["value"])."%' OR".
                " nit LIKE '%".$search["value"]."%' OR".
                " LOWER(contacto) LIKE '%".\strtolower($search["value"])."%' OR".
                " LOWER(direccion) LIKE '%".\strtolower($search["value"])."%' OR".
                " telefono LIKE '%".$search["value"]."%' OR".
                " LOWER(correo) LIKE '%".\strtolower($search["value"])."%' )");
        }

        $parcialRegistros = $proveedores->count();
        $proveedores = $proveedores->skip($start)->take($length)->get();

        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($proveedores as $value) {
                $myArray[]=(object) array(
                    'id'=>$value->id,
                    'nombre'=>$value->nombre,
                    'nit'=>$value->nit,
                    'contacto'=>$value->contacto,
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
                //'last_query' => $proveedores->toSql(),
                'recordsTotal' =>$proveedores->count(),
                'recordsFiltered' =>$proveedores->count(),
                //'data' =>$myArray,
                'data' =>$myArray];

        return response()->json($data);
}

}
