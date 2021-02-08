<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ControlEmpleados extends Model {

	protected $table = "control_empleados";
    protected $fillable = [
    						'id',
							'usuario_creador_id',
							'usuario_id',
							'nombre',
							'cedula',
							'codigo_barras',
							'huella_digital',
							'estado_empleado',
							'created_at',
							'updated_at'
						];

	public function userID(){
    	return $this->belongsTo('App\User','usuario_id','id');
    }

	public static function permitidos(){
		$user = Auth::user();
		$perfil=$user->perfil;
			if($perfil->nombre == 'superadministrador'){
			    return ControlEmpleados::whereNotNull('control_empleados.id');
			}else if($perfil->nombre == 'administrador'){
			    return ControlEmpleados::where("control_empleados.usuario_id",$user->userAdminId());
			}else if($perfil->nombre == "usuario"){
			    $usuario_creador = User::find($user->usuario_creador_id);
			    if($usuario_creador){
			        return ControlEmpleados::where('control_empleados.usuario_id',$usuario_creador->id);
			    }
			}
	}
}

