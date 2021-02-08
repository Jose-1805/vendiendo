<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\Anuncio;
use App\Models\Bodega;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\RequestAlmacen;

class AlmacenesController extends Controller {
    public function __construct()
    {
        $this->middleware("auth");
        $this->middleware("modConfiguracion");
        $this->middleware("terminosCondiciones");
        $this->middleware("modAlmacenes");
    }

    public function getIndex(){
        return view("almacenes.index");
    }

    public function postStore(RequestAlmacen $request){

        if(Auth::user()->permitirFuncion("Crear","almacenes","inicio") && (Auth::user()->plan()->n_almacenes == 0 || Auth::user()->plan()->n_almacenes > Auth::user()->countAlmacenesAdministrador())) {
            $data = $request->all();

            if($request->has('administrador') && $request->input('administrador') != '') {
                $administrador = User::permitidos(false,true)->where('usuarios.id', $request->input('administrador'))->first();
                if (!$administrador) return response(['error' => ['La información enviada es incorrecta']], 422);

                $almacen_administrador = Almacen::where('administrador', $administrador->id)->first();
                if ($almacen_administrador) return response(['error' => ['El administrador seleccionado ya ha sido asignado a otro almacén']], 422);
            }else{
                $data['administrador'] = null;
            }

            $almacen_prefijo = Almacen::permitidos()->where('prefijo',$request->input('prefijo'))->first();
            if($almacen_prefijo)
                return response(['error' => ['Ya existe un almacén con el prefijo '.$request->input('prefijo')]], 422);

            $data["usuario_id"] = Auth::user()->userAdminId();
            $data["usuario_creador_id"] = Auth::user()->id;

            $almacen = new Almacen();
            $almacen->fill($data);
            $almacen->save();

            Session::flash("mensaje", "Almacén registrado con éxito");
            return ["success" => true];
        }
        return response(['error'=>['Unauthorized.']],401);
    }

    public function postUpdate(RequestAlmacen $request){
        if(Auth::user()->permitirFuncion("Editar","almacenes","inicio")) {

            $data = $request->all();
            if($request->has('administrador') && $request->input('administrador') != '') {
                $administrador = User::permitidos(false, true)->where('usuarios.id', $request->input('administrador'))->first();
                if (!$administrador) return response(['error' => ['La información enviada es incorrecta']], 422);
            }

            $almacen = Almacen::permitidos()->where("id",$request->input("id"))->first();
            if(!$almacen)return response(["error"=>["La información enviada es incorrecta"]],422);

            $almacen_prefijo = Almacen::permitidos()->where('prefijo',$request->input('prefijo'))->where('id','<>',$almacen->id)->first();
            if($almacen_prefijo)
                return response(['error' => ['Ya existe un almacén con el prefijo '.$request->input('prefijo')]], 422);

            if($request->has('administrador') && $request->input('administrador') != '') {
                $almacen_administrador = Almacen::where('id', '<>', $almacen->id)->where('administrador', $administrador->id)->first();
                if ($almacen_administrador) return response(['error' => ['El administrador seleccionado ya ha sido asignado a otro almacén']], 422);
            }else{
                $data['administrador'] = null;
            }

            $data = $request->all();
            $almacen->fill($data);
            $almacen->save();

            Session::flash("mensaje","Bodega editada con éxito");
            return ["success"=>true];
        }
        return response(['error'=>['Unauthorized.']],401);

    }


    public function getEditar($id){
        if(Auth::user()->permitirFuncion("Editar","almacenes","inicio")) {
            $almacen = Almacen::permitidos()->where("id", $id)->first();

            if ($almacen) {
                return view("almacenes.action")->with("almacen", $almacen)->with("action", "Editar");
            }
        }
        return response(['error'=>['Unauthorized.']],401);
    }

    public function getCrear(){
        if(Auth::user()->permitirFuncion("Crear","almacenes","inicio") && (Auth::user()->plan()->n_almacenes == 0 || Auth::user()->plan()->n_almacenes > Auth::user()->countAlmacenesAdministrador())) {
            return view("almacenes.action")->with("action", "Crear");
        }
        return redirect('/');
    }



    public function getListAlmacenes(Request $request)
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
        $almacenes = Almacen::permitidos()->select("almacenes.*","usuarios.nombres","usuarios.apellidos")
        ->leftJoin("vendiendo.usuarios","almacenes.administrador","=","vendiendo.usuarios.id");

        if($orderBy == "administrador")$orderBy = "usuarios.nombres";
        $almacenes = $almacenes->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $almacenes->count();
        if ($search['value'] != null) {
            $f = "%".$search['value']."%";
            $almacenes = $almacenes->where(
                function($query) use ($f){
                    $query->where("almacenes.nombre","like",$f)
                        ->orWhere("almacenes.direccion","like",$f)
                        ->orWhere("almacenes.telefono","like",$f)
                        ->orWhere("almacenes.latitud","like",$f)
                        ->orWhere("almacenes.longitud","like",$f)
                        ->orWhere("almacenes.usuarios.nombres","like",$f)
                        ->orWhere("usuarios.apellidos","like",$f);
                }
            );
        }

        $parcialRegistros = $almacenes->count();
        $almacenes = $almacenes->skip($start)->take($length)->get();

        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($almacenes as $value) {
                $myArray[]=(object) array(
                    'id'=>$value->id,
                    'nombre'=>$value->nombre,
                    'direccion'=>$value->direccion,
                    'telefono'=>$value->telefono,
                    'latitud'=>$value->latitud,
                    'longitud'=>$value->longitud,
                    'administrador'=>$value->nombres.' '.$value->apellidos);
            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $almacenes->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$almacenes];

        return response()->json($data);

    }
}
