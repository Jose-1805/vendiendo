<?php
    $diferencia = $totalFacturas - ($totalCostosFijos + $totalCompras + $totalGastosDiarios);

    $total_costos_gastos = $totalCostosFijos + $totalCompras + $totalGastosDiarios;
    $ganancia = 0;
    $perdida = 0;

    //$rentabilidad = $totalFacturas == 0 ? 0 : ($diferencia * 100)/$totalFacturas;
    $rentabilidad = $total_costos_gastos == 0 ? 0 : ($diferencia / $total_costos_gastos) * 100;
    //dd($rentabilidad);
    if($diferencia > 0){
        $ganancia = $diferencia;
    }else {
        $perdida = $diferencia * -1;
    }
?>

@if($totalFacturas != 0)
    <table class="table centered highlight">
        <thead>
            <th>Ventas sin IVA</th>
            <th>Costos compras</th>
            <th>Costos fijos</th>
            <th>Gastos diarios</th>
            <th>Utilidad</th>
            <th>Perdida</th>
            <th>% Rentabilidad neta</th>
            <th>Detalle</th>
        </thead>
        <tbody>
            <tr>
                <td>${{number_format($totalFacturas,2,",",".")}}</td>
                <td>${{number_format($totalCompras,2,",",".")}}</td>
                <td>${{number_format($totalCostosFijos,2,",",".")}}</td>
                <td>${{number_format($totalGastosDiarios,2,",",".")}}</td>
                <td>${{number_format($ganancia,2,",",".")}}</td>
                <td>${{number_format($perdida,2,",",".")}}</td>
                <td>{{number_format($rentabilidad,2,",",".")."%"}}</td>
                <td><a href="{{url('/reporte/perdidas-ganancias-detalle?fecha_inicio='.$fecha_inicio.'&fecha_fin='.$fecha_fin.'&almacen='.$almacen)}}"><i class="fa fa-chevron-right"></i></a></td>
            </tr>
        </tbody>
    </table>
@else
    <center><h6>Sin resultados...</h6></center>
@endif