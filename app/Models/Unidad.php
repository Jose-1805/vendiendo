<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class Unidad extends Model {

	protected $table = 'unidades';

    protected $fillable = ['nombre','sigla'];

    protected $guarded = ['id'];

    public static function unidadesPermitidas(){

        $user = Auth::user();
        $perfil = $user->perfil;

        if($perfil->nombre == "superadministrador") {
            return Unidad::where("superadministrador","si");
        }else if($perfil->nombre == "administrador") {
            if($user->bodegas == 'si' && $user->admin_bodegas == 'no')
                return Unidad::where("usuario_id", $user->userAdminId());

            return Unidad::where("usuario_id", $user->id);
        }else if($perfil->nombre == "usuario"){
            $usuarioCreador = User::find($user->usuario_creador_id);
            if($usuarioCreador){
                return Unidad::where("usuario_id", $usuarioCreador->id);
            }
        }

        return Unidad::whereNull("id");
    }
    public function Productos(){
        return $this->hasMany('App\Models\Producto');
    }
    
    public static function lista(){
        $unidades = Unidad::unidadesPermitidas()->get();
        $unidades_aux = [];
        foreach ($unidades as $c){
            $unidades_aux[$c->id] = $c->nombre;
        }
        return $unidades_aux;
    }

    public static function listaSuperadministrador(){
        $unidades = Unidad::where("superadministrador","si")->get();
        $unidades_aux = [];
        foreach ($unidades as $c){
            $unidades_aux[$c->id] = $c->nombre;
        }
        return $unidades_aux;
    }

    public static function lista_api(){
        $unidades = Unidad::unidadesPermitidas()->get();
        $unidades_aux = [];
        $i=0;
        foreach ($unidades as $c){
            //$unidades_aux[$c->id] = $c->nombre;
            $unidades_aux[$i] = [ "id" => $c->id, "nombre" => $c->nombre];
            $i++;
        }
        return $unidades_aux;
    }
}
