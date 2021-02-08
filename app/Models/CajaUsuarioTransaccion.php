<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class CajaUsuarioTransaccion extends Model {

	protected $table ="cajas_usuarios_transacciones";
    protected $fillable =['valor','tipo','comentario','caja_usuario_id','usuario_creador_id','fecha'];

    public function creador(){
        return $this->belongsTo(User::class,"usuario_creador_id");
    }

    public static function totalEnvioACaja($caja_usuario){
        $relaciones = CajaUsuarioTransaccion::where("caja_usuario_id",$caja_usuario)->get();
        $aux =  0;
        foreach ($relaciones as $r){
            $aux += $r->valor;
        }
        return $aux;
    }
}
