<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PedidoProveedor extends Model {
    protected $table = 'pedidos_proveedor';
    protected $fillable = ['consecutivo','valor_total','proveedor_id','administrador_id'];
    protected $guarded = "id";

    public static function permitidos(){
        $perfil = Auth::user()->perfil;
        if($perfil->nombre == "administrador" || $perfil->nombre == "usuario"){
            return PedidoProveedor::where("administrador_id",Auth::user()->userAdminId());
        }else{
            return PedidoProveedor::where("proveedor_id",Auth::user()->id);
        }
    }

    public function generarConsecutivo(){
        $pedido = PedidoProveedor::where("proveedor_id",$this->proveedor_id)->orderBy("consecutivo","DESC")->first();
        if($pedido){
            $this->consecutivo = $pedido->consecutivo + 1;
        }else{
            $this->consecutivo = 1;
        }
    }

    public function productos(){
        return $this->belongsToMany(Producto::class,"pedidos_proveedor_productos","pedido_proveedor_id","producto_id");
    }

    public function proveedor(){
        return $this->belongsTo(User::class,"proveedor_id");
    }

    public function administrador(){
        return $this->belongsTo(User::class,"administrador_id");
    }
}
