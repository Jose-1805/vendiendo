<?php
$meses = [
        1 => "Enero",
        2 => "Febrero",
        3 => "Marzo",
        4 => "Abril",
        5 => "Mayo",
        6 => "Junio",
        7 => "Julio",
        8 => "Agosto",
        9 => "Septiembre",
        10 => "Octubre",
        11 => "Noviembre",
        12 => "Diciembre",
];
?>
<table class="table centered highlight">
    <thead>
        @if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'no')
            <th>Almacén</th>
        @endif
        <th>Fecha</th>
        <th>Valor fijado</th>
        <th>Valor acumulado</th>
        <th>Cumplimiento</th>
    </thead>
    <tbody>
    @if(isset($reporte))
        <tr><td></td><td></td><td></td><td>
    @endif
    @forelse($objetivosVentas as $o)
        <tr>
            @if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'no')
                <td >{{$o->almacen->nombre}}</td>
            @endif
            <td >{{$meses[$o->mes]."/".$o->anio}}</td>
            <td >{{number_format($o->valor,0,',','')}}</td>
            @if(Auth::user()->bodegas == 'si')
                @if(Auth::user()->admin_bodegas == 'si')
                    @if($almacen && !is_null($almacen) && $almacen != 0)
                        <?php $acumulado = $o->valorAcumulado($o->almacen_id); ?>
                    @else
                        <?php $acumulado = $o->valorAcumulado(); ?>
                    @endif
                @else
                    <?php $acumulado = $o->valorAcumulado($o->almacen_id); ?>
                @endif
            @else
                <?php $acumulado = $o->valorAcumulado(); ?>
            @endif
            <td >{{number_format($acumulado,2,',','')}}</td>
            <td >{{number_format(($acumulado * 100)/ $o->valor,2,',','')}}%</td>
        </tr>
    @empty
        <tr>
            <td colspan="3" class="center-align">No se encontraron resultados</td>
        </tr>
    @endforelse
    </tbody>
</table>
@if(!isset($reporte))
    {!! $objetivosVentas->render() !!}
@endif
