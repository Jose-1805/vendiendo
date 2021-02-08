<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class CajaHistorialEstado extends Model {

	protected $table ="cajas_historial_estados";
    protected $fillable =['estado_nuevo','estado_anterior','razon_estado','caja_id','usuario_id'];
}
