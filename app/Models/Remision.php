<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection as Collection;


class Remision extends Model {

	protected $table = "remisiones";
    protected $fillable = ["numero","fecha_vencimiento","estado","cliente_id","usuario_id","usuario_creador_id","factura_id"];


    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return Remision::whereNotNull('id');
        }else if($perfil->nombre == 'administrador'){
            return Remision::where("remisiones.usuario_id",$user->id);
        }else if($perfil->nombre == "usuario"){
            $usuario_creador = User::find($user->usuario_creador_id);
            if($usuario_creador){
                return Remision::where('remisiones.usuario_id',$usuario_creador->id);
            }
        }
    }

    public function factura(){
        return $this->belongsTo("App\Models\Factura");
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

    public function productos(){
        return $this->belongsToMany("App\Models\Producto","productos_remisiones");
    }

    public function cantidadRemitida($idProducto){
        $cant = 0;
        $producto = $this->productos()->select("productos_remisiones.cantidad")->where("productos.id",$idProducto)->first();
        if($producto)$cant = $producto->cantidad;
        return $cant;
    }

    public static function ultimaRemision(){
        return Remision::permitidos()->orderBy("id","DESC")->orderBy("created_at","DESC")->orderBy("numero","DESC")->first();
    }

    public function getValor(){
        $valor = 0;
        $productos = $this->productos()->select("productos_remisiones.*")->get();
        foreach ($productos as $p){
            $precio = $p->precio_costo + (($p->precio_costo * $p->utilidad)/100);
            $valor += ($precio + (($precio * $p->iva)/100))*$p->cantidad;
        }
        return $valor;
    }
}
