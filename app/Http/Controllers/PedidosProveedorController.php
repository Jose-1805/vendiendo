<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models\PedidoProveedor;
use App\Models\Producto;
use App\Models\PromocionProveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\RequestPromocionProveedor;

class PedidosProveedorController extends Controller {

    public function __construct()
    {
        $this->middleware("auth");
        $this->middleware("modPedidosProveedor");
        $this->middleware("modConfiguracion");
        $this->middleware("terminosCondiciones");
    }

    public function getIndex(){
        $pedidos = PedidoProveedor::where("proveedor_id",Auth::user()->id)->orderBy("created_at","DESC")->paginate(env("PAGINATE"));
        return view("pedidos_proveedor.index")->with("pedidos",$pedidos);
    }

    public function postUpdateEstado(Request $request){
        if($request->has("id") && $request->has("estado")){
            $pedido = PedidoProveedor::permitidos()->where("id",$request->input("id"))->first();
            if($pedido){
                $nuevosEstados = [];
                switch ($pedido->estado){
                    case 'sin procesar': $nuevosEstados = ["revisado"];
                        break;
                    case 'revisado': $nuevosEstados = ["aprobado","rechazado"];
                        break;
                    case 'aprobado': $nuevosEstados = ["enviado","cancelado"];
                        break;
                    case 'enviado': $nuevosEstados = ["recibido"];
                        break;
                }

                if(in_array($request->input("estado"),$nuevosEstados)){
                    $pedido->estado = $request->input("estado");
                    $pedido->save();
                    Session::flash("mensaje","El estado del pedido ha sido cambiado con éxito");
                    return ["success"=>true];
                }
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }

    public function postDetalle(Request $request){
        if($request->has("id")){
            $pedido = PedidoProveedor::permitidos()->where("id",$request->input("id"))->first();
            return view("pedidos_proveedor.detalle")->with("pedido",$pedido);
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }
}
