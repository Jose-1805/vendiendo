<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection as Collection;


class Pqrs extends Model {

	protected $table = "pqrs";
    protected $fillable = ["tipo_identificacion","identificacion","nombre","email","direccion","telefono","tipo","queja","usuario_id"];


    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return Pqrs::whereNotNull('id');
        }else if($perfil->nombre == 'administrador'){
            return Pqrs::where("pqrs.usuario_id",$user->id);
        }else if($perfil->nombre == "usuario"){
            $usuario_creador = User::find($user->usuario_creador_id);
            if($usuario_creador){
                return Pqrs::where('pqrs.usuario_id',$usuario_creador->id);
            }
        }
    }

    public function usuario(){
        return $this->belongsTo("App\User");
    }
}
