<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class Anuncio extends Model {
    protected $table = 'anuncios';
    protected $fillable = ['titulo','descripcion','desde','hasta','valor','contacto','categoria_id','otras','usuario_id'];
    protected $guarded = "id";
    
    public static function permitidos(){
        return Anuncio::where("anuncios.usuario_id",Auth::user()->id);
    }

    public function categoria(){
        return $this->belongsTo(Categoria::class);
    }
}
