<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class Cajas extends Model {

	protected $table ="cajas";
    protected $fillable =['nombre','prefijo','estado','usuario_id','usuario_creador_id'];

    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return Cajas::where('id','<>','null');
        }else if($perfil->nombre == 'administrador'){
            return Cajas::where("cajas.usuario_id",$user->id);
        }else if($perfil->nombre == "usuario"){
            $usuario_creador = User::find($user->usuario_creador_id);
            if($usuario_creador){
                return Cajas::where('cajas.usuario_id',$usuario_creador->id);
            }
        }
    }

    public static function permitidosAlmacen(){
        $user = Auth::user();
        $perfil=$user->perfil;

        if($perfil->nombre == 'administrador' && $user->bodegas == 'si' && $user->admin_bodegas == 'no'){
            $almacen = Almacen::where('administrador',$user->id)->first();
            if($almacen) {
                return Cajas::where("cajas.almacen", 'si')->where('cajas.almacen_id',$almacen->id);
            }
        }
        return Cajas::where("cajas.usuario_id",'0');//retorna la coleccion vacia
    }

    public function usuarios(){
        return $this->belongsToMany(User::class,"cajas_usuarios","caja_id","usuario_id");
    }

    public function historial(){
        return $this->hasMany(CajaHistorialEstado::class,"caja_id");
    }

    public function ultimoHistorial(){
        return $this->historial()->orderBy("created_at","DESC")->first();
    }

    public function relacionUsuarioActual(){
        return $this->usuarios()->select("cajas_usuarios.valor_inicial","cajas_usuarios.valor_final","usuarios.*")->where("cajas_usuarios.estado","activo")->first();
    }

    public function permitirAccion(){
        if(count($this->usuarios)){
            return false;
        }

        return true;
    }

    public function transacciones(){
        return CajaUsuarioTransaccion::join("cajas_usuarios","cajas_usuarios_transacciones.caja_usuario_id","=","cajas_usuarios.id")
            ->join("cajas","cajas_usuarios.caja_id","=","cajas.id")->where("cajas.id",$this->id);
    }

    public function cerrar(){
        $this->estado = "cerrada";
    }

    public static function cajasUsuariosActivo(){
        return Cajas::permitidos()->join("cajas_usuarios","cajas.id","=","cajas_usuarios.caja_id")
            ->where("cajas_usuarios.estado","activo");
    }
}
