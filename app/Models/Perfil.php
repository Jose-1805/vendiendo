<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perfil extends Model {

    protected $table = 'perfiles';

    public static function admin_user(){
        return Perfil::where('nombre','administrador')->orWhere('nombre','usuario')->get();
    }
}