<?php namespace App\Models;

use App\General;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class Cliente extends Model {
    protected $table = 'clientes';
    protected $fillable = ["nombre","tipo_identificacion","identificacion","telefono","correo","direccion"];
    protected $guarded = "id";

    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'administrador'){
            if(Auth::user()->bodegas == 'si')
                return Cliente::where("usuario_id",$user->userAdminId());
            return Cliente::where("usuario_id",$user->id);
        }else if($perfil->nombre == "usuario"){
            $usuario_creador = User::find($user->usuario_creador_id);
            if($usuario_creador){
                return Cliente::where('usuario_id',$usuario_creador->id);
            }
        }
        return false;
    }
    public static function savePoins($total_factura,$cliente_id){
        $user = User::where('id',Auth::user()->userAdminId())->first();

        if ($user->pesos_venta > 0 && $user->puntos_pago > 0){
            $numero_puntos = ($total_factura * $user->puntos_venta) / $user->pesos_venta;
            $valor_puntos = (round($numero_puntos) * $user->pesos_pago) / $user->puntos_pago;
        }else{
            $numero_puntos = 0;
            $valor_puntos = 0;
        }
        //actualizar numero de puntos y fecha de caducidad
        $cliente = Cliente::permitidos()->where("id",$cliente_id)->first();
        $cliente->valor_puntos += $valor_puntos;
        if ($user->caducidad_puntos >0 && $user->caducidad_puntos !='' )
            $cliente->fecha_caducidad_puntos = General::sumarDias($user->caducidad_puntos);
        $cliente->save();

        return $cliente;
    }
    public static function unSavePoins($total_factura,$cliente_id){
        $user = User::where('id',Auth::user()->userAdminId())->first();

        if ($user->pesos_venta > 0 && $user->puntos_pago > 0){
            $numero_puntos = ($total_factura * $user->puntos_venta) / $user->pesos_venta;
            $valor_puntos = (round($numero_puntos) * $user->pesos_pago) / $user->puntos_pago;
        }else{
            $numero_puntos = 0;
            $valor_puntos = 0;
        }
        //actualizar numero de puntos y fecha de caducidad
        $cliente = Cliente::permitidos()->where("id",$cliente_id)->first();
        $cliente->valor_puntos -= $valor_puntos;
        /*if ($user->caducidad_puntos >0 && $user->caducidad_puntos !='' )
            $cliente->fecha_caducidad_puntos = General::sumarDias($user->caducidad_puntos);*/
        $cliente->save();

        return $cliente;
    }

    public function tokenPuntos(){
        return $this->hasMany(TokenPuntos::class);
    }
    public function facturas(){
        return $this->hasMany(Factura::class);
    }

    public function permitirEliminar(){
        if(count($this->facturas) || count($this->tokenPuntos)){
            return false;
        }
        return true;
    }
}
