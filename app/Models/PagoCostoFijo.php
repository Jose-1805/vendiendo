<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Caja;
use App\User;

class PagoCostoFijo extends Model {

    protected $table = 'pagos_costos_fijos';

    public static function PayFixedCost($valor){

        //$fecha_actual = date("Y"). "-". date("m") . "-" . date("d");
        //$usuario_id = Auth::user()->userAdminId();
        $caja = Caja::abierta();

        $caja->efectivo_final -= $valor;
        $caja->save();
        return $caja;
    }
    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return PagoCostoFijo::whereNotNull('id');
        }else if($perfil->nombre == 'administrador'){
            return PagoCostoFijo::where("pagos_costos_fijos.usuario_id",$user->id);
        }else if($perfil->nombre == "usuario"){
            $usuarioCreador = User::find(Auth::user()->userAdminId());
            if($usuarioCreador){
                return PagoCostoFijo::where('pagos_costos_fijos.usuario_id',$usuarioCreador->id);
            }
        }
    }
}
