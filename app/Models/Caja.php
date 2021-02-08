<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class Caja extends Model {

	protected $table ="caja";
    protected $fillable =['efectivo_inicial','efectivo_final','consignacion','usuario_id','usuario_creador_id','fecha','estado'];

    public static function abierta(){
        if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si') {
            return Caja::cajasPermitidas()->where("estado", "abierta")->where('principal_ab', 'si')->first();
        }else if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'no') {
            $almacen = Almacen::permitidos()->where('administrador',Auth::user()->id)->first();
            if($almacen){
                return Caja::cajasPermitidas()->where("estado", "abierta")->where('almacen', 'si')->where('almacen_id',$almacen->id)->first();
            }
            return false;
        }

        return Caja::cajasPermitidas()->where("estado","abierta")->first();
    }

    public static function cajasPermitidas(){

        $user = Auth::user();
        $perfil = $user->perfil;

        //dd(Auth::user()->userAdminId());

        if($perfil->nombre == "administrador") {
            if($user->bodegas == 'si' && $user->admin_bodegas == 'no')
                return Caja::where("usuario_id",$user->userAdminId());
            return Caja::where("usuario_id", $user->id);
        }else if($perfil->nombre == "usuario"){
            $usuarioCreador = User::find(Auth::user()->userAdminId());
            if($usuarioCreador){
                //dd($usuarioCreador->id);
                return Caja::where("usuario_id", $usuarioCreador->id);
            }

        }
        //dd(Caja::where("usuario_id", 0));
        return Caja::where("usuario_id", 0);
    }

    public static function cajasPermitidasAlmacen(){

        $user = Auth::user();
        $perfil = $user->perfil;
        if($user->bodegas == 'no' || $perfil->nombre != 'administrador' || $user->admin_bodegas == 'si')return new Caja();

        $almacen = Almacen::where('administrador',$user->id)->first();
        if(!$almacen)return false;

        return Caja::where("almacen", 'si')->where('almacen_id',$almacen->id);
    }

    public function operacionesCaja(){
        return $this->hasMany(OperacionCaja::class,'caja_id','id');
    }
    public static function updateCaja($tipo_movimiento,$valor){

        $nuevo_efectivo='';
        $caja = Caja::abierta();

        switch ($tipo_movimiento){
            case 'Adicionar':
                $nuevo_efectivo = $caja->efectivo_final + $valor;
                $caja->update([
                    'efectivo_final' => $nuevo_efectivo
                ]);
                break;
            case 'Reducir':
                $nuevo_efectivo = $caja->efectivo_final - $valor;
                $caja->update([
                    'efectivo_final' => $nuevo_efectivo
                ]);
                break;
            case 'Consignacion':
                $caja->update([
                    'consignacion' => $valor
                ]);
                break;
            default:
                break;

        }
        return $caja;

    }

    public function valorRetiros(){
        return MovimientoCajaBanco::where("caja_id",$this->id)->where("tipo","Retiro")->get()->sum("valor");
    }

    public function valorConsignaciones(){
        return MovimientoCajaBanco::where("caja_id",$this->id)->where("tipo","Consignación")->get()->sum("valor");
    }

}
