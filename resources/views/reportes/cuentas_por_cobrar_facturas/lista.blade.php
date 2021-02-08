<?php
    $total_ventas_cobrar = $clientes_facturas_all->sum('subtotal') +  $clientes_facturas_all->sum('iva');

    if(!isset($almacen))$almacen = null;

    if(Auth::user()->bodegas == 'si')
        $total_abonos = App\Models\ABFactura::getTotalAbonos($almacen);
    else
        $total_abonos = App\Models\Factura::getTotalAbonos();

?>
@if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
    <div class="col s12 m4 l3 input-field right">
        {!! Form::label("almacen","Almacén",["class"=>"active"]) !!}
        {!! Form::select("almacen",['Todos']+\App\Models\Almacen::permitidos()->lists('nombre','id'),$almacen,['id'=>'almacen']) !!}
    </div>
@endif

<div class="col s12" id="datos-facturas">
    <p class="col s12 m4 text-center" style="display: inline-block;font-size: 20px;"><strong style="color: #0584b1;">Total ventas por cobrar </strong><br>${{number_format($total_ventas_cobrar,2,',','.')  }}</p>
    <p class="col s12 m4 text-center" style="display: inline-block;font-size: 20px;"><strong style="color: #0584b1;">Total abonos </strong><br>${{ number_format($total_abonos,2,',','.') }}</p>
    <p class="col s12 m4 text-center" style="display: inline-block;font-size: 20px;"><strong style="color: #0584b1;">Saldo por cobrar </strong><br>${{ number_format($total_ventas_cobrar - $total_abonos,2,',','.') }}</p>
</div>
<div class="col s12 m10 divider"></div>
<?php
if ($pos == '')
    $display = 'none';
else
    $display = 'block';
?>
<div id="div-lista-clientes-facturas" class="col s12 content-table-slide" style="display: {{ $display }}">
    <table class="bordered highlight centered" id="tabla_reporte_cuentas_x_cobrar_factura" width="100%">
        <thead>
        <tr>
            <th>Cliente</th>
            <th># facturas pendietes de pago</th>
            <th>Valor facturas</th>
            <th>Valor abonos</th>
            <th>Saldo a cobrar</th>
            <th>Detalle</th>
        </tr>
        </thead>
    </table>

</div>
