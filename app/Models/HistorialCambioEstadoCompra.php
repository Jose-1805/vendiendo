<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialCambioEstadoCompra extends Model {

	protected $table = "historialcambioestadocompra";
    protected $fillable = ["compra_id","usuario_id","estado_compra","estado_pago"];

}
