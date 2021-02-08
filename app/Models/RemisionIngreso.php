<?php namespace App\Models;

use App\User;
use App\Models\ProductoHistorial;
use App\Models\Abono;
use App\Models\Caja;
use App\Models\CostoFijo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection as Collection;


class RemisionIngreso extends Model {

	protected $table = "remisiones_ingreso";
    protected $fillable = [
        "numero",
        "usuario_id",
        "usuario_creador_id",
    ];
    protected $dates = ['created_at', 'updated_at'];


    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return RemisionIngreso::whereNotNull('id');
        }else if($perfil->nombre == 'administrador'){
            return RemisionIngreso::where("remisiones_ingreso.usuario_id",$user->id);
        }else if($perfil->nombre == "usuario"){
            $usuario_creador = User::find($user->usuario_creador_id);
            if($usuario_creador){
                return RemisionIngreso::where('remisiones_ingreso.usuario_id',$usuario_creador->id);
            }
        }
    }

    public function usuario(){
        return $this->belongsTo("App\User");
    }

    public function usuarioCreador(){
        return $this->belongsTo("App\User","usuario_creador_id","id");
    }

    public function productos(){
        return $this->belongsToMany("App\Models\Producto","remisiones_ingreso_productos","remision_ingreso_id","producto_id");
    }

    public function materiasPrimas(){
        return $this->belongsToMany("App\Models\MateriaPrima","remisiones_ingreso_materias_primas","remision_ingreso_id","materia_prima_id");
    }

    public static function ultimaRemisionIngreso(){
        return RemisionIngreso::permitidos()->orderBy("id","DESC")->orderBy("created_at","DESC")->orderBy("numero","DESC")->first();
    }
}
