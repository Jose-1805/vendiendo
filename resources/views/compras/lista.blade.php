

<div class="row">
    <div class="col s12 m12 content-table-slide" id="div-lista-compras">
        <table id="ComprasTabla" class="bordered highlight centered">
            <thead>
                <tr>
                    <th><i class="fa fa-circle font-xsmall"></i> Número</th>
                    <th><i class="fa fa-circle font-xsmall"></i> Valor</th>
                    <th><i class="fa fa-circle font-xsmall"></i> Fecha</th>
                    <th>Proveedor</th>
                    <th>Usuario</th>
                    <th><i class="fa fa-circle font-xsmall"></i> Estado compra</th>
                    <th><i class="fa fa-circle font-xsmall"></i> Estado pago</th>
                    <th style="width:80px !important;">Opciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@if(count($compras))
<div id="modal-estados-compra" class="modal modal-fixed-footer">
    <div class="modal-content">
        <p class="titulo-modal">Actualizar estados de la compra No. <b id="numeroCompra"></b></p>
        <div id='mensaje-confirmacion-estados-compra'></div>

        <form name="estados-compras-form" id="estados-compras-form" action="{{url('compra/update')}}" class="col s12">
            <div class="col s12" id="contenido-estados-compra" style="width: 100%">

            </div>
        </form>
    </div>

    <div class="modal-footer">
        <div class="progress hide" id="progress-action-form-estados-compra" style="top: 30px;margin-bottom: 30px;">
            <div class="indeterminate cyan"></div>
        </div>
        <div class="col s12" id="contenedor-botones-estados-compra">
            <a href="#!" class="modal-close cyan-text btn-flat">Cerrar</a>
            <a id="btn-action-form-estados-compra" class="red-text btn-flat" onclick="actualizarEstados()">Cambiar estados</a>
        </div>
    </div>
</div>
@endif
<div id="modal-abonos-compra" class="modal modal-fixed-footer" >

    <!--<td><a href="#modal-cuadre-caja" class="modal-trigger tooltipped" data-tooltip="Abonos"><i class="fa fa-paypal"></i></a></td>-->

    <div id="lista-abonos-compra">
        <div id='mensaje-confirmacion-abonos-compra'></div>
    </div>

    <div class="modal-footer">
        <div class="progress hide" id="progress-action-form-abonos-compra" style="top: 30px;margin-bottom: 30px;">
            <div class="indeterminate cyan"></div>
        </div>
        <div class="col s12" id="contenedor-botones-abonos-compra">
            <a href="#!" class="modal-close cyan-text btn-flat" onclick="window.location.reload()">Cerrar</a>
        </div>
    </div>
</div>

<div id="modal-cuadre-caja" class="modal modal-fixed-footer" >
    <div class="modal-content">
        <p class="titulo-modal">Operaciones de caja</p>
        <div id='mensaje-confirmacion-cuadre-caja'></div>
        {!! Form::open([ 'url' => 'caja/operacion-caja' ,'data-toggle'=>'validator', 'class' => 'form-inline','role'=> 'form','method' => 'POST', 'novalidate', 'id' => 'form-caja',  'autocomplete' =>'off'] ) !!}
            @include('compras.abonos.form_entrar_dinero_caja')
        {!! Form::close() !!}
    </div>
    <div class="modal-footer">
        <div class="progress hide" id="progress-action-form-cuadre-caja" style="top: 30px;margin-bottom: 30px;">
            <div class="indeterminate cyan"></div>
        </div>
        <div class="col s12" id="contenedor-botones-cuadre-caja">
            <a href="#!" class="modal-close cyan-text btn-flat" onclick="cerrarModalCuadreCaja()">Cerrar</a>
        </div>
    </div>
</div>