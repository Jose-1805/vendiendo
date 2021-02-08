<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Resolucion extends Model {

    protected $table = 'resoluciones';
    protected $fillable = ["numero","prefijo","fecha","inicio","fin","fecha_vencimiento","fecha_notificacion","numero_notificacion","estado","usuario_id","usuario_creador_id"];

    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return Resolucion::whereNotNull('id');
        }else if($perfil->nombre == 'administrador'){
            if(Auth::user()->bodegas == 'si')
                return Resolucion::where("usuario_id",$user->userAdminId());

            return Resolucion::where("usuario_id",$user->id);
        }else if($perfil->nombre == "usuario"){
            $usuario_creador = User::find($user->usuario_creador_id);
            if($usuario_creador){
                return Resolucion::where('usuario_id',$usuario_creador->id);
            }
        }
    }

    public function usuario(){
        return $this->belongsTo(User::class);
    }

    public function isLast(){
        $user = Auth::user();
        $perfil=$user->perfil;
        $usuario = null;
        if($perfil->nombre == 'administrador'){
            $usuario = $user;
        }else{
            $usuario = User::find(Auth::user()->usuario_creador_id);
        }
        $last = Resolucion::where("usuario_id",$usuario->id)->orderBy("created_at","DESC")->orderBy("id","DESC")->first();
        if($last){
            if($last->id == $this->id){
                return true;
            }
        }
        return false;
    }

    public static function resolucionEnEspera($inicio){
        return Resolucion::permitidos()->where("estado","en espera")->where("inicio",$inicio)->first();
    }

    public static function getActiva(){
        return Resolucion::permitidos()->where("estado","activa")->first();
    }

    public static function activarEnEspera(){
        $activa = Resolucion::getActiva();
        if(!$activa) {
            $resolucion = Resolucion::permitidos()->where("estado", "en espera")->orderBy("id")->orderBy("created_at")->orderBy("inicio")->first();
            if ($resolucion) {
                $resolucion->estado = "activa";
                $resolucion->save();
            }
        }
    }

    /**
     * funcion a editar cuando el modulo de facturas se desarrolle
     */
    public function facturas(){
        return $this->hasMany("App\Models\Factura");
    }
}
