<?php
    $filas = [
        'fecha',
        'saldo_efectivo',
        'space_1',
        'space_2_ventas',
        'ventas_efectivo',
        'ventas_descuento',
        'ventas_medios_pago',
        'ventas_credito',
        'puntos',
        'total_ventas',
        'space_3',
        'space_4_compras',
        'compras_efectivo',
        'compras_credito',
        'total_compras',
        'compras_devolucion_efectivo',
        'space_5',
        'space_6_abonos',
        'abonos_clientes',
        'abonos_proveedores',
        'total_abonos',
        'space_7',
        'space_8_costos',
        'gastos_diarios',
        'costos_fijos',
        'total_costos',
        'space_9',
        'space_10_consignaciones',
        'consignaciones_banco',
        'consignaciones_caja',
        'space_11',
        'space_12_cierre-total-caja',
        'efectivo',
        'otros_medios_pago',
        'totales'
    ];
?>


<table class="table centered highlight table-rotate">

    <tbody>
        @foreach($filas as $fila)
            @if(explode('_',$fila)[0] != 'space')
                <tr>
                    <td>{{strtoupper(str_replace('_',' ',$fila))}}</td>
                    @foreach($cajas as $c)
                        <td>{{$c->$fila}}</td>
                    @endforeach
                </tr>
            @else
                @if(count(explode('_',$fila)) > 2)
                    <tr>
                        <th>
                            {{strtoupper(str_replace('-',' ',explode('_',$fila)[count(explode('_',$fila))-1]))}}
                        </th>
                    </tr>
                @else
                    <tr></tr>
                @endif
            @endif
        @endforeach
    </tbody>
</table>
@if(!isset($reporte))
    {!! $cajas->render() !!}
@endif
