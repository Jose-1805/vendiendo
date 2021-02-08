<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection as Collection;


class ABBodegaStockProducto extends Model {

    protected $connection = 'mysql_alm';
	protected $table = "bodegas_stock_productos";
    protected $fillable = [
        "stock",
        "bodega_id",
        "producto_id",
    ];

    protected $guarded = "id";
    
}
