<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model {

    protected $table = 'modulos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre', 'seccion'];

    public function funciones(){
        return $this->hasMany("App\Models\Funcion");
    }

    public function allTareas(){
        return Tarea::select("tareas.*")
            ->join("funciones_tareas","tareas.id","=","funciones_tareas.tarea_id")
            ->join("funciones","funciones_tareas.funcion_id","=","funciones.id")
            ->join("modulos","funciones.modulo_id","=","modulos.id")
            ->where("modulos.id",$this->id)
            ->groupBy("tareas.nombre")->get();
    }

    public function fechaCaducidad($idUsuario){
        $usuario_modulo = UsuarioModulo::where("usuario_id",$idUsuario)->where("modulo_id",$this->id)->first();
        if($usuario_modulo){
            return $usuario_modulo->hasta;
        }
        
        $plan_usuario = PlanUsuario::select("planes_usuarios.*")
            ->join("planes","planes_usuarios.plan_id","=","planes.id")
            ->join("planes_modulos","planes.id","=","planes_modulos.plan_id")
            ->join("modulos","planes_modulos.modulo_id","=","modulos.id")
            ->where("planes_usuarios.usuario_id",$idUsuario)
            ->where("modulos.id",$this->id)
            ->where("planes_usuarios.estado","activo")
            ->first();

        if($plan_usuario){
            return $plan_usuario->hasta;
        }

        return false;
    }

}
