<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class TipoPago extends Model {

    protected $connection = 'mysql';
    protected $table = "tipos_pago";
    protected $fillable = [];

}
