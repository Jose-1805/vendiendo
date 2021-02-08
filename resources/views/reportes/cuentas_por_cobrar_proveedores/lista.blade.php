<?php
$numColumns = 7;
?>
<div class="col s12" id="datos-proveedor">
   <p class="col s12 m12 text-center" style="display: inline-block;font-size: 20px;"><strong style="color: #0584b1;">Cuentas por cobrar</strong><br>${{number_format($ValorTotalCuentasXCobrar,2,',','.')  }}</p>
   {{--<p class="col s12 m4 text-center" style="display: inline-block;font-size: 20px;"><strong style="color: #0584b1;">Cuentas cobradas: </strong><br>${{ number_format($valorTotalCuentasCobradas,2,',','.') }}</p>
   <p class="col s12 m4 text-center" style="display: inline-block;font-size: 20px;"><strong style="color: #0584b1;">Cuentas por cobrar: </strong><br>${{ number_format($ValorTotalCuentasXCobrar,2,',','.') }}</p>--}}
</div>
<div class="col s12 divider"></div>
<?php
    if ($pos == '')
        $display = 'none';
    else
        $display = 'block';
?>
<div id="div-lista-cc" style="display: {{ $display }}">


    <table class="bordered highlight responsive-table centered" id="t_rep_cuentas_x_cobrar_proveedores" width="100%">
        <thead>
        <tr>
            <th>No. Compra</th>
            <th>Elemento</th>
            <th width="25px">Cantidad devolución</th>
            <th>Valor devolución</th>
            <th>Motivo</th>
            <th>Fecha</th>
            <th>Proveedor</th>
            <th>Estado<br><label style="font-size: 10px">Sin pagar/Pagada</label></th>
        </tr>
        </thead>
        <tbody id="datos"></tbody>
    </table>
</div>
<div id="modal-forma-pago" class="modal modal-fixed-footer " style=" width: 30% !important;height: 40%">
    <div class="modal-content">
        <p class="titulo-modal">Forma de pago de la cuenta</p>
        <div id='mensaje-confirmacion-estados-compra'></div>

        <div class="input-field col s12 m12">
            <div class="input-field col s6 m6">
                <input name="forma_pago" type="radio" value="Efectivo" id="Efectivo" checked/>
                <label for="Efectivo">Efectivo</label>
            </div>
            <div class="input-field col s6 m6">
                <input name="forma_pago" type="radio" value="Mercancia" id="Mercancia"/>
                <label for="Mercancia">Mercancia</label>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <div class="progress hide" id="progress-action-form-forma-pago" style="top: 30px;margin-bottom: 30px;">
            <div class="indeterminate cyan"></div>
        </div>
        <div class="col s12" id="contenedor-botones-forma-pago">
            <a href="#!" class="modal-close cyan-text btn-flat" onclick="window.location.reload()">Cerrar</a>
            <a id="btn-action-form-forma-pago" class="red-text btn-flat" onclick="PagarCC()">Aceptar</a>
        </div>
    </div>
</div>

@section('js')
    @parent
    <script src="{{asset('js/productos/funciones.js')}}"></script>
    <script src="{{asset('js/compras/devolutionAction.js')}}"></script>
@stop