<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class CostoFijo extends Model {

    protected $table = 'costos_fijos';
    protected $fillable = ['nombre','estado','usuario_id','usuario_creador_id'];
    protected $guarded = "id";

    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return CostoFijo::whereNotNull('id');
        }else if($perfil->nombre == 'administrador'){
            if($user->bodegas == 'si' && $user->admin_bodegas == 'no') {
                $almacen = $user->almacenActual();
                if($almacen){
                    return CostoFijo::where('almacen_id',$almacen->id);
                }
            }
            return CostoFijo::where("costos_fijos.usuario_id",$user->id);
        }else if($perfil->nombre == "usuario"){
            if($user->bodegas == 'si' && $user->admin_bodegas == 'no') {
                $almacen = $user->almacenActual();
                if($almacen){
                    return CostoFijo::where('almacen_id',$almacen->id);
                }
            }
            $usuarioCreador = User::find(Auth::user()->userAdminId());
            if($usuarioCreador){
                return CostoFijo::where('costos_fijos.usuario_id',$usuarioCreador->id);
            }
        }
    }

    public static function totalPagos($fechaInicio,$fechaFin,$almacen = false){
        $costosFijos = CostoFijo::permitidos()->select("pagos_costos_fijos.valor")
            ->join("pagos_costos_fijos","costos_fijos.id","=","pagos_costos_fijos.costo_fijo_id")
            ->whereBetween("pagos_costos_fijos.fecha",[$fechaInicio,$fechaFin]);

        if($almacen)$costosFijos = $costosFijos->where('almacen_id',$almacen);
        $costosFijos = $costosFijos->get();
        $total = 0;
        foreach ($costosFijos as $c){
            $total += $c->valor;
        }
        return $total;
    }

    public function pagoMes($mes,$anio){
        $diasMes = cal_days_in_month ( CAL_GREGORIAN , $mes, $anio);

        $fecha_inicio = $anio."-".$mes."-1";
        $fecha_fin = $anio."-".$mes."-".$diasMes;


        return PagoCostoFijo::where("costo_fijo_id",$this->id)->whereBetween("fecha",[$fecha_inicio,$fecha_fin])->orderBy("created_at","DESC")->first();
    }
    public function pagosCostosFijo(){
        return $this->hasMany(PagoCostoFijo::class,'costo_fijo_id','id');
    }

    public function pagos(){
        return $this->hasMany("App\Models\PagoCostoFijo");
    }


    public static function getValorByCajaMaestra($caja_maestra){


      //Pagos por costos fijos
        $costos_fijos = CostoFijo::permitidos()->get();
        $costos_fijos_pagados=0;
        foreach ($costos_fijos as $cfp){
            $costos_fijos_pagados += $cfp->pagosCostosFijo()->where('caja_maestra_id',$caja_maestra)->sum('valor');
        }

        $total_egresos =  $costos_fijos_pagados;

        return $total_egresos;
    }

}
