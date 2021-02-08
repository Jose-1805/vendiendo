<?php
        $total_ventas_cobrar = $compras_proveedor_all->sum('valor');
        if(Auth::user()->bodegas == 'si')
            $total_abonos = App\Models\ABCompra::getTotalAbonos();
        else
            $total_abonos = App\Models\Compra::getTotalAbonos();
?>
<tr>
    <td colspan="5" style="text-align: center;font-size: 30px;"><p class="titulo"><strong style="color: #0584b1;">Cuentas por pagar general</strong>"A fecha {{ \App\General::fechaActualString() }}"</p></td>
</tr>

<table id="informacion-general">
    <tr>
        <th></th>
        <th style="color: #0584b1;">Total compras por pagar</th>
        <th style="color: #0584b1;">Total abonos</th>
        <th style="color: #0584b1;">Saldo por pagar</th>
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
            <th>Proveedor</th>
            <th># compras pendientes de pago</th>
            <th>Valor compras</th>
            <th>Valor abonos</th>
            <th>Saldo a pagar</th>
        </tr>
        </thead>
        <tbody>
        @forelse($compras_proveedor as $cp)
            <tr>
                <td>{{ $cp->proveedor->nombre }}</td>
                <td style="text-align: center;">{{ $cp->num_compras }}</td>
                <td>{{ number_format($cp->valor_compras,2,',','')}}</td>
                <?php
                    if(Auth::user()->bodegas == 'si')
                        $abonos_proveedor = App\Models\ABCompra::getAbonosByProveedor($cp->proveedor_id);
                    else
                        $abonos_proveedor = App\Models\Compra::getAbonosByProveedor($cp->proveedor_id);
                    //$abonos_proveedor = $cp->abonos;
                ?>
                <td>{{number_format($abonos_proveedor,2,',','') }}</td>
                <td>{{number_format($cp->valor_compras - $abonos_proveedor,2,',','')}}</td>
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