<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
@section('contenido')
    <div id="contenedor-detalle-compra" class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        @include('compras.detalles.index')
    </div>
@endsection
<div id="modal-detalle-compra" class="modal modal-fixed-footer ">
    <div class="modal-content">
        <p id="titulo-devolucion" class="titulo-modal"></p>
        {!!Form::open(['url'=>'compra/devolucion/'.$compra->id,'id'=>'devolucion-form', 'method'=>'POST'])!!}
            <div class="col s12" id="contenido-detalle-compra" style="width: 100%">

            </div>
        {!!Form::close()!!}
    </div>
    <div class="modal-footer">
        <div class="progress hide" id="progress-action-form-devolucion" style="top: 30px;margin-bottom: 30px;">
            <div class="indeterminate cyan"></div>
        </div>
        <div class="col s12" id="contenedor-botones-detalle-compra">
            <a href="#!" class="modal-close cyan-text btn-flat">Cerrar</a>
            <a id="btn-action-form-devolucion" class="red-text btn-flat" onclick="ejecutarDevolucion()">Ejecutar devolución</a>
        </div>
    </div>
</div>
<div id="modal-forma-pago-compra" class="modal modal-fixed-footer " style=" width: 30% !important;height: 40%">
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
            <a href="#!" class="modal-close cyan-text btn-flat" onclick="cancelarPagoDevolucion()">Cerrar</a>
            <a id="btn-action-form-forma-pago" class="red-text btn-flat" onclick="pagarDevolucion()">Aceptar</a>
        </div>
    </div>
</div>


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

@section('js')
    @parent
    <script type="application/javascript" src="{{asset('js/compras/funciones.js')}}"></script>
    <script src="{{asset('js/compras/devolutionAction.js')}}"></script>

@endsection