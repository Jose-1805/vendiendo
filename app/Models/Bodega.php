<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class Bodega extends Model {

    protected $connection = 'mysql_alm';
    protected $table = 'bodegas';
    protected $fillable = ['nombre','direccion','usuario_id','usuario_creador_id'];
    protected $guarded = "id";

    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return Bodega::whereNotNull('id');
        }else {
            return Bodega::where("bodegas.usuario_id",$user->userAdminId());
        }
    }
}
