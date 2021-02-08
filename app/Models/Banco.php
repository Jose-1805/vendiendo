<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class Banco extends Model {
    protected $table = 'bancos';
    protected $fillable = [];
    protected $guarded = "id";

}
