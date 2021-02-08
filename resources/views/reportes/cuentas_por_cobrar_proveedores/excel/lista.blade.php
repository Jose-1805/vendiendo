<?php
$numColumns = 9;
?>
<tr>
    <td colspan="7" style="text-align: center;font-size: 30px;"><strong style="color: #0584b1;">Cuentas por cobrar a proveedores</strong> "Devoluciones a fecha {{ \App\General::fechaActualString() }}"</td><br>
</tr>
<table id="informacion-general">
    <tr>
        <th></th>
        <th></th>
        <th></th>
        <th style="color: #0584b1;">Cuentas por cobrar</th>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td>{{number_format($ValorTotalCuentasXCobrar,2,',','')  }}</td>
    </tr>
</table>
<br>
<table>
        <thead>
            <tr>
                <th>No. Compra</th>
                <th>Elemento</th>
                <th width="25px">Cantidad devoluci&oacute;n</th>
                <th>Valor devoluci&oacute;n</th>
                <th>Motivo</th>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
        @forelse($cuentasXpagar as $cxp)
            {{--dd($cxp)--}}
            <tr>
                <td>{{$cxp->compra->numero}}</td>
            @if($cxp->tipo_compra == 'Producto')
                <td>{{$cxp->producto->nombre}}</td>
            @else
                <td>{{$cxp->materia->nombre}}</td>
            @endif
                <td style="text-align: center">{{$cxp->cantidad_devolucion}}</td>
                <td>{{number_format($cxp->valor_devolucion,2,',','')}}</td>
                <td style="text-align: left">{{$cxp->motivo}}</td>
                <td>{{$cxp->fecha_devolucion}}</td>
                <td>{{$cxp->proveedor->nombre}}</td>
                <td>{{$cxp->estado}}</td>
            </tr>
        @empty
            <tr>
                <td colspan="{{$numColumns}}" class="center"><p>Sin resultados</p></td>
            </tr>
        @endforelse
        </tbody>
</table>

