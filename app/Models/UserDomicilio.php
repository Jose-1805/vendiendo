<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDomicilio extends Model {

	protected $table = "user_domicilio";
    protected $fillable = ['nombre','identificacion','direccion','telefono','correo','latitud','longitud'];
    protected $hidden = ['password', 'api_key'];

    public static function savePosition($api_key,$latitud,$longitud){
        $user = UserDomicilio::where('api_key',$api_key)->first();

        $user->latitud = $latitud;
        $user->longitud = $longitud;
        $user->save();

        return $user;
    }

}
