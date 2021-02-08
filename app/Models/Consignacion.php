<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class Consignacion extends Model {
    protected $table = 'consignaciones';
    protected $fillable = [];
    protected $guarded = "id";



}
