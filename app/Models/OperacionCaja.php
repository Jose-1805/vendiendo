<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Caja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OperacionCaja extends Model {

	protected $table = "operaciones_caja";
    protected $fillable = ['caja_id','usuario_id','fecha','valor','tipo_movimiento','comentario'];

    public static function saveOperacionCaja(Request $request){

        $fecha_actual = date("Y"). "-". date("m") . "-" . date("d");
        $caja = Caja::cajasPermitidas()->where('fecha',$fecha_actual)->take(1)->first();
        $user = Auth::user();

        $fecha_hora_actual = date("Y-m-d H:i:s");

        $operacion = new OperacionCaja();
        $operacion->caja_id = $caja->id;
        $operacion->usuario_id = $user->id;
        $operacion->fecha = $fecha_hora_actual;
        $operacion->valor = $request->get('valor');
        $operacion->tipo_movimiento = $request->get('tipo_movimiento');
        $operacion->comentario = $request->get('comentario');
        $operacion->save();

        return $operacion;
    }

}
