<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProveedorMateriaPrima extends Model {

    protected $table = 'proveedores_materias_primas';

    public function proveedor(){
        return $this->belongsTo("App\Models\Proveedor");
    }
}
