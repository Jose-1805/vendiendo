<?php
    $total_iva = 0;
    $total_facturas = 0;
    $total_descuentos = 0;
?>
<table class="table centered highlight">
    <thead>
        @if(Auth::user()->bodegas == 'si')
            <th>Almacén</th>
        @endif
        <th>Factura</th>
        <th>Fecha</th>
        <th>Cliente</th>
        <th>Subtotal</th>
        <th>Iva</th>
        <th>Descuento</th>
        <th>Total</th>
    </thead>
    <tbody>
    @if(isset($reporte))
        <tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
    @endif
    @forelse($ventas as $venta)
        <?php
            $total_iva += $venta->iva;
            $total_facturas += $venta->subtotal;
            $total_descuentos += $venta->descuento;
        ?>
        <tr>
            @if(Auth::user()->bodegas == 'si')
                <?php
                    $almacen = \App\Models\Almacen::permitidos()->where('id',$venta->almacen_id)->first()->nombre;
                ?>
                <td >{{$almacen}}</td>
            @endif
            <td >{{$venta->numero}}</td>
            <td >{{$venta->created_at}}</td>
            <td>{!! \App\TildeHtml::TildesToHtml($venta->nombre) !!}</td>
            <td>{{number_format($venta->subtotal, 2, "," , "" )}}</td>
            <td>{{number_format($venta->iva,2,",","")}}</td>
            <td>{{number_format($venta->descuento,2,",","")}}</td>
            <td>{{number_format($venta->subtotal + $venta->iva -$venta->descuento,2,",","")}}</td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="center-align">No se encontraron productos</td>
        </tr>
    @endforelse
    @if(count($ventas))
        <?php $estilo = ""; ?>
        @if(!isset($reporte))
            <?php $estilo  = "border-top: 1px solid #00c0e4;"?>
        @endif
        <tr style="{{$estilo}}">
            <td colspan="5"></td>
            <td><strong>Total sin iva:</strong></td>
            <td>
                {{number_format($total_facturas,2,",","")}}
            </td>
        </tr>
        <tr>
            <td colspan="5"></td>
            <td><strong>Total iva:</strong></td>
            <td>
                {{number_format($total_iva,2,",","")}}
            </td>
        </tr>
        <tr>
            <td colspan="5"></td>
            <td><strong>Total iva:</strong></td>
            <td>
                {{number_format($total_descuentos,2,",","")}}
            </td>
        </tr>
        <tr>
            <td colspan="5"></td>
            <td><strong>Total:</strong></td>
            <td>
                {{number_format($total_facturas + $total_iva - $total_descuentos,2,",","")}}
            </td>
        </tr>
    @endif
    </tbody>
</table>
@if(!isset($reporte))
    {!! $ventas->render() !!}
@endif






