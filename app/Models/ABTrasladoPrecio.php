<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection as Collection;


class ABTrasladoPrecio extends Model {

    protected $connection = 'mysql_alm';
	protected $table = "traslados_precios";
	public $timestamps = false;
    protected $fillable = [
        "cantidad",
        "traslado_id",
        "historial_costo_id",
        "historial_utilidad_id"];

    protected $guarded = "id";
    
}
