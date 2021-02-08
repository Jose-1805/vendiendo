<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ImportacionMateriaPrima extends Model {

    protected $table = "importaciones_materias_primas";

    protected $fillable = [
        "nombre",
        "descripcion",
        "codigo",
        "valor",
        "umbral",
        "cantidad",
        "usuario_id",
        "usuario_creador_id",
        "estado"
    ];

    protected $guarded = "id";

    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return ImportacionMateriaPrima::whereNotNull('id');
        }else {
            return ImportacionMateriaPrima::where("importaciones_materias_primas.usuario_id",$user->userAdminId());
        }
    }

    public static function importacionesProveedor(){
        return ImportacionMateriaPrima::where("proveedor","si")->where("usuario_id",Auth::user()->id);
    }
}
