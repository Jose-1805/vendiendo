<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection as Collection;


class ABAlmacenStockProducto extends Model {

    protected $connection = 'mysql_alm';
	protected $table = "almacenes_stock_productos";
    protected $fillable = [
        "stock",
        "almacen_id",
        "producto_id",
    ];

    protected $guarded = "id";
    
}
