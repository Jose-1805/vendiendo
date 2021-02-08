<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection as Collection;


class ProductoApp extends Model {

    protected $table = "productos_app";
    protected $fillable = [
        "nombre",
        "stock",
        "descripcion",
        "barcode",
        "usuario_id",
        "usuario_creador_id",
        "unidad_id",
        "categoria_id",
        "medida_venta",
        "estado"];

    protected $guarded = "id";

    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return ProductoApp::whereNotNull('id');
        }else if($perfil->nombre == 'administrador'){
            return ProductoApp::where("productos_app.usuario_id",$user->id);
        }else if($perfil->nombre == "usuario"){
            $usuario_creador = User::find($user->usuario_creador_id);
            if($usuario_creador){
                return ProductoApp::where('productos_app.usuario_id',$usuario_creador->id);
            }
        }
    }

    public function unidad(){
        return $this->belongsTo("App\Models\Unidad");
    }

    public function categoria(){
        return $this->belongsTo("App\Models\Categoria");
    }
}
