<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models\Producto;
use App\Models\PromocionProveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\RequestPromocionProveedor;

class PromocionesProveedorController extends Controller {

    public function __construct()
    {
        $this->middleware("auth");
        $this->middleware("modPromocionesProveedor");
        //$this->middleware("modConfiguracion");
        $this->middleware("terminosCondiciones");
    }

    public function getIndex(){
        $promociones = PromocionProveedor::permitidos()->orderBy("estado","ASC")->orderBy("promociones_proveedores.created_at","DESC")->get();
        return view("promociones_proveedores.index")->with("promociones",$promociones);
    }

    public function postStore(RequestPromocionProveedor $request){
        $data = $request->all();
        $producto = Producto::productosProveedor()->where("id",$data["producto"])->first();
        if($producto) {
            if($producto->estado == "Activo") {
                if($producto->tienePromocionFecha($request->input("fecha_inicio"))){
                    return response(["error"=>["El producto seleccionado ya tiene una promoción para la fecha ".$request->input("fecha_inicio").", para crear esta promoción debe desactivar la promoción que incluye la fecha mencionada."]],422);
                }
                if($producto->tienePromocionFecha($request->input("fecha_fin"))){
                    return response(["error"=>["El producto seleccionado ya tiene una promoción para la fecha ".$request->input("fecha_inicio").", para crear esta promoción debe desactivar la promoción que incluye la fecha mencionada."]],422);
                }

                if($producto->tienePromocionFechaInicioFin($request->input("fecha_inicio"),$request->input("fecha_fin"))){
                    return response(["error"=>["El producto seleccionado ya tiene una promoción activa entre el rango de fechas (".$request->input("fecha_inicio")." - ".$request->input("fecha_fin")."), para crear esta promoción debe desactivar la promoción que se incluye en el rango mencionado."]],422);
                }
                $promocion = new PromocionProveedor();
                $data["valor_actual"]=$producto->precio_costo;
                $data["estado"]="Activo";
                $data["producto_id"]=$producto->id;
                $promocion->fill($data);
                $promocion->save();
                Session::flash("mensaje","Promoción creada con exito");
                return ["success"=>true];
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }

    public function postUpdateEstado(Request $request){
        if($request->has("id") && $request->has("estado")){
            $promocion = PromocionProveedor::permitidos()->where("promociones_proveedores.id",$request->input("id"))->first();
            if($promocion){
                if($request->input("estado") == "activo" || $request->input("estado") == "inactivo") {
                    if($request->input("estado") == "activo"){
                        $producto = $promocion->producto;
                        if($producto->tienePromocionFecha($promocion->fecha_inicio)){
                            return response(["error"=>["El producto seleccionado ya tiene una promoción activa para la fecha ".$promocion->fecha_inicio.", para crear esta promoción debe desactivar la promoción que incluye la fecha mencionada."]],422);
                        }
                        if($producto->tienePromocionFecha($promocion->fecha_fin)){
                            return response(["error"=>["El producto seleccionado ya tiene una promoción activa para la fecha ".$promocion->fecha_inicio.", para crear esta promoción debe desactivar la promoción que incluye la fecha mencionada."]],422);
                        }

                        if($producto->tienePromocionFechaInicioFin($promocion->fecha_inicio,$promocion->fecha_fin)){
                            return response(["error"=>["El producto seleccionado ya tiene una promoción activa entre el rango de fechas (".$promocion->fecha_inicio." - ".$promocion->fecha_fin."), para crear esta promoción debe desactivar la promoción que se incluye en el rango mencionado."]],422);
                        }
                    }
                    $promocion->estado = $request->input("estado");
                    $promocion->save();
                    return ["success" => true];
                }
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }
}
