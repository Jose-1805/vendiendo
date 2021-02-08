<?php
        $total_ventas_cobrar = $clientes_facturas_all->sum('subtotal') +  $clientes_facturas_all->sum('iva');
        if(Auth::user()->bodegas == 'si')
            $total_abonos = App\Models\ABFactura::getTotalAbonos($almacen);
        else
            $total_abonos = App\Models\Factura::getTotalAbonos();
?>
<tr>
    <td colspan="5" style="text-align: center;font-size: 30px;"><p class="titulo"><strong style="color: #0584b1;">Cuentas por cobrar general</strong>"Facturas a {{ \App\General::fechaActualString() }}"</p></td>
</tr>

<table id="informacion-general">
    <tr>
        <th></th>
        <th style="color: #0584b1;">Total ventas por cobrar</th>
        <th style="color: #0584b1;">Total abonos</th>
        <th style="color: #0584b1;">Saldo por cobrar</th>
    </tr>
    <tr>
        <td></td>
        <td>{{number_format($total_ventas_cobrar,2,',','')  }}</td>
        <td>{{ number_format($total_abonos,2,',','') }}</td>
        <td>{{ number_format($total_ventas_cobrar - $total_abonos,2,',','') }}</td>
    </tr>
</table>
<br>
<div id="div-lista-clientes-facturas">
    <table>
        <thead>

        <tr>
            <th>Cliente</th>
            <th># facturas pendietes de pago</th>
            <th>Valor facturas</th>
            <th>Valor abonos</th>
            <th>Saldo a cobrar</th>
        </tr>
        </thead>
        <tbody>
        @forelse($clientes_facturas as $cf)
            <tr>
                <td>{{ $cf->cliente->nombre }}</td>
                <td style="text-align: center;">{{ $cf->num_facturas }}</td>
                <td>{{ number_format($cf->valor_facturas,2,',','')}}</td>
                <?php
                    if(Auth::user()->bodegas == 'si')
                        $abonos_cliente = App\Models\ABFactura::getAbonosByCliente($cf->cliente_id,$almacen);
                    else
                        $abonos_cliente = App\Models\Factura::getAbonosByCliente($cf->cliente_id);
                ?>
                <td>{{number_format($abonos_cliente,2,',','') }}</td>
                <td>{{number_format($cf->valor_facturas - $abonos_cliente,2,',','')}}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5">
                    Sin resultados
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>