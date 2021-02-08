<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection as Collection;


class ABHistorialCosto extends Model {

    protected $connection = 'mysql_alm';
	protected $table = "historial_costos";
    protected $fillable = [
        "precio_costo_nuevo",
        "precio_costo_anterior",
        "iva_nuevo",
        "iva_anterior",
        "producto_id",
        "proveedor_id",
        "stock",
        "usuario_id"];

    protected $guarded = "id";

    public function producto()
    {
        return $this->belongsTo("App\Models\ABProducto");
    }
    
}
