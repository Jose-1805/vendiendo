<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class GastoDiario extends Model {

    protected $table = 'gastos_diarios';
    protected $fillable = ['valor','descripcion','usuario_id','usuario_creador_id','caja_maestra_id'];
    protected $guarded = "id";

    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;

        if($perfil->nombre == 'superadministrador'){
            return GastoDiario::whereNotNull('id');
        }else {
            if($user->bodegas == 'si' && $user->admin_bodegas == 'no') {
                $almacen = $user->almacenActual();
                if($almacen){
                    return GastoDiario::where('almacen_id',$almacen->id);
                }
            }
            return GastoDiario::where("gastos_diarios.usuario_id",$user->userAdminId());
        }
    }

    public static function totalPagos($fechaInicio,$fechaFin,$almacen = false){
        $gastos_diarios = GastoDiario::permitidos()->select("gastos_diarios.valor")
            ->whereBetween("gastos_diarios.created_at",[$fechaInicio,$fechaFin]);

        if($almacen){
            $gastos_diarios = $gastos_diarios->where("almacen_id",$almacen);
        }
        $gastos_diarios = $gastos_diarios->get();

        $total = 0;
        foreach ($gastos_diarios as $gd){
            $total += $gd->valor;
        }
        return $total;
    }


    public static function getValorByCajaMaestra($caja_maestra){
        $compras = Compra::permitidos()->where('compras.caja_maestra_id', $caja_maestra);

        //compras pagadas directamente
        $compras_pagadas_directamente = $compras
            ->where('estado_pago','Pagada')
            ->where('numero_cuotas', '=', 0)
            ->sum('valor');

        //gastos diarios
        $gastos = GastoDiario::permitidos()->where("caja_maestra_id",$caja_maestra)->get();


        $total_egresos = $gastos->sum("valor");

        return $total_egresos;
    }
}
