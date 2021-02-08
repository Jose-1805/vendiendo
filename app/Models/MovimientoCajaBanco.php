<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class MovimientoCajaBanco extends Model {
    protected $table = 'movimientos_cajas_bancos';
    protected $fillable = ['tipo','valor','observacion','usuario_creador_id','caja_id','cuenta_banco_id'];
    protected $guarded = "id";

}
