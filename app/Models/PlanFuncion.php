<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanFuncion extends Model {

    protected $table = 'planes_funciones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    public function tareas(){
        return $this->belongsToMany("App\Models\Tarea","planes_funciones_tareas","plan_funcion_id","tarea_id");
    }
}
