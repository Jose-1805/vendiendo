<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProveedorProducto extends Model {

	protected $table ="proveedor_producto";

    public function proveedor(){
        return $this->belongsTo("App\Models\Proveedor");
    }
}
