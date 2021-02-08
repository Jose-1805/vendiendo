<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class ActualizacionSistema extends Model {
    protected $table = 'actualizaciones_sistema';
    protected $fillable = [];
    protected $guarded = "id";

    public static function ultimaActualizacion(){
        return ActualizacionSistema::orderBy('fecha','desc')->first();
    }

    public function usuarioTieneActualizacion(){
        $result = Auth::user()->actualizacionesSoftware()->where('actualizaciones_sistema.id',$this->id)->first();
        if($result) return true;
        return false;
    }
}
