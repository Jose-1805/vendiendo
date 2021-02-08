<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Anuncio;
use App\Models\Bodega;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\RequestAnuncio;

class BodegasController extends Controller {
    public function __construct()
    {
        $this->middleware("auth");
        $this->middleware("modConfiguracion");
        $this->middleware("terminosCondiciones");
        $this->middleware("modBodegas");
    }

    public function getIndex(){
        return view("bodegas.index");
    }

    public function postStore(Request $request){
        if(Auth::user()->permitirFuncion("Crear","bodegas","inicio") && (Auth::user()->plan()->n_bodegas == 0 || Auth::user()->plan()->n_bodegas > Auth::user()->countBodegasAdministrador())) {
            if(count(Bodega::permitidos()->get()) == 0) {
                $data = $request->all();
                if (!$request->has('nombre') || !$request->has('direccion'))
                    return response(['error' => ['Todos los campos son requeridos']], 422);
                $data["usuario_id"] = Auth::user()->userAdminId();
                $data["usuario_creador_id"] = Auth::user()->id;

                $bodega = new Bodega();
                $bodega->fill($data);
                $bodega->save();

                Session::flash("mensaje", "Bodega almacenada con éxito");
                return ["success" => true];
            }else{
                return response(['error' => ['No es posible crear más de una bodega']], 422);
            }
        }
        return response(['error'=>['Unauthorized.']],401);
    }

    public function postUpdate(Request $request){
        if(Auth::user()->permitirFuncion("Editar","bodegas","inicio")) {
            if(!$request->has("id"))return response(["error"=>["La información enviada es incorrecta"]],422);

            if (!$request->has('nombre') || !$request->has('direccion'))
                return response(['error' => ['Todos los campos son requeridos']], 422);

            $bodega = Bodega::permitidos()->where("id",$request->input("id"))->first();
            if(!$bodega)return response(["error"=>["La información enviada es incorrecta"]],422);

            $data = $request->all();
            $bodega->fill($data);
            $bodega->save();

            Session::flash("mensaje","Bodega editada con éxito");
            return ["success"=>true];
        }
        return response(['error'=>['Unauthorized.']],401);

    }

    public function postEditar(Request $request){
        if(Auth::user()->permitirFuncion("Editar","bodegas","inicio")) {
            $id = $request->input('id');
            $bodega = Bodega::permitidos()->where("id", $id)->first();

            if ($bodega) {
                return view("bodegas.form")->with("bodega", $bodega);
            }
        }
        return response(['error'=>['Unauthorized.']],401);
    }

    public function getListBodegas(Request $request)
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
        $bodegas = Bodega::permitidos()->select("bodegas.*","usuarios.nombres","usuarios.apellidos")
        ->join("vendiendo.usuarios","bodegas.usuario_creador_id","=","vendiendo.usuarios.id");

        if($orderBy == "creador")$orderBy = "usuarios.nombres";
        $bodegas = $bodegas->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $bodegas->count();
        if ($search['value'] != null) {
            $f = "%".$search['value']."%";
            $bodegas = $bodegas->where(
                function($query) use ($f){
                    $query->where("nombre","like",$f)
                        ->orWhere("direccion","like",$f)
                        ->orWhere("usuarios.nombres","like",$f)
                        ->orWhere("usuarios.apellidos","like",$f);
                }
            );
        }

        $parcialRegistros = $bodegas->count();
        $bodegas = $bodegas->skip($start)->take($length)->get();

        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($bodegas as $value) {
                $myArray[]=(object) array(
                    'id'=>$value->id,
                    'nombre'=>$value->nombre,
                    'direccion'=>$value->direccion,
                    'creador'=>$value->nombres.' '.$value->apellidos);
            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $bodegas->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$bodegas];

        return response()->json($data);

    }

}
