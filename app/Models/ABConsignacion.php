<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class ABConsignacion extends Model {
    protected $connection = 'mysql_alm';
    protected $table = 'consignaciones';
    protected $fillable = [];
    protected $guarded = "id";



}
