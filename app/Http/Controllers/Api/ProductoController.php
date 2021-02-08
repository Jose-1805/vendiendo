<?php

namespace App\Http\Controllers\Api;

use App\Models\ProductoApp;
use App\Models\Producto;
use App\User;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\RequestProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{

    public function postListaWeb(){

        $productos = Producto::permitidos()->select('productos.*','productos_historial.id as producto_historial_id')
            ->join('productos_historial','productos.id','=','productos_historial.producto_id')
            ->whereRaw('productos_historial.id in (SELECT MAX(ph.id) as ph_id FROM `productos_historial` as ph where ph.proveedor_id in (SELECT p.proveedor_actual FROM productos as p WHERE p.id = ph.producto_id) OR ph.proveedor_id IS NULL GROUP By ph.producto_id,ph.proveedor_id)')
            ->get();

        return response(["data"=>$productos],200);
    }

    public function postListaPendiente(){
        return response(["data"=>ProductoApp::permitidos()->where("estado","pendiente")->get()],200);
    }

    public function postStore(Request $request){
        if(Auth::user()->permitirFuncion("Crear","productos","inicio")) {   
            $validator = Validator::make($request->all(),RequestProducto::getRulesApp(),RequestProducto::getMessagesApp());
            if($validator->fails()){
                return response(["data"=>[$validator->errors()]],200);
            }

            $data=$request->all();
            if($request->has('unidad'))
                $data['unidad_id']=$request->get('unidad');
            if($request->has('categoria'))
                $data['categoria_id']=$request->get('categoria');

            $admin = User::find(Auth::user()->userAdminId());
            $producto = ProductoApp::permitidos()->where("barcode",$request->input("barcode"))->first();
            if(!$producto){
                $producto = new ProductoApp();
            }

            if($data["estado"] == "agregado"){
                $data["estado"] = "pendiente";
            }

            $producto->fill($data);
            $producto->usuario_id = Auth::user()->userAdminId();
            $producto->usuario_creador_id = Auth::user()->id;
            if($data["estado"] == "editado"){
                $producto->producto_id = $data["old_id"];
            }
            //$producto->estado = "pendiente";//procesado, ;
            $producto->save();

            return response(["data"=>["success"=>true,"mensaje"=>"producto creado con éxito"]],200);
        }else{
            return response(["Error"=>["unauthorized"]],401);
        }
    }


}