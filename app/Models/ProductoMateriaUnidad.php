<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoMateriaUnidad extends Model {

	protected $table = "producto_materia_unidad";

    public function unidad(){
        return $this->belongsTo('App\Models\Unidad');
    }
    public function materiaPrima(){
        return $this->belongsTo('App\Models\MateriaPrima');
    }

}
