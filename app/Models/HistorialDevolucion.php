<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class HistorialDevolucion extends Model {

	protected $table = "historial_devoluciones";
    protected $fillable = [
        'compra_id',
        'elemento_id',
        'cantidad',
        'tipo_elemento',
        'motivo',
        'usuario_id',
        'proveedor_id'
    ];

    protected $guarded = ['id'];
    public static function permitidos(){
        $user = Auth::user();
        $perfil = $user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return HistorialDevolucion::whereNotNull('id');
        }else if($perfil->nombre == 'administrador'){
            return HistorialDevolucion::where("usuario_id",$user->id);
        }else if($perfil->nombre == "usuario"){
            $usuario_creador = User::find($user->usuario_creador_id);
            if($usuario_creador){
                return HistorialDevolucion::where('usuario_id',$usuario_creador->id);
            }
        }
    }

}
