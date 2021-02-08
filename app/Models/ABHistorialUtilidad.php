<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection as Collection;


class ABHistorialUtilidad extends Model {

    protected $connection = 'mysql_alm';
	protected $table = "historial_utilidad";
    protected $fillable = [
        "utilidad",
        "producto_id",
        "almacen_id"];

    protected $guarded = "id";

    public function producto()
    {
        return $this->belongsTo("App\Models\ABProducto");
    }
    
}
