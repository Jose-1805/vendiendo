<?php
    $numColumns = 6;
?>


<table id="FacturasTabla" class="" style="width:100%;">
    <thead>
        <tr>
            <th class="hide"></th>
            <th><i class="fa fa-circle font-xsmall"></i> Número</th>
            <th>Valor</th>
            <th><i class="fa fa-circle font-xsmall"></i> Fecha</th>
            <th>Cliente</th>
            <th>Usuario</th>
            <th>Caja</th>
            <th><i class="fa fa-circle font-xsmall"></i> Estado</th>
            @if((\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","facturas","inicio")) && \Illuminate\Support\Facades\Auth::user()->cajaAsignada())
                <th>Abonos</th>
            @else
                <th class="hide"></th>
            @endif
            <th>Detalles</th>
        </tr>
    </thead>
</table>

<div id="modal-abonos" class="modal modal-fixed-footer">
    <div class="modal-content">
        <p class="titulo-modal">Abonos</p>
        <div class="" id="load-info-abonos">
            <p class="center-align">Cargando información<i class="fa fa-spin fa-spinner text-cyan"></i></p>
        </div>
        <div class="row hide" id="info-abonos">
        </div>
    </div>

    <div class="modal-footer">
        <div class="col s12 hide" id="contenedor-botones-abonos">
            <a href="#!" class="green-text btn-flat" onclick="javascript: guardarAbono()">Aceptar</a>
            <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-abonos">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>
<div id="modal-pagar-cliente" class="modal modal-fixed-footer modal-sm" style="/*height: 400px !important;min-height: 400px;*/">
    <div class="modal-content">
        <p class="titulo-modal">Pagar</p>
        <div class="col s12">
            <strong>Total a pagar </strong>
            <p id="total-pagar-modal-cliente">$ 0</p>
        </div>
        <div class="col s12" id="div-efectivo-cliente">

        </div>
        <div class="col s12" id="div-regreso-cliente">
            <strong>Regreso</strong>
            <p id="regreso-modal-cliente">$ 0</p>
            <label id='mensaje-efectivo' style='color: red'></label>
        </div>

    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-pagar-modal">
            <a class="green-text btn-flat" onclick="javascript: validarPagoFactura()">Realizar pago</a>
            <a class="green-text btn-flat" onclick="javascript: ejecutarCambioEstadoFactura()">Omitir calculo</a>
            <a class="modal-close cyan-text btn-flat" >Cancelar</a>
        </div>

        <div class="progress hide" id="progress-pagar-modal">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>
<div id="modal-pendiente-pagar" class="modal modal-fixed-footer modal-small" style="height: 400px !important;min-height: 350px;">
    <div class="modal-content">
        <p class="titulo-modal">Información de pagos y notificaciones</p>
        @include('templates.mensajes',["id_contenedor"=>"pendiente-pagar"])
        {!! Form::open(["id"=>"form-datos-pago","class"=>"row"]) !!}
        <div class="col s12 m12">
            {!! Form::label("dias_credito","Dias de credito",["class"=>"active"]) !!}
            {!! Form::text("dias_credito",30,["id"=>"dias_credito","class"=>"num-entero"]) !!}
        </div>
        <div class="col s12 m6 input-field">
            {!! Form::text("numero_cuotas",1,["id"=>"numero_cuotas","class"=>"num-entero","onblur"=>"maxNumeroCuotas()"]) !!}
            {!! Form::label("numero_cuotas","Número de cuotas",["class"=>"active"]) !!}
        </div>
        <div class="col s12 m6 input-field">
            {!! Form::select("tipo_periodicidad_notificacion",[""=>"Seleccione","quincenal"=>"Quincenal","mensual"=>"Mensual","nunca"=>"Nunca"],null,["id"=>"tipo_periodicidad_notificacion"]) !!}
            {!! Form::label("tipo_periodicidad_notificacion","Tipo periodicidad de la notificación",["class"=>"active","style"=>"margin-top:25px !important;"]) !!}
        </div>
        <div class="hide" id="periodicidad">
            <div class="col s12 m6 input-field">
                {!! Form::date("fecha_primera_notificacion",date("Y-m-d"),["id"=>"fecha_primera_notificacion"]) !!}
                {!! Form::label("fecha_primera_notificacion","Fecha primera notificacion",["class"=>"active"]) !!}
            </div>
        </div>
        {!! Form::close() !!}

    </div>
    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-pendiente-pagar-modal">
            <a class="green-text btn-flat" onclick="javascript: cargarDatosNotificacion()">Aceptar</a>
            <a class="modal-close cyan-text btn-flat" onclick="window.location.reload(true)">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-pendiente-pagar-modal">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>
