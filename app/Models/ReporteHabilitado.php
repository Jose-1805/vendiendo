<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class ReporteHabilitado extends Model {
    protected $table = 'reportes_habilitados';
    protected $fillable = [];
    protected $guarded = "id";

}
