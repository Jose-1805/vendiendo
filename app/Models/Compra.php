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


class Compra extends Model {

	protected $table = "compras";
    protected $fillable = [
        "numero",
        "valor",
        "estado",
        "estado_pago",
        "caja_maestra_id",
        "proveedor_id",
        "usuario_id",
        "usuario_creador_id",
        "numero_cuotas",
        "tipo_periodicidad_notificacion",
        "periodicidad_notificacion",
        "fecha_primera_notificacion",
        "dias_credito"
    ];
    protected $dates = ['created_at', 'updated_at'];


    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return Compra::whereNotNull('id');
        }else if($perfil->nombre == 'administrador'){
            return Compra::where("compras.usuario_id",$user->id);
        }else if($perfil->nombre == "usuario"){
            $usuario_creador = User::find($user->usuario_creador_id);
            if($usuario_creador){
                return Compra::where('compras.usuario_id',$usuario_creador->id);
            }
        }
    }
    function abonos(){
        return $this->hasMany("App\Models\Abono","tipo_abono_id")->where("tipo_abono","compra");
    }

    public function proveedor(){
        return $this->belongsTo("App\Models\Proveedor");
    }

    public function usuario(){
        return $this->belongsTo("App\User");
    }

    public function usuarioCreador(){
        return $this->belongsTo("App\User","usuario_creador_id","id");
    }

    /*
    La tabla ya no se relaciona directamente con los productos
    public function productos(){
        return $this->belongsToMany("App\Models\Producto","compras_productos");
    }*/

    public function productosHistorial(){
        return $this->belongsToMany("App\Models\ProductoHistorial","compras_productos_historial");
    }

    public function materiasPrimasHistorial(){
        return $this->belongsToMany(MateriaPrimaHistorial::class,"compras_materias_primas_historial");
    }

    public function getPrecioCosto(){
        $productos = $this->productosHistorial()->select("compras_productos_historial.cantidad","productos_historial.precio_costo_nuevo")->get();
        $precioCosto = 0;
        foreach ($productos as $p){
            $precioCosto += ($p->cantidad * $p->precio_costo_nuevo);
        }

        $materiasPrimas = $this->materias_primas()->select("compras_materias_primas.cantidad","proveedores_materias_primas.valor")
            ->join("proveedores_materias_primas","materias_primas.id","=","proveedores_materias_primas.materia_prima_id")
            ->where("proveedores_materias_primas.proveedor_id",$this->proveedor_id)->get();
        foreach ($materiasPrimas as $m){
            $precioCosto += ($m->cantidad * $m->valor);
        }
        return $precioCosto;
    }

    public static function totalPrecioCosto($fechaInicio,$fechaFin){
        $compras = Compra::permitidos()->whereBetween("created_at",[$fechaInicio,$fechaFin])->where("estado","<>","Cancelada")->get();
        $total = 0;
        foreach ($compras as $c){
            $total += $c->getPrecioCosto();
        }
        return $total;
    }

    public function materias_primas(){
        return $this->belongsToMany("App\Models\MateriaPrima","compras_materias_primas","compra_id","materia_prima_id");
    }

    public function materias_primas_historial(){
        return $this->belongsToMany(MateriaPrimaHistorial::class,"compras_materias_primas_historial","compra_id","materia_prima_historial_id");
    }

    public static function ultimaCompra(){
        return Compra::permitidos()->orderBy("id","DESC")->orderBy("created_at","DESC")->orderBy("numero","DESC")->first();
    }
    
    public function updateCompra($id_compra,$valor_devolucion){
        $compra = Compra::permitidos()->find($id_compra);
        if ($compra){
            $valor_compra = $compra->valor;
            $valor_compra = $valor_compra - $valor_devolucion;

            $compra = Compra::permitidos()->where('id',$id_compra)
                ->update([
                    'valor'=>$valor_compra
                ]);
            return $compra;
        }
        return false;

    }
    public function updateStock($id,$cantidad_devolucion,$tipo_compra){
        if ($tipo_compra == "MateriaPrima")
            $elemento = MateriaPrima::permitidos()->find($id);
        else if($tipo_compra == "Producto")
            $elemento = Producto::permitidos()->find($id);

        $stock = $elemento->stock;
        $stock = $stock - $cantidad_devolucion;
        //$stock;
        if($stock >= 0){
            if ($tipo_compra == "MateriaPrima"){
                $elemento = MateriaPrima::where('id',$id)
                    ->update([
                        'stock'=>$stock
                    ]);
            }else if($tipo_compra == "Producto"){
                /*$elemento = Producto::permitidos()->where('id',$id)
                    ->update([
                        'stock'=>$stock
                    ]);*/
            }
            return $elemento;
        }else{
            return false;
        }
    }
    public static function calculateQuantity($tipo_compra,$id_compra,$id_elemento,$cantidad_devolucion,$id_elemento_historial){

        if ($tipo_compra == "MateriaPrima"){
            $compra_elemento = DB::table('compras_materias_primas_historial')->select('compras_materias_primas_historial.cantidad','compras_materias_primas_historial.id')
                ->join("materias_primas_historial","compras_materias_primas_historial.materia_prima_historial_id","=","materias_primas_historial.id")
                ->where('compra_id',$id_compra)
                ->where('materias_primas_historial.materia_prima_id',$id_elemento)->first();
        }else if($tipo_compra == "Producto"){
            $compra_elemento = DB::table('compras_productos_historial')->select('cantidad','id')
                ->where('compra_id',$id_compra)
                ->where('producto_historial_id',$id_elemento_historial)->first();
        }
        $cantidad = $compra_elemento->cantidad;
        $cantidad = $cantidad - $cantidad_devolucion;

        return $cantidad;

    }
    public static function deleteElemento($tipo_compra,$id_compra,$id_elemento){

        if ($tipo_compra == "MateriaPrima"){
            $compra_elemento = DB::table('compras_materias_primas_historial')->select('cantidad','id')
                ->where('compra_id',$id_compra)
                ->where('materia_prima_historial_id',$id_elemento)->first();
        }else if($tipo_compra == "Producto"){
            $compra_elemento = DB::table('compras_productos_historial')->select('cantidad','id')
                ->where('compra_id',$id_compra)
                ->where('producto_historial_id',$id_elemento)->first();
        }

        if ($tipo_compra == "MateriaPrima")
            $elemento = DB::table('compras_materias_primas_historial')->where('id',$compra_elemento->id)->delete();
        else if($tipo_compra == "Producto")
            $elemento = DB::table('compras_productos_historial')->where('id',$compra_elemento->id)->delete();

        if($elemento)
            return $elemento;
        else
            return false;

    }
    public static function saveDevolutionHistory($compra_id,$elemento_id,$cantidad_devolucion,$tipo_elemento,$motivo,$proveedor_id){
        $user = Auth::user();
        $history = HistorialDevolucion::create([
            'compra_id'     => $compra_id,
            'elemento_id'   => $elemento_id,
            'cantidad'      => $cantidad_devolucion,
            'tipo_elemento' => $tipo_elemento,
            'motivo'        => $motivo,
            'usuario_id'    => $user->id,
            'proveedor_id'  => $proveedor_id
        ]);
        if($history)
            return $history;
        else
            return false;
    }
    public static function saveStatesHistory($compra_id,$estado_compra,$estado_pago){
        $user = Auth::user();

        $history = HistorialCambioEstadoCompra::create([
            'compra_id'     => $compra_id,
            'usuario_id'    => $user->id,
            'estado_compra' => $estado_compra,
            'estado_pago'   => $estado_pago
        ]);
        if($history)
            return $history;
        else
            return false;
    }
    public static function updateStockReceived($compra_id){

        $productos = DB::table('compras_productos_historial')->select('*')->where('compra_id',$compra_id)->get();
        $materias_primas = DB::table('compras_materias_primas_historial')->select('*')->where('compra_id',$compra_id)->get();

        //actualizar el inventario con los elementos de la compra
        if(count($productos)){
            foreach ($productos as $key => $value){
                $producto = ProductoHistorial::select('producto_id')->where('id',$value->producto_historial_id)->first();
                $elemento = Producto::find($producto->producto_id);
                $elemento->stock += $value->cantidad;
                $elemento->save();

            }
        }
        if (count($materias_primas)){
            foreach ($materias_primas as $key => $value){
                $elemento = MateriaPrima::find($value->materia_prima_id);
                $elemento->stock += $value->cantidad;
                $elemento->save();
            }
        }

        return $elemento;
    }
    public static function payPurchase($valor_compra){

        $fecha_actual = date("Y"). "-". date("m") . "-" . date("d");
        $usuario_id = Auth::user()->userAdminId();
        $caja = Caja::where('usuario_id',$usuario_id)
            ->where('estado','abierta')
            ->first();
        if($caja->efectivo_final < $valor_compra)return false;

        $caja->efectivo_final -= $valor_compra;
        $caja->save();
        return $caja;
    }
    public static function updatePaymentStatus($id_compra){
        $compra = Compra::find($id_compra);
        if ($compra){
            $compra = Compra::where('id',$id_compra)
                ->update([
                    'estado_pago'=>"Pagada"
                ]);
            return $compra;
        }
        return false;

    }
    public static function payBox($valor_abono){

        $usuario_id = Auth::user()->userAdminId();
        $caja = Caja::abierta();

        $caja->efectivo_final -= $valor_abono;
        $caja->save();
        return $caja;
    }
    public static function payCompraAbonos($compra_id){
        $fecha_actual = date("Y") . "-" . date("m") . "-" . date("d");

        //si tiene abonos, se paga el saldo que debe de la compra, compra-abonos
        $compra = Compra::permitidos()->where("id", $compra_id)->first();
        if ($compra) {
            $suma_abonos = $compra->abonos()->sum('valor');
            $saldo = $compra->valor - $suma_abonos;
        }
        $abono = new Abono();
        $abono->valor = $saldo;
        $abono->nota = "Pago el total de la compra, al cambiar el estado";
        $abono->fecha = $fecha_actual;
        $abono->usuario_id = Auth::user()->id;
        $abono->tipo_abono = 'compra';
        $abono->tipo_abono_id = $compra_id;
        $abono->save();
    }

    function getSaldo(){
        $pagos = 0;
        if($this->estado_pago == "Pendiente por pagar"){
            foreach ($this->abonos as $abono){
                $pagos += $abono->valor;
            }
            return $this->valor - $pagos;
        }
        return 0;
    }

    function ultimoAbono(){
        return $this->abonos()->orderBy("created_at","DESC")->orderBy("id","DESC")->first();
    }

    public static function getAbonosByProveedor($proveedor_id){
        $compras_proveedor = Compra::permitidos()->select('id')
            ->where('dias_credito','>','0')
            ->where('proveedor_id',$proveedor_id)
            ->get();
        $total_abonos = 0;
        if (count($compras_proveedor)){
            foreach ($compras_proveedor as $cp){
                $abonos = Abono::select('*')
                    ->where('tipo_abono','compra')
                    ->where('tipo_abono_id',$cp->id)
                    ->get();
                $total_abonos += $abonos->sum('valor');
            }
        }
        return $total_abonos;
    }
    public static function getTotalAbonos(){
        $compras = Compra::permitidos()->select('id')
            ->where('dias_credito','>','0')
            ->get();
       $abonos=0;
       $total_abonos  = 0;
       if (count($compras)){
           foreach ($compras as $fc){
               $abonos = Abono::select(DB::raw('(sum(valor)) as valor_total'))
                   ->where('tipo_abono','compra')
                   ->where('tipo_abono_id',$fc->id)
                   ->get();
               $total_abonos += $abonos->sum('valor_total');
           }
           return $total_abonos;
       }
        return $abonos;
    }
    public static function getValorEgresosByFecha($fecha_caja){
        $compras = Compra::permitidos()->whereDate('updated_at', '=', $fecha_caja);

        //compras pagadas directamente
        $compras_pagadas_directamente = $compras
            ->where('estado_pago','Pagada')
            ->where('numero_cuotas', '=', 0)
            ->sum('valor');

        //gastos diarios
        $fecha_inicio  = date("Y-m-d",strtotime($fecha_caja));
        $fecha_fin = $fecha_inicio." 23:59:59";
        $fecha_inicio .= " 00:00:00";
        $gastos = GastoDiario::permitidos()->whereBetween("created_at",[$fecha_inicio,$fecha_fin])->get();

        //compras pagadas cambiando el estado, pagada a un solo abono o por abonos
        $compras = Compra::permitidos()->whereDate('updated_at', '=', $fecha_caja);
        $compras_pagadas_estado = $compras->select('id')
            ->where('estado_pago','Pagada')
            ->where('numero_cuotas', '>', 0)
            ->get();
        $total_abonos = 0;
        foreach ($compras_pagadas_estado as $cpe){
            $abonos = Abono::select('*')
                ->where('tipo_abono','compra')
                ->where('tipo_abono_id',$cpe->id)
                ->where('fecha',$fecha_caja)
                ->get();
            $total_abonos += $abonos->sum('valor');
        }

        //compras pendientes por pagar con abonos realizados en el dia de la caja
        $compras = Compra::permitidos();
        $compras_pendiente_abonos = $compras->select('id')
            ->where('estado_pago','Pendiente por pagar')
            ->where('numero_cuotas', '>', 0)
            ->get();
        $total_abonos_2 = 0;
        foreach ($compras_pendiente_abonos as $cpab){
            $abonos = Abono::select('*')
                ->where('tipo_abono','compra')
                ->where('tipo_abono_id',$cpab->id)
                ->whereDate('fecha', '=', $fecha_caja)
                ->get();
            $total_abonos_2 += $abonos->sum('valor');
        }
        //Pagos por costos fijos
        $costos_fijos = CostoFijo::permitidos()->get();
        $costos_fijos_pagados=0;
        foreach ($costos_fijos as $cfp){
            $costos_fijos_pagados += $cfp->pagosCostosFijo()->where('created_at',$fecha_caja)->sum('valor');
        }
        
        $total_egresos = $compras_pagadas_directamente + $total_abonos + $total_abonos_2 + $costos_fijos_pagados + $gastos->sum("valor");

        return $total_egresos;
    }

    public static function getValorEgresosByCajaMaestra($caja_maestra){
        $compras = Compra::permitidos()->where('compras.caja_maestra_id', $caja_maestra);

        //compras pagadas directamente
        $compras_pagadas_directamente = $compras
            ->where('estado_pago','Pagada')
            ->where('numero_cuotas', '=', 0)
            ->sum('valor');

        //gastos diarios
        $gastos = GastoDiario::permitidos()->where("caja_maestra_id",$caja_maestra)->get();

        //compras pagadas cambiando el estado, pagada a un solo abono o por abonos
        $compras = Compra::permitidos()->where('caja_maestra_id', $caja_maestra);
        $compras_pagadas_estado = $compras->select('id')
            ->where('estado_pago','Pagada')
            ->where('numero_cuotas', '>', 0)
            ->get();
        $total_abonos = 0;
        foreach ($compras_pagadas_estado as $cpe){
            $abonos = Abono::select('*')
                ->where('tipo_abono','compra')
                ->where('tipo_abono_id',$cpe->id)
                ->where('caja_maestra_id',$caja_maestra)
                ->get();
            $total_abonos += $abonos->sum('valor');
        }

        //compras pendientes por pagar con abonos realizados en el dia de la caja
        $compras = Compra::permitidos()->where('caja_maestra_id', $caja_maestra);
        $compras_pendiente_abonos = $compras->select('id')
            ->where('estado_pago','Pendiente por pagar')
            ->where('numero_cuotas', '>', 0)
            ->get();
        $total_abonos_2 = 0;
        foreach ($compras_pendiente_abonos as $cpab){
            $abonos = Abono::select('*')
                ->where('tipo_abono','compra')
                ->where('tipo_abono_id',$cpab->id)
                ->where('caja_maestra_id', $caja_maestra)
                ->get();
            $total_abonos_2 += $abonos->sum('valor');
        }
        //Pagos por costos fijos
        $costos_fijos = CostoFijo::permitidos()->get();
        $costos_fijos_pagados=0;
        foreach ($costos_fijos as $cfp){
            $costos_fijos_pagados += $cfp->pagosCostosFijo()->where('caja_maestra_id',$caja_maestra)->sum('valor');
        }

        $total_egresos = $compras_pagadas_directamente + $total_abonos + $total_abonos_2 + $costos_fijos_pagados + $gastos->sum("valor");

        return $total_egresos;
    }


    public static function getValorByCajaMaestra($caja_maestra){

        //  echo "****Caja Maestra:".$caja_maestra."<br>";

        $compras = Compra::permitidos()->where('compras.caja_maestra_id', $caja_maestra);

        //compras pagadas directamente
        $compras_pagadas_directamente = $compras
            ->where('estado_pago','Pagada')
            ->where('numero_cuotas', '=', 0)
            ->sum('valor');


        //compras pagadas cambiando el estado, pagada a un solo abono o por abonos
        //$compras = Compra::permitidos()->where('caja_maestra_id', $caja_maestra);
        $compras_pagadas_estado =  Compra::permitidos()->select('id')
            ->where('estado_pago','Pagada')
            ->where('numero_cuotas', '>', 0)
            ->get();
        $total_abonos = 0;


        foreach ($compras_pagadas_estado as $cpe){
            $abonos = Abono::select('*')
                ->where('tipo_abono','compra')
                ->where('tipo_abono_id',$cpe->id)
                ->where('caja_maestra_id',$caja_maestra)
                ->get();
            $total_abonos += $abonos->sum('valor');


            //  if($abonos->sum('valor')>0){
            //    echo "----Tipo Abono Id:".$cpe->id."<br>";
            //      echo "Abono 1:".$abonos->sum('valor')."<br>";
            //  }

        }


        //compras pendientes por pagar con abonos realizados en el dia de la caja
        //$compras = Compra::permitidos()->where('caja_maestra_id', $caja_maestra);
        $compras_pendiente_abonos = Compra::permitidos()->select('id')
            ->where('estado_pago','Pendiente por pagar')
            ->where('numero_cuotas', '>', 0)
            ->get();


        //  echo "0000 Caja Maestra:".$caja_maestra."<br>";
        $total_abonos_2 = 0;

        foreach ($compras_pendiente_abonos as $cpab){
            $abonos = Abono::select('*')
                ->where('tipo_abono','compra')
                ->where('tipo_abono_id',$cpab->id)
                ->where('caja_maestra_id', $caja_maestra)
                ->get();
            $total_abonos_2 += $abonos->sum('valor');


            // if($abonos->sum('valor')>0){
            //   echo "----Tipo Abono Id:".$cpab->id."<br>";
            //     echo "Abono 2:".$abonos->sum('valor')."<br>";
            // }

        }
        //Pagos por costos fijos

        $total_egresos = $compras_pagadas_directamente + $total_abonos + $total_abonos_2;

        // echo "Total egresos: Pagadas directamente:".$compras_pagadas_directamente." - Total abonos Pagados: ". $total_abonos ." -  Total abonos Pendientes: ". $total_abonos_2 ."<br>";

        return $total_egresos;
    }
    public static function getEfectivoByCajaMaestra($caja_maestra){

        //  echo "****Caja Maestra:".$caja_maestra."<br>";

        $compras = Compra::permitidos()->where('compras.caja_maestra_id', $caja_maestra);

        //compras pagadas directamente
        $compras_pagadas_directamente = $compras
            ->where('estado_pago','Pagada')
            ->where('numero_cuotas', '=', 0)
            ->sum('valor');

        $compras_ = Compra::permitidos()
            ->join('cuentas_por_cobrar_proveedores','compras.id','=','cuentas_por_cobrar_proveedores.compra_id')
            ->where('cuentas_por_cobrar_proveedores.estado','!=','PAGADA')
            ->where('compras.caja_maestra_id', $caja_maestra);

        //compras pagadas directamente
        $compras_pagadas_directamente += $compras_->sum('valor_devolucion');

        return $compras_pagadas_directamente;
    }
    public static function getEfectivoCuentasPorCobrarByCajaMaestra($caja_maestra){

        //  echo "****Caja Maestra:".$caja_maestra."<br>";

        $compras = Compra::permitidos()
            ->join('cuentas_por_cobrar_proveedores','compras.id','=','cuentas_por_cobrar_proveedores.compra_id')
            ->where('cuentas_por_cobrar_proveedores.estado','PAGADA')
            ->where('cuentas_por_cobrar_proveedores.forma_pago','Efectivo')
            ->where('cuentas_por_cobrar_proveedores.caja_maestra_id', $caja_maestra);

        //compras pagadas directamente
        $compras_pagadas_directamente = $compras
            ->sum('valor_devolucion');

        return $compras_pagadas_directamente;
    }
    public static function getCreditoByCajaMaestra($caja_maestra){

        $compras = Compra::permitidos()->where('compras.caja_maestra_id', $caja_maestra);


        $compras_credito =  $compras->select('valor')
            ->where(function ($q){
                $q->where('estado_pago','Pendiente por pagar')
                    ->orWhere(function ($q_){
                        $q_->where('estado_pago','Pagada')
                            ->where('numero_cuotas', '>', 0);
                    });
            })
            ->sum('valor');

        return $compras_credito;
    }

    public function cuentasPorCobrar(){
        return $this->hasMany(CuentaPorCobrar::class,"compra_id");
    }

    public function historialDevoluciones(){
        return $this->hasMany(HistorialDevolucion::class,'compra_id');
    }
}
