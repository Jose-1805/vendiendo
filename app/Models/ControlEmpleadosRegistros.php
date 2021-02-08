<?php namespace App\models;

use Illuminate\Database\Eloquent\Model;

class ControlEmpleadosRegistros extends Model {
	protected $table = "control_empleados_registros";
    protected $fillable = [
    						'id',
							'control_empleado_id',
							'estado_sesion',
							'fecha_llegada',
							'fecha_salida',
							'created_at'
						];

	public function empleadoID(){
    	return $this->belongsTo('App\Models\ControlEmpleados','control_empleado_id','id');
    }

}


