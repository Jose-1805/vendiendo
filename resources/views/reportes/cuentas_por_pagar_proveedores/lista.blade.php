<?php
$total_compras_por_pagar = $compras_proveedor_all->sum('valor');
if(Auth::user()->bodegas == 'si')
$total_abonos = App\Models\ABCompra::getTotalAbonos();
else
$total_abonos = App\Models\Compra::getTotalAbonos();
?>
<div class="col s12" id="datos-facturas">
    <p class="col s12 m4 text-center" style="display: inline-block;font-size: 20px;"><strong style="color: #0584b1;">Total compras por pagar </strong><br>${{number_format($total_compras_por_pagar,2,',','.')  }}</p>
    <p class="col s12 m4 text-center" style="display: inline-block;font-size: 20px;"><strong style="color: #0584b1;">Total abonos </strong><br>${{ number_format($total_abonos,2,',','.') }}</p>
    <p class="col s12 m4 text-center" style="display: inline-block;font-size: 20px;"><strong style="color: #0584b1;">Saldo por pagar </strong><br>${{ number_format($total_compras_por_pagar - $total_abonos,2,',','.') }}</p>
</div>
<div class="col s12 m12 divider"></div>
<?php
if ($pos == '')
    $display = 'none';
else
    $display = 'block';
?>
<div id="div-lista-proveedores-compras" class="col s12 content-table-slide" style="display: {{ $display }}">
     <table class="bordered highlight centered" id="t_rep_cuentas_x_pagar_proveedores" width="100%">
        <thead>
        <tr>
            <th>Proveedor</th>
            <th># compras pendientes de pago</th>
            <th>Valor compras</th>
            <th>Valor abonos</th>
            <th>Saldo a pagar</th>
            <th>Detalle</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>