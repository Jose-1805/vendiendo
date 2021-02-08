<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Sede extends Model {

    protected $table = "sedes";
    protected $fillable = ['nombre','direccion','municipio_id','departamento_id','descripcion','latitud','longitud','estado','usuario_id'];

    public function usuario(){
        return $this->belongsTo(User::class);
    }    
    public function municipio(){
        return $this->belongsTo(Municipio::class);
    }
}
