<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class Notificacion extends Model {
    protected $table = 'notificaciones';
    protected $fillable = ['mensaje','link','tipo'];
    protected $guarded = "id";

    public function usuarios(){
        return $this->belongsToMany("App\User","usuarios_notificaciones","notificacion_id","usuario_id");
    }

}
