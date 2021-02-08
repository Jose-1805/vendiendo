<table class="table centered highlight">
    <thead>
        <th>Fecha</th>
        <th>Tipo</th>
        <th>{!! \App\TildeHtml::TildesToHtml('Código de barras') !!}</th>
        <th>{!! \App\TildeHtml::TildesToHtml('Descripción') !!}</th>
        <th>Cantidad</th>
        <th>Valor</th>
        <th>Origen</th>
        <th>Destino</th>
    </thead>
    <tbody>
    @if(isset($reporte))
        <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
    @endif
    @forelse($productos as $p)
        <?php
            $tipo = '';
            $valor = $p->costo_calculado + (($p->costo_calculado * $p->iva_calculado)/100);
            if (!is_null($p->estado_facturas) && $p->estado_facturas != '') {
                $tipo = 'Salida (Factura)';
                $valor += (($valor * $p->utilidad_calculada)/100);
            }

            if (!is_null($p->estado_compra) && $p->estado_compra != '') {
                $tipo = 'Entrada (Compra)';
            }
            if (Auth::user()->bodegas == 'si' && !is_null($p->estado_traslado) && $p->estado_traslado != '') {
                if($p->estado_traslado == 'recibido')
                    $tipo = 'Entrada (Traslado)';
                else
                    $tipo = 'Salida (Traslado)';
                $valor += (($valor * $p->utilidad_calculada)/100);
            }
            if (!is_null($p->estado_remision) && $p->estado_remision != '') {
                $tipo = 'Salida (Remisión)';
                $valor += (($valor * $p->utilidad_calculada)/100);
            }

            $valor = $valor * $p->cantidad_vendida;
        ?>
        <tr>
            <td >{!! $p->fecha_creacion !!}</td>
            <td >{!! $tipo !!}</td>
            <td >{!! $p->barcode !!}</td>
            <td class="truncate" style="max-width: 300px !important;">{!! substr(\App\TildeHtml::TildesToHtml($p->descripcion),0,50) !!}</td>
            <td >{{$p->cantidad_vendida}}</td>
            <td>{{$valor}}</td>
            <td>{{$p->origen}}</td>
            <td>{{$p->destino}}</td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="center-align">No se encontraron productos</td>
        </tr>
    @endforelse
    </tbody>
</table>
