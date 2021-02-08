<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
class Sms extends Model {

	protected $table = "sms";
    protected $fillable = ['usuario_creator_id','usuario_id','mensaje','titulo','f_h_programacion','telefonos','estado'];

    public function SmsHistoriales(){
        return $this->hasMany(SmsHistorial::class,'sms_id','id');
    }
    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return Sms::whereNotNull('id');
        }else if($perfil->nombre == 'administrador'){
            return Sms::where("sms.usuario_id",$user->id);
        }else if($perfil->nombre == "usuario"){
            $usuario_creador = User::find($user->usuario_creador_id);
            if($usuario_creador){
                return Sms::where('sms.usuario_id',$usuario_creador->id);
            }
        }
    }

    public static function countSmsSendOk(){
        $admin = User::find(Auth::user()->userAdminId());
        $data_plan = $admin->planes()->select("planes_usuarios.*")->where("planes_usuarios.estado","activo")->first();
        $fechaInicioPlan = date("Y-m-d",strtotime($data_plan->created_at));
        $diaInicioPlan = intval(date("d",strtotime($fechaInicioPlan)));
        $diaHoy = intval(date("d"));

        if($diaInicioPlan < 10)$diaInicioPlan = "0".$diaInicioPlan;
        $fechaInicio = date("Y-m")."-".$diaInicioPlan;

        //si el día actual es menor al día de inicio del plan
        //se consultan los mensajes enviados entre el día de inicio del plan en el mes pasado hasta un mes despues
        if($diaHoy < $diaInicioPlan){
            $fechaInicio = date("Y-m-d",strtotime("-1 month",strtotime($fechaInicio)));
        }

        $fechaFin = date("Y-m-d",strtotime("+1 month",strtotime($fechaInicio)));

        $sms = Sms::permitidos()/*->select("sms_consulta.*")
            ->join("sms_consumo","sms.id","=","sms_consumo.sms_id")
            ->join("sms_consulta","sms_consumo.id","=", "sms_consulta.sms_consumo_⁯id")
            ->where("sms_consulta.status","<>","fallido")*/
            ->whereBetween("sms.f_h_programacion",[$fechaInicio,$fechaFin])
            ->get();
        $cantidad = 0;
        foreach ($sms as $s){
            $cantidad += count(explode("-",$s->telefonos));
        }
        return $cantidad;
    }

}
