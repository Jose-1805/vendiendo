<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class CuentaBancaria extends Model {
    protected $table = 'cuentas_bancos';
    protected $fillable = ['titular','numero','saldo','banco_id','usuario_id','usuario_creador_id'];
    protected $guarded = "id";
    
    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return CuentaBancaria::where('id','<>','0');
        }else if($perfil->nombre == 'administrador'){
            if($user->bodegas == 'si' && $user->admin_bodegas == 'no')
                return CuentaBancaria::where("usuario_id",$user->userAdminId());
            return CuentaBancaria::where("usuario_id",$user->id);
        }else if($perfil->nombre == "usuario"){
            $usuario_creador = User::find($user->usuario_creador_id);
            if($usuario_creador){
                return CuentaBancaria::where('usuario_id',$usuario_creador->id);
            }
        }
    }

    public static function lista(){
        $cuentas = CuentaBancaria::permitidos()->select("cuentas_bancos.*","bancos.nombre")
            ->join("bancos","cuentas_bancos.banco_id","=","bancos.id")->get();
        $cuentas_aux = [];
        foreach ($cuentas as $c){
            $cuentas_aux[$c->id] = $c->numero." (".$c->nombre.")";
        }
        return $cuentas_aux;
    }

    public function consignaciones(){
        return $this->hasMany(Consignacion::class,"cuenta_id");
    }
}
