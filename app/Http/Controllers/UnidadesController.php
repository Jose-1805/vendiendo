<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Unidad;
use Illuminate\Http\Request;
use App\Http\Requests\UnidadCreateRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class UnidadesController extends Controller {

    public function __construct()
    {
        $this->middleware('auth');
        if(Auth::user()->perfil->nombre != "superadministrador")
        $this->middleware("modConfiguracion",["except"=>["postStore"]]);
        $this->middleware('modUnidad');
        $this->middleware("terminosCondiciones");
    }

    public function getIndex(Request $request){

        $filtro = "";
        if($request->has('filtro')){
            $unidades = $this->listaFitroUnidades($request->get('filtro'));
            $filtro = $request->get('filtro');
        }else{
            $unidades = Unidad::unidadesPermitidas()->orderBy("created_at", "DESC")->paginate(env('PAGINATE'));
        }
        return view('unidades.index')->with("unidades",$unidades)->with("filtro",$filtro);
    }

    public function getCreate(Request $request){
        $titulo='Crear Unidad';
        if(Auth::user()->permitirFuncion("Crear","Unidades","configuracion")) {
            $view = view('unidades.create')->with("unidad", new Unidad())->with('titulo', $titulo);

            if($request->has("noPrOpc")){
                $view = $view->with("noPrOpc",true);
            }
            return $view;
        }

        return redirect('/');

    }
    public function postStore(UnidadCreateRequest $request){

        if(Auth::user()->permitirFuncion("Crear", "Unidades","configuracion")){
            if(Auth::user()->perfil->nombre != "superadministrador") {
                $unidadesNombre = Unidad::where("nombre", $request->input("nombre"))
                    ->where("usuario_id", Auth::user()->userAdminId())->get();
            }else{
                $unidadesNombre = Unidad::where("nombre", $request->input("nombre"))
                    ->where("superadministrador", "si")->get();
            }

            if($unidadesNombre->count()>0)
                return response(["Error"=>["Ya existe una unidad con el nombre ingresado"]],422);

            $unidad = new Unidad();
            $unidad->fill($request->all());
            $unidad->usuario_id_creator = Auth::user()->id;
            $unidad->usuario_id = Auth::user()->userAdminId();
            $unidad->superadministrador = "no";
            if(Auth::user()->perfil->nombre == "superadministrador") {
                $unidad->superadministrador = "si";
            }
            $unidad->save();

            $data = ["success" => true];
            if($request->has("noPrOpc")){
                $data["id_anterior"]=$unidad->id;
                $data["valores"] = Unidad::unidadesPermitidas()->get();
                $data["location"] = "productos";
            }else if($request->has("configuracion_unidades")){
                $data["location"] = "inicio";
                Session::flash("posicion", "Unidades");
                Session::flash("mensaje", "La unidad se registro con éxito");
            }else{
                $data["location"] = "unidades";
                Session::flash("mensaje", "La unidad se registro con éxito");
            }
            return $data;
        }
        return response("Unauthorized", 401);
    }
    public function getEdit($id)
    {
        if(Auth::user()->permitirFuncion("Editar","Unidades","configuracion")) {
            $titulo="Editar Unidad";
            $unidad = Unidad::unidadesPermitidas()->where("id",$id)->first();
            if ($unidad && $unidad->exists) {
                return view("unidades.edit")->with("unidad", $unidad)->with('titulo',$titulo);
            }
            return redirect("/");
        }

        return redirect("/");
    }
    public function postUpdate($id, Requests\UnidadUpdateRequest $request){

        if(Auth::user()->permitirFuncion("Editar","Unidades","configuracion")){
            $unidad = Unidad::unidadesPermitidas()->where("id",$id)->first();

            if($unidad && $unidad->exists){
                $unidad->fill($request->all());
                $unidad->save();
                Session::flash("mensaje", "La unidad se actualizo correctamente");
                return ['success' => true,'href'=>url('/unidades')];
            }
            return ['success' => false];

        }
        return response("Unauthorized",401);
        
    }

    public function postFiltro(Request $request){
        if($request->has("filtro")){
            $unidades = $this->listaFitroUnidades($request->get("filtro"));
        }else{
            $unidades = Unidad::unidadesPermitidas()->orderBy("updated_at","DESC")->paginate(env('PAGINATE'));
        }
        $unidades->setPath(url('/unidades'));
        return view("unidades.lista")->with("unidades",$unidades);
    }

    public  function  listaFitroUnidades($filtro){

        //exit($filtro);

        $f = "%".$filtro."%";
        return $unidades = Unidad::unidadesPermitidas()->where(
            function($query) use ($f){
                $query->where("nombre","like",$f);
            }
        )->orderBy("created_at","DESC")->paginate(env('PAGINATE'));

    }

    public function postDestroy($id)
    {
        if(Auth::user()->permitirFuncion("Eliminar","Unidades","configuracion")) {
            $unidad = Unidad::unidadesPermitidas()->where("id",$id)->first();

            if ($unidad && $unidad->exists) {
                $unidad->delete();
                Session::flash("mensaje", "La unidad ha sido eliminado con éxito");
                return ["success" => true];
            } else {
                return ["error" => "La información es incorrecta"];
            }
        }
        return response("Unauthorized",401);
    }

    public function getListUnidades(Request $request)
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

        $unidades = Unidad::unidadesPermitidas();

        $unidades = $unidades->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $unidades->count();
        if ($search['value'] != null) {
            $f = "%".$search['value']."%";
            $unidades = $unidades->where(
                function($query) use ($f){
                    $query->where("nombre","like",$f)
                        ->orWhere("sigla","like",$f);
                }
            );
        }

        $parcialRegistros = $unidades->count();
        $unidades = $unidades->skip($start)->take($length)->get();

        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($unidades as $value) {
                $myArray[]=(object) array(
                    'id'=>$value->id,
                    'nombre'=>$value->nombre,
                    'sigla'=>$value->sigla);
            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $unidades->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$unidades];

        return response()->json($data);

    }

}
