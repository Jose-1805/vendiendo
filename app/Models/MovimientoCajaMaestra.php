<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class MovimientoCajaMaestra extends Model {
    protected $table = 'movimientos_cajas_maestras';
    protected $fillable = [];
    protected $guarded = "id";

}
