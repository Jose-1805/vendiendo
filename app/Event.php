<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Event extends Model {

    protected $table = 'eventos';
    protected $fillable = ['titulo','descripcion','inicio','fin','color'];
    protected $hidden = ['id'];

    public static function permitidos(){
        return Event::where('eventos.usuario_id',Auth::user()->userAdminId());
    }

    public function notificacionesUsuarios(){
        return $this->belongsToMany(User::class,'notificaciones_eventos_usuarios','evento_id','usuario_id');
    }
}
