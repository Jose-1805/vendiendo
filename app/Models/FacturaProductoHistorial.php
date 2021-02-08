<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection as Collection;


class FacturaProductoHistorial extends Model {

	protected $table = "facturas_productos_historial";
    public $timestamps = false;
}
