<table class="table centered highlight">
    <thead>
    <th>{{\App\TildeHtml::TildesToHtml('Número')}}</th>
    <th>Estado</th>
    <th>Subtotal</th>
    <th>Iva</th>
    <th>Total</th>
    <th>Vlr. Puntos</th>
    <th>Vlr. Medios de pago</th>
    <th>Descuento</th>
    <th>Efectivo</th>
    </thead>
    <tbody>
    @if(isset($reporte))
        <tr><td></td><td></td><td></td></tr>
    @endif
    <?php
    $full_subtotal = 0;
    $full_iva = 0;
    $full_descuento = 0;
    $full_valor_puntos = 0;
    $full_facturas = 0;
    $full_valor_medios_pago = 0;
    $full_efectivo = 0;
    ?>
    @forelse($facturas as $factura)
        <?php
            $valor_medios_pago = $factura->getValorMediosPago();
            $valor_puntos = 0;
            $token_puntos = \App\Models\TokenPuntos::where("factura_id",$factura->id)->first();
                if($token_puntos) $valor_puntos = $token_puntos->valor;

            $efectivo = ($factura->subtotal + $factura->iva)-($valor_puntos + $valor_medios_pago + $factura->descuento);
        ?>
        <tr>
            <td >{{$factura->numero}}</td>
            <td >{{$factura->estado}}</td>
            <td >{{number_format($factura->subtotal,2,',','')}}</td>
            <td >{{number_format($factura->iva,2,',','')}}</td>
            <td >{{number_format(($factura->iva+$factura->subtotal),2,',','')}}</td>
            <td >{{number_format($valor_puntos,2,',','')}}</td>
            <td >{{number_format($valor_medios_pago,2,',','')}}</td>
            <td >{{number_format($factura->descuento,2,',','')}}</td>
            <td >{{number_format($efectivo,2,',','')}}</td>
        </tr>
        <?php
        $full_subtotal += $factura->subtotal;
        $full_iva += $factura->iva;
        $full_descuento += $factura->descuento;
        $full_valor_puntos += $valor_puntos;
        $full_facturas += ($factura->subtotal + $factura->iva);
        $full_valor_medios_pago += $valor_medios_pago;
        $full_efectivo += $efectivo;
        ?>
    @empty
        <tr>
            <td colspan="5" class="center-align">No se encontraron resultados</td>
        </tr>
    @endforelse
    @if(count($facturas))
        <?php $estilo = ""; ?>
        @if(!isset($reporte))
            <?php $estilo  = "border-top: 1px solid #00c0e4;"?>
        @endif
        <tr style="{{$estilo}}">
            <th class="center-align" colspan="2">TOTAL</th>
            <td >{{number_format($full_subtotal,2,',','')}}</td>
            <td >{{number_format($full_iva,2,',','')}}</td>
            <td >{{number_format($full_facturas,2,',','')}}</td>
            <td >{{number_format($full_valor_puntos,2,',','')}}</td>
            <td >{{number_format($full_valor_medios_pago,2,',','')}}</td>
            <td >{{number_format($full_descuento,2,',','')}}</td>
            <td >{{number_format($full_efectivo,2,',','')}}</td>
        </tr>
    @endif
    </tbody>
</table>
@if(!isset($reporte))
    {!! $facturas->render() !!}
@endif
