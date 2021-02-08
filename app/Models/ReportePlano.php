<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class ReportePlano extends Model {

	protected $table = "reportes_planos";
    protected $fillable = ['usuario_creator_id','usuario_id','seccion','campos','nombre'];

    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return ReportePlano::whereNotNull('id');
        }else if($perfil->nombre == 'administrador'){
            return ReportePlano::where("reportes_planos.usuario_id",$user->id);
        }else if($perfil->nombre == "usuario"){
            $usuario_creador = User::find($user->usuario_creador_id);
            if($usuario_creador){
                return ReportePlano::where('reportes_planos.usuario_id',$usuario_creador->id);
            }
        }
    }

}
