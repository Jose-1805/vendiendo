<?php
$total_compras = 0;
$total_gastos = 0;
$total_facturas = 0;
$total_descuentos = 0;
$total_valor_puntos = 0;

$diferencia = $totalFacturas - ($totalCostosFijos + $totalCompras + $totalGastosDiarios);
$total_costos_gastos = $totalCostosFijos + $totalCompras + $totalGastosDiarios;

$ganancia = 0;
$perdida = 0;
$rentabilidad = $total_costos_gastos == 0 ? 0 :  ($diferencia / $total_costos_gastos) * 100;
if($diferencia > 0){
    $ganancia = $diferencia;
}else {
    $perdida = $diferencia * -1;
}
?>

<table class="table centered highlight">
    <thead>
        <tr>
            <th colspan="6" style="border-bottom:1px solid rgba(0,0,0,.5);">GENERAL</th>
        </tr>
        <tr>
            <th>Ventas</th>
            <th>Costos compras</th>
            <th>Costos fijos</th>
            <th>Gastos diarios</th>
            <th>Utilidad</th>
            <th>Perdida</th>
            <th>% Rentabilidad neta</th>
        </tr>
    </thead>
    <tbody>
        <tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
        <tr>
            <td >{{($totalFacturas)}}</td>
            <td >{{($totalCompras)}}</td>
            <td>{{($totalCostosFijos)}}</td>
            <td>{{($totalGastosDiarios)}}</td>
            <td>{{($ganancia)}}</td>
            <td>{{($perdida)}}</td>
            <td>{{($rentabilidad)}}</td>
        </tr>
    </tbody>
</table>

<table class="table centered highlight">
    <thead>
        <tr>
            <th colspan="6" style="border-bottom:1px solid rgba(0,0,0,.5);">VENTAS</th>
        </tr>
        <tr>
            <th>Fecha</th>
            <th>Producto</th>
            <th>Ventas</th>
            <th>Costos compra</th>
            <th>Utilidad</th>
            <th>%Utilidad</th>
        </tr>
    </thead>
    <tbody >
    @if(isset($reporte))
        <tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
    @endif

    @forelse($facturas as $f)
        <?php
            if(Auth::user()->bodegas == 'si'){
                $prs = $f->productosHistorialCosto()->select("historial_costos.*")->get();
            }else{
                $prs = $f->productosHistorial()->select("productos_historial.*","facturas_productos_historial.cantidad")->get();
            }

            $total_descuentos += $f->descuento;
            $total_valor_puntos += $f->valor;//valor de los puntos
        ?>
        @foreach($prs as $p)
            <tr>
                <td >{{$f->created_at}}</td>
                <td>{{$p->producto->nombre}}</td>
                <?php
                    if(Auth::user()->bodegas == 'si'){
                        $utilidad_obj = $f->productosHistorialUtilidad()->select('facturas_historial_utilidad.cantidad','historial_utilidad.utilidad as utilidad')->where('historial_utilidad.producto_id',$p->producto_id)->first();
                        $p->utilidad_nueva = $utilidad_obj->utilidad;
                        $p->cantidad = $utilidad_obj->cantidad;
                    }
                    $valorVenta = ($p->precio_costo_nuevo + (($p->precio_costo_nuevo * $p->utilidad_nueva)/100)) * $p->cantidad;
                    $valorCompra = $p->precio_costo_nuevo * $p->cantidad;

                    $total_facturas += $valorVenta;
                    $total_compras += $valorCompra;
                ?>
                <td>{{($valorVenta)}}</td>
                <td>{{($valorCompra)}}</td>
                <td>{{($valorVenta - $valorCompra)}}</td>
                <td>{{((($valorVenta - $valorCompra)/$valorCompra))*100}}</td>

            </tr>
        @endforeach
    @empty
        <tr>
            <td>No se han registrado facturas</td>
        </tr>
    @endforelse    
    @if(count($facturas))
        <tr>
            <th class="center-align">TOTAL DESCUENTOS</th><td></td>
            <td >{{$total_descuentos}}</td>
            <td ></td>
            <td >{{$total_descuentos}}</td>
            <td ></td>
        </tr>
        <tr>
            <th class="center-align">TOTAL VALOR PUNTOS</th><td></td>
            <td >{{$total_valor_puntos}}</td>
            <td ></td>
            <td >{{$total_valor_puntos}}</td>
            <td ></td>
        </tr>
        <tr>
            <th class="center-align">TOTAL</th><td></td>
            <td>{{($total_facturas-$total_descuentos-$total_valor_puntos)}}</td>
            <td>{{($total_compras)}}</td>
            <td>{{($total_facturas - $total_compras - $total_descuentos - $total_valor_puntos)}}</td>
            @if($total_compras > 0)
                <td>{{((($total_facturas - $total_descuentos - $total_compras - $total_valor_puntos)/$total_compras))*100}}</td>
            @else
                <td></td>
            @endif
        </tr>
    @endif  
    </tbody>
</table>
<table class="table centered highlight margin-top-50">
    <thead>
        <tr>
            <th colspan="7" style="border-bottom:1px solid rgba(0,0,0,.5);">COSTOS FIJOS</th>
        </tr>
        <tr>
            <th>Fecha</th>
            <th>Item</th>
            <th>Valor</th>
        </tr>
    </thead>
    <tbody >
    @if(isset($reporte))
        <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
    @endif
    @forelse($costosFijos as $c)
            <?php
            $total_gastos += $c->valor;
            ?>
            <tr>
                <td >{{$c->fecha}}</td>
                <td>{{$c->nombre}}</td>
                <td>{{($c->valor)}}</td>
            </tr>
    @empty
        <tr>
            <td colspan="7">No se han registrado costos fijo</td>
        </tr>
    @endforelse
    @if(count($costosFijos))
        <tr>
            <th class="center-align">TOTAL</th><td></td>
            <td>{{($total_gastos)}}</td>
        </tr>
    @endif
    </tbody>
</table>
<table class="table centered highlight margin-top-50">
    <thead>
        <tr>
            <th colspan="7" style="border-bottom:1px solid rgba(0,0,0,.5);">GASTOS DIARIOS</th>
        </tr>
        <tr>
            <th>Fecha</th>
            <th>Descripción</th>
            <th>Usuario</th>
            <th>Valor</th>
        </tr>
    </thead>
    <tbody >
    @if(isset($reporte))
        <tr><td></td><td></td><td></td><td></td></tr>
    @endif
    @forelse($gastosDiarios as $gs)
            <tr>
                <td >{{(string)$gs->created_at}}</td>
                <td>{{$gs->descripcion}}</td>
                <td>{{$gs->usuario}}</td>
                <td>{{($gs->valor)}}</td>
            </tr>
    @empty
        <tr>
            <td colspan="7">No se han registrado gastos diarios</td>
        </tr>
    @endforelse
    @if(count($gastosDiarios))
        <tr>
            <th colspan="3" class="center-align">TOTAL</th>
            <td>{{($totalGastosDiarios)}}</td>
        </tr>
    @endif
    </tbody>
</table>