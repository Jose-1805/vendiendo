<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class CotizacionHistorial    extends Model {

	protected $table ="cotizaciones_historial";
    protected $fillable =['observacion','usuario_id','cotizacion_id'];

    public function usuario(){
        return $this->belongsTo(User::class);
    }

}
