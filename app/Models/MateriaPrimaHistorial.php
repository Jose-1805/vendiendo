<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection as Collection;


class MateriaPrimaHistorial extends Model {

	protected $table = "materias_primas_historial";
    protected $fillable = [
        "precio_costo_nuevo",
        "precio_costo_anterior",
        "materia_prima_id",
        "proveedor_id",
        "stock",
        "usuario_id"];

    protected $guarded = "id";

    public function materiaPrima()
    {
        return $this->belongsTo(MateriaPrima::class,"materia_prima_id");
    }
    
}
