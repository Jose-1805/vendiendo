<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PreguntaFrecuente extends Model {

	protected $table ="preguntas_frecuentes";
    protected $fillable =['pregunta','respuesta','embebido','enlace'];

    /*public static function getAbonos($id_compra){
        $abonos = Abono::select('*')
            ->where('tipo_abono_id',$id_compra)
            ->where('tipo_abono','compra')
            ->where('usuario_id',Auth::user()->id)
            ->orderBy("updated_at")
            ->get();
        return $abonos;
    }*/
}
