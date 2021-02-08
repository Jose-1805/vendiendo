<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Anuncio;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\RequestAnuncio;

class AnunciosController extends Controller {
    public function __construct()
    {
        $this->middleware("auth");
        $this->middleware("modConfiguracion");
        $this->middleware("terminosCondiciones");
    }

    public function getIndex(){
        return view("anuncios.index");
    }

    public function getCreate(){
        return view("anuncios.create");
    }

    public function postStore(RequestAnuncio $request){
        $data = $request->all();
        if($request->input("categoria") != "otras"){
            $categoria = Categoria::where("negocio","si")->where("id",$request->input("categoria"))->first();
            if(!$categoria)return response(["error"=>["La información enviada es incorrecta"]],422);
            $data["categoria_id"] = $categoria->id;
            $data["otras"] = "";
        }

        $data["usuario_id"] = Auth::user()->id;
        $data["desde"] = date("Y-m-d");
        $data["hasta"] = date("Y-m-d",strtotime("+1 month",strtotime(date("Y-m-d"))));

        $anuncio = new Anuncio();
        $anuncio->fill($data);
        $anuncio->save();

        $folder = public_path("img/anuncios/".$anuncio->id);
        for($i = 1;$i < 4;$i++) {
            $name = "imagen_".$i;
            if ($request->hasFile($name)) {
                $imagen = $request->file($name);
                $anuncio->$name = $name.".".$imagen->getClientOriginalExtension();
                $imagen->move($folder, $anuncio->$name);
            }
        }
        $anuncio->save();

        Session::flash("mensaje","Anuncio almacenado con éxito");
        return ["success"=>true];
    }

    public function postUpdate(RequestAnuncio $request){
        if(!$request->has("anuncio"))return response(["error"=>["La información enviada es incorrecta"]],422);

        $anuncio = Anuncio::permitidos()->where("id",$request->input("anuncio"))->first();
        if(!$anuncio)return response(["error"=>["La información enviada es incorrecta"]],422);

        $data = $request->all();
        if($request->input("categoria") != "otras"){
            $categoria = Categoria::where("negocio","si")->where("id",$request->input("categoria"))->first();
            if(!$categoria)return response(["error"=>["La información enviada es incorrecta"]],422);
            $data["categoria_id"] = $categoria->id;
            $data["otras"] = "";
        }

        $anuncio->fill($data);
        $anuncio->save();

        $folder = public_path("img/anuncios/".$anuncio->id);
        for($i = 1;$i < 4;$i++) {
            $name = "imagen_".$i;
            if ($request->hasFile($name)) {
                if($anuncio->$name){
                    @unlink($folder."/".$anuncio->$name);
                }
                $imagen = $request->file($name);
                $anuncio->$name = $name.".".$imagen->getClientOriginalExtension();
                $imagen->move($folder, $anuncio->$name);
            }
        }
        $anuncio->save();

        Session::flash("mensaje","Anuncio editado con éxito");
        return ["success"=>true];
    }

    public function getEdit($id){
        $anuncio = Anuncio::permitidos()->where("id",$id)->first();

        if($anuncio){
            return view("anuncios.edit")->with("anuncio",$anuncio);
        }

        return redirect("/");
    }

    public function postVistaImagenes(Request $request){
        if($request->has("id")){
            $anuncio = Anuncio::permitidos()->where("id",$request->input("id"))->first();
            if($anuncio){
                return view("anuncios.vista_imagenes")->with("anuncio",$anuncio)->render();
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }

    public function getListAnuncios(Request $request)
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
        $anuncios = Anuncio::permitidos()->select("anuncios.*")->leftJoin("categorias","categoria_id","=","categorias.id");
        if($orderBy == "categoria")$orderBy = "categorias.nombre";
        $anuncios = $anuncios->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $anuncios->count();
        if ($search['value'] != null) {
            $f = "%".$search['value']."%";
            $anuncios = $anuncios->where(
                function($query) use ($f){
                    $query->where("titulo","like",$f)
                        ->orWhere("anuncios.descripcion","like",$f)
                        ->orWhere("desde","like",$f)
                        ->orWhere("hasta","like",$f)
                        ->orWhere("valor","like",$f)
                        ->orWhere("contacto","like",$f)
                        ->orWhere("estado","like",$f)
                        ->orWhere("categorias.nombre","like",$f)
                        ->orWhere("otros","like",$f);
                }
            );
        }

        $parcialRegistros = $anuncios->count();
        $anuncios = $anuncios->skip($start)->take($length)->get();

        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($anuncios as $value) {
                if($value->categoria_id)$cat = $value->categoria->nombre;
                else $cat = $value->otras;

                $imagenes = "";
                if($value->imagen_1 || $value->imagen_2 || $value->imagen_3)
                    $imagenes = '<a onclick="verImagenes('.$value->id.')"><i class="fa fa-picture-o fa-2x" style="cursor: pointer;"></i></a>';
                $myArray[]=(object) array(
                    'id'=>$value->id,
                    'titulo'=>$value->titulo,
                    'descripcion'=>$value->descripcion,
                    'valor'=>'$ '.number_format($value->valor,0,',','.'),
                    'desde'=>$value->desde,
                    'hasta'=>$value->hasta,
                    'contacto'=>$value->contacto,
                    'categoria'=>$cat,
                    'estado'=>$value->estado,
                    'imagenes'=>$imagenes,
                    'editar'=>'<a href="'.url("/anuncio/edit/".$value->id).'"><i class="fa fa-pencil-square-o fa-2x" style="cursor: pointer;"></i></a>');
            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $anuncios->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$anuncios];

        return response()->json($data);

    }

}
