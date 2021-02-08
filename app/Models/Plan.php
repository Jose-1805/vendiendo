<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Plan extends Model {

    protected $table = 'planes';

    protected $fillable = [
        "nombre",
        "duracion",
        "valor",
        "n_usuarios",
        "n_compras",
        "n_facturas",
        "n_almacenes",
        "n_bodegas",
        "n_proveedores",
        "n_clientes",
        "n_productos",
        "n_promociones_sms",
        "puntos",
        "notificaciones",
        "cliente_predeterminado",
        "factura_abierta",
        "objetivos_ventas",
        "validacion_stock",
        "importacion_productos",
    ];

    public function modulos(){
        return $this->belongsToMany("App\Models\Modulo","planes_modulos");
    }

    public function funciones(){
        return $this->belongsToMany("App\Models\Funcion","planes_funciones");
    }

    public function reportes(){
        return $this->belongsToMany("App\Models\Reporte","planes_reportes");
    }

    public function hasModulo($id){
        if(count($this->modulos()->where("modulos.id",$id)->get())){
            return true;
        }
        return false;
    }

    public function hasFuncion($id){
        if(count($this->funciones()->where("funciones.id",$id)->get())){
            return true;
        }
        return false;
    }

    public function hasTarea($funcion_id,$id){
        if(count($this->funciones()->where("funciones.id",$id)->get())){
            $planFuncion = PlanFuncion::where("funcion_id",$funcion_id)->where("plan_id",$this->id)->first();
            if($planFuncion && count($planFuncion->tareas()->where("tareas.id",$id)->get())){
                return true;
            }
        }
        return false;
    }

    public function hasReporte($id){
        if(count($this->reportes()->where("reportes.id",$id)->get())){
            return true;
        }
        return false;
    }

    public function eliminarRelaciones(){
        DB::statement("DELETE FROM planes_modulos WHERE plan_id = ".$this->id);
        DB::statement("DELETE FROM planes_funciones WHERE plan_id = ".$this->id);
        DB::statement("DELETE FROM planes_reportes WHERE plan_id = ".$this->id);
    }

    public function planesFunciones(){
        return $this->hasMany("App\Models\PlanFuncion");
    }

    public static function lista(){
        $data = [];
        foreach (Plan::all() as $p){
            $data[$p->id] = $p->nombre;
        }
        return $data;
    }
}
