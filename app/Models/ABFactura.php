<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection as Collection;


class ABFactura extends Model {

    protected $connection = 'mysql_alm';
	protected $table = "facturas";
    protected $fillable = ["numero","subtotal","iva","estado","cliente_id","usuario_id","resolucion_id","dias_credito","caja_usuario_id"];


    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return ABFactura::whereNotNull('id');
        }else if($perfil->nombre == 'administrador'){
            return ABFactura::where("facturas.usuario_id",$user->userAdminId());
        }else if($perfil->nombre == "usuario"){
            $usuario_creador = User::find($user->usuario_creador_id);
            if($usuario_creador){
                return ABFactura::where('facturas.usuario_id',$usuario_creador->id);
            }
        }
    }
    public function token(){
        return $this->hasOne("App\Models\TokenPuntos",'factura_id','id');
    }

    public static function ultimaFactura($general = false){
        if(Auth::user()->admin_bodegas == 'si' || $general) {
            return ABFactura::permitidos()->orderBy("id", "DESC")->orderBy("created_at", "DESC")->orderBy("numero", "DESC")->first();
        }else {
            $almacen = Auth::user()->almacenActual();
            if(!$almacen)return null;
            return ABFactura::permitidos()->orderBy("id", "DESC")->orderBy("created_at", "DESC")->orderBy("numero", "DESC")->where('almacen_id',$almacen->id)->first();
        }
    }
    public function cliente(){
        return $this->belongsTo("App\Models\Cliente");
    }

    public function usuario(){
        return $this->belongsTo("App\User");
    }

    public function usuarioCreador(){
        return $this->belongsTo("App\User","usuario_creador_id","id");
    }

    public function productosHistorialUtilidad(){
        return $this->belongsToMany("App\Models\ABHistorialUtilidad","facturas_historial_utilidad","factura_id","historial_utilidad_id");
    }

    public function productosHistorialCosto(){
        return $this->belongsToMany("App\Models\ABHistorialCosto","facturas_historial_costos","factura_id","historial_costo_id");
    }

    public static function imagetosepia(&$img) {
        if (!($t = imagecolorstotal($img))) {
            $t = 256;
            imagetruecolortopalette($img, true, $t);
        }
        $total = imagecolorstotal( $img );
        for ( $i = 0; $i < $total; $i++ ) {
            $index = imagecolorsforindex( $img, $i );
            $red = ( $index["red"] * 0.393 + $index["green"] * 0.769 + $index["blue"] * 0.189 );
            $green = ( $index["red"] * 0.349 + $index["green"] * 0.686 + $index["blue"] * 0.168 );
            $blue = ( $index["red"] * 0.272 + $index["green"] * 0.534 + $index["blue"] * 0.131 );
            if ($red > 255) { $red = 255; }
            if ($green > 255) { $green = 255; }
            if ($blue > 255) { $blue = 255; }
            imagecolorset( $img, $i, $red, $green, $blue );
        }
    }

    public static function utilidades($fechaInicio,$fechaFin,$paginate = null){
        /*
         * select facturas.*,sum(productos.precio_costo) as full_precio_costo from facturas
INNER JOIN facturas_productos ON facturas.id = facturas_productos.factura_id
INNER JOIN productos on productos.id = facturas_productos.producto_id
GROUP BY facturas.id*/
        //se suma un dia para que la fecha de finalización de la consulta quede incluida
        $result = Factura::select("facturas.*","clientes.tipo_identificacion","clientes.identificacion",DB::raw("sum((productos_historial.precio_costo_nuevo + ((productos_historial.precio_costo_nuevo * productos_historial.iva_nuevo)/100)) * facturas_productos_historial.cantidad) as full_precio_costo"))
        ->join("facturas_productos_historial","facturas.id","=","facturas_productos_historial.factura_id")
        ->join("productos_historial","facturas_productos_historial.producto_historial_id","=","productos_historial.id")
        ->join("productos","productos_historial.producto_id","=","productos.id")
        ->join("clientes","facturas.cliente_id","=","clientes.id")
        ->where("facturas.estado","Pagada")
        ->where("facturas.usuario_id",Auth::user()->userAdminId())
        ->whereBetween("facturas.created_at",[$fechaInicio,$fechaFin])
        ->groupBy("facturas.id");

        if($paginate == null){
            return $result->get();
        }else{
            return $result->paginate($paginate);
        }
    }
    public static function ventas($fechaInicio,$fechaFin,$paginate = null){
        /*
         * select facturas.*,sum(productos.precio_costo) as full_precio_costo from facturas
INNER JOIN facturas_productos ON facturas.id = facturas_productos.factura_id
INNER JOIN productos on productos.id = facturas_productos.producto_id
GROUP BY facturas.id*/
        //se suma un dia para que la fecha de finalización de la consulta quede incluida
        $fechaFin = date("Y-m-d",strtotime("+1days",strtotime($fechaFin)));
        $result = Factura::select("facturas.*","clientes.nombre")
        ->leftJoin("clientes","facturas.cliente_id","=","clientes.id")
        ->where("facturas.usuario_id",Auth::user()->userAdminId())
        ->where(function($q){
            $q->where("facturas.estado","Pagada")
                ->orWhere("facturas.estado","Pendiente por pagar")
                ->orWhere("facturas.estado","cerrada")
                ->orWhere("facturas.estado","abierta");
        })

        ->whereBetween("facturas.created_at",[$fechaInicio,$fechaFin]);

        if($paginate == null){
            return $result->get();
        }else{
            return $result->paginate($paginate);
        }
    }

    function getSaldo(){
        $pagos = 0;
        if($this->estado == "Pendiente por pagar" || $this->estado == 'Pedida'){
            foreach ($this->abonos as $abono){
                $pagos += $abono->valor;
            }
            return ($this->subtotal + $this->iva) - $pagos;
        }
        return 0;
    }

    function ultimoAbono(){
        return $this->abonos()->orderBy("created_at","DESC")->orderBy("id","DESC")->first();
    }

    function abonos(){
        return $this->hasMany("App\Models\ABAbono","tipo_abono_id")->where("tipo_abono","factura");
    }
    /*public function abonosG(){
        return $this->hasMany("App\Models\Abono","tipo_abono_id")
            ->selectRaw('SUM(valor) as abonos')
            ->where("tipo_abono","factura")
            ->get();
    }*/
    public static function valorFacturasXCobrar($estado,$columna){

        if (Auth::user()->perfil->nombre == "administrador")
            $usuario_id = Auth::user()->id;
        else
            $usuario_id = Auth::user()->usuario_creador_id;

        $facturasXCobrar =  ABFactura::where('usuario_creador_id',$usuario_id)
            ->where('estado',$estado)->sum($columna);

        return $facturasXCobrar;
    }
    public static function getAbonosByCliente($cliente_id,$almacen = null){
        $facturas_cliente = ABFactura::permitidos()->select('id')
            ->where('estado','Pendiente por pagar')
            ->where('cliente_id',$cliente_id);

        if($almacen)$facturas_cliente = $facturas_cliente->where('almacen_id',$almacen);
        $facturas_cliente = $facturas_cliente->get();

        $total_abonos = 0;
        if (count($facturas_cliente)){
            foreach ($facturas_cliente as $fc){
                $abonos = ABAbono::select('*')
                    ->where('tipo_abono','factura')
                    ->where('tipo_abono_id',$fc->id)
                    ->get();
                $total_abonos += $abonos->sum('valor');
            }
        }
        return $total_abonos;
    }
    public static function getTotalAbonos($almacen = null){
        $facturas = ABFactura::permitidos()->select('id')
            ->where('estado','Pendiente por pagar');

        if($almacen)$facturas = $facturas->where('almacen_id',$almacen);

        $facturas = $facturas->get();

        $abonos=0;
        $total_abonos  = 0;

        if (count($facturas)){
            foreach ($facturas as $fc){
                $abonos = ABAbono::select(DB::raw('(sum(valor)) as valor_total'))
                    ->where('tipo_abono','factura')
                    ->where('tipo_abono_id',$fc->id)
                    ->get();
                $total_abonos += $abonos->sum('valor_total');
            }
            return $total_abonos;
        }
        return $abonos;

    }
    public static function abonosByFactura($id_factura){
        $abonos = Abono::select('*')
            ->where('tipo_abono_id',$id_factura)
            ->where('tipo_abono','factura')
            ->count();
        return $abonos;
    }
    public static function getValorFacturasByFecha($fecha_caja){
        //return $fecha_caja;
        $facturas = Factura::permitidos()->whereDate('updated_at', '=', $fecha_caja);
        //Facturas pagadas directamente
        $facturas_pagadas_directamente = $facturas->select(DB::raw('(sum(subtotal)+sum(iva)) as valor_facturas'))
            ->where('estado','Pagada')
            ->where('numero_cuotas', '=', 0)
            ->get();

        //facturas abierta
        $facturas_repidas = Factura::permitidos()->whereDate('updated_at', '=', $fecha_caja)->select(DB::raw('(sum(subtotal)+sum(iva)) as valor_facturas'))
            ->where(function($q){
                $q->where('estado','abierta')
                    ->orWhere('estado','cerrada');
            })
            ->get();
        //facturas pagadas por abonos
        $facturas = Factura::permitidos()->whereDate('updated_at', '=', $fecha_caja);
        $facturas_pagadas_abonos = $facturas->select('id')
            ->where('estado','Pagada')
            ->where('numero_cuotas', '>', 0)
            ->get();

        $total_abonos =0;
        foreach ($facturas_pagadas_abonos as $fpa){
            $abonos = Abono::select('*')
                ->where('tipo_abono','factura')
                ->where('tipo_abono_id',$fpa->id)
                ->whereDate('updated_at', '=', $fecha_caja)
                ->get();
            $total_abonos += $abonos->sum('valor');
        }
        //facturas pendietes por pagar y a las que se les ha realizado un abono el dia de la caja
        $facturas = Factura::permitidos();
        $facturas_pendiente_abonos = $facturas->select('id')
            ->where('estado','Pendiente por pagar')
            ->where('numero_cuotas', '>', 0)
            ->get();
        $total_abonos_2 = 0;
        foreach ($facturas_pendiente_abonos as $fpab){
            $abonos = Abono::select('*')
                ->where('tipo_abono','factura')
                ->where('tipo_abono_id',$fpab->id)
                ->whereDate('fecha', '=', $fecha_caja)
                ->get();
            $total_abonos_2 += $abonos->sum('valor');
        }

        $total_ingresos = $facturas_pagadas_directamente->sum('valor_facturas')+ $facturas_repidas->sum("valor_facturas") + $total_abonos + $total_abonos_2;
        //cuentas pendientes de pago

        return $total_ingresos;
    }
    public static function getValorFacturasByCajaMaestra($caja_maestra){
        //return $fecha_caja;
        $base_consulta = Factura::permitidos()
            ->join("cajas_usuarios","facturas.caja_usuario_id","=","cajas_usuarios.id")
            ->join("caja","cajas_usuarios.caja_mayor_id","=","caja.id")
            ->where("caja.id",$caja_maestra);

        $facturas = $base_consulta->select("facturas.*");
        //Facturas pagadas directamente
        $facturas_pagadas_directamente = $facturas->select(DB::raw('(sum(subtotal)+sum(iva)-sum(descuento)) as valor_facturas'))
            ->where('facturas.estado','Pagada')
            ->where('facturas.numero_cuotas', '=', 0)
            ->get();

        //facturas abierta
        $facturas_repidas = $base_consulta->select(DB::raw('(sum(subtotal)+sum(iva)-sum(descuento)) as valor_facturas'))
            ->where(function($q){
                $q->where('facturas.estado','abierta')
                    ->orWhere('facturas.estado','cerrada');
            })
            ->get();
        //facturas pagadas por abonos
        $facturas = $base_consulta;
        $facturas_pagadas_abonos = $facturas->select('facturas.id')
            ->where('facturas.estado','Pagada')
            ->where('facturas.numero_cuotas', '>', 0)
            ->get();

        $total_abonos =0;
        foreach ($facturas_pagadas_abonos as $fpa){
            $abonos = Abono::select('*')
                ->join("cajas_usuarios","abonos.caja_usuario_id","=","cajas_usuarios.id")
                ->join("caja","cajas_usuarios.caja_mayor_id","=","caja.id")
                ->where("caja.id",$caja_maestra)
                ->where('tipo_abono','factura')
                ->where('tipo_abono_id',$fpa->id)
                ->get();
            $total_abonos += $abonos->sum('valor');
        }
        //facturas pendietes por pagar y a las que se les ha realizado un abono el dia de la caja
        $facturas = $base_consulta;
        $facturas_pendiente_abonos = $facturas->select('facturas.id')
            ->where('facturas.estado','Pendiente por pagar')
            ->where('facturas.numero_cuotas', '>', 0)
            ->get();
        $total_abonos_2 = 0;
        foreach ($facturas_pendiente_abonos as $fpab){
            $abonos = Abono::select('*')
                ->join("cajas_usuarios","abonos.caja_usuario_id","=","cajas_usuarios.id")
                ->join("caja","cajas_usuarios.caja_mayor_id","=","caja.id")
                ->where("caja.id",$caja_maestra)
                ->where('tipo_abono','factura')
                ->where('tipo_abono_id',$fpab->id)
                ->get();
            $total_abonos_2 += $abonos->sum('valor');
        }

        $total_ingresos = $facturas_pagadas_directamente->sum('valor_facturas')+ $facturas_repidas->sum("valor_facturas") + $total_abonos + $total_abonos_2;
        //cuentas pendientes de pago

        return $total_ingresos;
    }

    public static function totalVentasSinIva($fechaInicio,$fechaFin,$almacen = false){
        $facturas = ABFactura::permitidos()->whereBetween("created_at",[$fechaInicio,$fechaFin])->where("estado","<>","anulada");
        if($almacen)$facturas = $facturas->where('almacen_id',$almacen);
        $facturas = $facturas->get();
        $total = 0;
        foreach ($facturas as $f){
            $total += ($f->subtotal - $f->descuento);
        }
        return $total;
    }


    public static function totalPrecioCosto($fechaInicio,$fechaFin,$almacen = false){
        $facturas = ABFactura::permitidos()->whereBetween("created_at",[$fechaInicio,$fechaFin])->where("estado","<>","anulada");
        if($almacen)$facturas = $facturas->where('almacen_id',$almacen);
        $facturas = $facturas->get();
        $total = 0;
        foreach ($facturas as $f){
            $pr = $f->productosHistorialCosto()->select("historial_costos.*")->get();
            foreach ($pr as $p) {
                $utilidad_obj = $f->productosHistorialUtilidad()->select('facturas_historial_utilidad.cantidad')->where('historial_utilidad.producto_id',$p->producto_id)->first();
                $total +=  $p->precio_costo_nuevo * $utilidad_obj->cantidad;
            }
        }
        return $total;
    }

    public static function cantidadVendidaMes($mes,$anio,$almacen = false){
        $dias = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
        $fechaInicio = $anio."-".$mes."-1";
        $fechaInicio = date("Y-m-d",strtotime($fechaInicio));
        $fechaFin = $anio."-".$mes."-".$dias;
        $fechaFin = date("Y-m-d",strtotime("+1days",strtotime($fechaFin)));
        $obj = ABFactura::permitidos()->select(DB::raw("sum(subtotal + iva - descuento) as cantidad_vendida"))
            ->whereBetween("created_at",[$fechaInicio,$fechaFin])
            ->where("facturas.estado","<>","anulada");

        if($almacen)
            $obj = $obj->where('almacen_id',$almacen);
        $obj = $obj->first();
        return $obj->cantidad_vendida;
    }

    public function resolucion(){
        return $this->belongsTo(Resolucion::class);
    }

    public function almacen(){
        return $this->belongsTo(Almacen::class);
    }

    public function tiposPago(){
        return $this->belongsToMany(TipoPago::class,'vendiendo_alm.facturas_tipos_pago','factura_id','tipo_pago_id');
    }

    public function getValorMediosPago(){
        //return $fecha_caja;
        $data = $this->tiposPago()->select(DB::raw('sum(vendiendo_alm.facturas_tipos_pago.valor) as valor'))->first();

        $total = 0;
        if($data)$total = $data->valor;
        return $total;
    }
}
