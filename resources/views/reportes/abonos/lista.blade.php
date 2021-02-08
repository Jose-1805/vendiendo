<?php
     $className = $tipo;// explode("\\",get_class($elementos[0]))[count(explode("\\",get_class($elementos[0])))-1];;

?>
<table class="table centered highlight">
    <thead>
        <th>{!! \App\TildeHtml::TildesToHtml('Número') !!}</th>
        @if($className == "factura")
            <th>{!! \App\TildeHtml::TildesToHtml('Cliente/Identificación') !!}</th>
        @elseif($className == "compra")
            <th>Proveedor/NIT</th>
        @endif
        <th>Fecha</th>
        <th>Valor</th>
        <th>Saldo</th>
        <th>Cantidad de cuotas</th>
        <th>Cuotas pagadas</th>
        <th>{!! \App\TildeHtml::TildesToHtml('Fecha última cuota') !!}</th>
    </thead>
    <tbody>
    @if(isset($reporte))
        <tr><td></td><td></td><td></td></tr>
    @endif
    <?php
        $full_saldos = 0;
        $full_valores = 0;
    ?>
    @forelse($elementos as $elemento)
        @if($className == "factura")
            @include('reportes.abonos.factura_row',["elemento",$elemento])
            <?php
                $full_valores += $elemento->subtotal + $elemento->iva;
                $full_saldos+= $elemento->getSaldo();
            ?>
        @elseif($className == "compra")
            @include('reportes.abonos.compra_row',["elemento",$elemento])
            <?php
                $full_valores += $elemento->valor;
                $full_saldos+= $elemento->getSaldo();
            ?>
        @endif
    @empty
        <tr>
            <td colspan="7" class="center-align">No se encontraron resultados</td>
        </tr>
    @endforelse
    @if(count($elementos))
        <?php $estilo = ""; ?>
        @if(!isset($reporte))
            <?php $estilo  = "border-top: 1px solid #00c0e4;"?>
        @endif
        <tr style="{{$estilo}}">
            <td colspan="4" style="text-align: center;"><strong>Total valores</strong></td>
            <td colspan="4" style="text-align: center;">{{number_format($full_valores,2,',','')}}</td>
        </tr>
        <tr style="{{$estilo}}">
            <td colspan="4" style="text-align: center;"><strong>Total saldos</strong></td>
            <td colspan="4" style="text-align: center;">{{number_format($full_saldos,2,',','')}}</td>
        </tr>
    @endif
    </tbody>
</table>
@if(!isset($reporte))
    {!! $elementos->render() !!}
@endif