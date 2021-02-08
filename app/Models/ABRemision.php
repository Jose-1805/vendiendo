<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection as Collection;


class ABRemision extends Model {

    protected $connection = 'mysql_alm';
	protected $table = "remisiones";
    protected $fillable = ["numero","fecha_vencimiento","estado","almacen_id","cliente_id","usuario_id","usuario_creador_id","factura_id"];


    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return ABRemision::whereNotNull('id');
        }else {
            return ABRemision::where("remisiones.usuario_id",$user->userAdminId());
        }
    }

    public function factura(){
        return $this->belongsTo("App\Models\ABFactura");
    }

    public function cliente(){
        return $this->belongsTo("App\Models\Cliente");
    }

    public function usuario(){
        return $this->belongsTo("App\User");
    }

    public function usuarioCreador(){
        return $this->belongsTo("App\User","usuario_creador_id","id");
    }

    public function historialCostos(){
        return $this->belongsToMany("App\Models\ABHistorialCosto","remisiones_historial_costos","remision_id", "historial_costo_id");
    }

    public function cantidadRemitida($idProducto){
        $cant = 0;
        $producto = $this->historialCostos()
            ->join('productos','historial_costos.producto_id','=','productos.id')
            ->select("remisiones_historial_costos.cantidad")->where("productos.id",$idProducto)->first();
        if($producto)$cant = $producto->cantidad;
        return $cant;
    }

    public static function ultimaRemision(){
        return ABRemision::permitidos()->orderBy("id","DESC")->orderBy("created_at","DESC")->orderBy("numero","DESC")->first();
    }

    public function getValor(){
        $valor = 0;
        $productos = $this->historialCostos()
            ->join('productos','historial_costos.producto_id','=','productos.id')
            ->join('historial_utilidad','remisiones_historial_costos.historial_utilidad_id','=','historial_utilidad.id')
            ->select("historial_costos.precio_costo_nuevo","historial_costos.iva_nuevo","remisiones_historial_costos.cantidad","historial_utilidad.utilidad")->get();
        foreach ($productos as $p){
            $precio = $p->precio_costo_nuevo + (($p->precio_costo_nuevo * $p->utilidad)/100);
            $valor += ($precio + (($precio * $p->iva_nuevo)/100))*$p->cantidad;
        }
        return $valor;
    }
}
