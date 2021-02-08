<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funcion extends Model {

    protected $table = 'funciones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    public function tareas(){
        return $this->belongsToMany("App\Models\Tarea","funciones_tareas");
    }

    public function hasTarea($id){
        $count = Tarea::join("funciones_tareas","tareas.id","=","funciones_tareas.tarea_id")
            ->join("funciones","funciones_tareas.funcion_id","=","funciones.id")
            ->where("funciones.id",$this->id)
            ->where("tareas.id",$id)
            ->get()->count();
        if($count>0)return true;
        return false;
    }


}
