<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ImportacionProducto extends Model {

    protected $table = "importaciones_productos";

    protected $fillable = [
        "nombre",
        "descripcion",
        "barcode",
        "precio_costo",
        "iva",
        "utilidad",
        "stock",
        "umbral",
        "medida_venta",
        "usuario_id",
        "usuario_creador_id",
        "tags"
    ];

    protected $guarded = "id";

    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return ImportacionProducto::whereNotNull('id');
        }else {
            return ImportacionProducto::where("importaciones_productos.usuario_id",$user->userAdminId());
        }
    }

    public static function importacionesProveedor(){
        return ImportacionProducto::where("proveedor","si")->where("usuario_id",Auth::user()->id);
    }
}
