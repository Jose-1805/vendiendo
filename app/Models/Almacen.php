<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class Almacen extends Model {

    protected $connection = 'mysql_alm';
    protected $table = 'almacenes';
    protected $fillable = ['nombre','prefijo','direccion','telefono','latitud','longitud','administrador','usuario_id','usuario_creador_id'];
    protected $guarded = "id";

    public static function permitidos($incluir_mi_almacen = true){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return Almacen::whereNotNull('id');
        }else {
            if($incluir_mi_almacen) {
                return Almacen::where("almacenes.usuario_id", $user->userAdminId());
            }else{
                $almacen = Auth::user()->almacenActual();
                if($almacen)
                    return Almacen::where("almacenes.usuario_id", $user->userAdminId())->where('almacenes.id','<>',$almacen->id);
                else
                    return Almacen::where("almacenes.usuario_id", $user->userAdminId());
            }
        }
    }
}
