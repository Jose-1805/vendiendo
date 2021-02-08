<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection as Collection;

class TrasladoPrecio extends Model {

    protected $connection = "mysql_alm";
	protected $table = "traslados_precios";
    protected $fillable = ["cantidad","traslado_id","historial_costo_id","historial_utilidad_id"];
    public $timestamps = false;

    public function historialCosto(){
        return $this->belongsTo(ABHistorialCosto::class,'historial_costo_id');
    }

    public function historialUtilidad(){
        return $this->belongsTo(ABHistorialUtilidad::class,'historial_utilidad_id');
    }

}
