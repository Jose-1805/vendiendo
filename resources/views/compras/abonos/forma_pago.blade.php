<div id="modal-forma-abono" class="modal modal-fixed-footer " style=" width: 50% !important;height: 60%">
    <div class="modal-content">
        <p class="titulo-modal">Notificación de abono de la cuenta</p>
        <div id='mensaje-confirmacion-estados-compra'></div>

        <div class="input-field col s12 m12">
            {!! Form::open(["id"=>"form-datos-pago","class"=>"row"]) !!}
            <div class="col s12 m6 input-field">
                {!! Form::text("dias_credito",30,["id"=>"dias_credito","class"=>"num-entero", "onblur"=>"maxNumeroCuotas()"]) !!}
                {!! Form::label("dias_credito","Dias de crédito",["class"=>"active"]) !!}
            </div>
            <div class="col s12 m6 input-field">
                {!! Form::text("numero_cuotas",1,["id"=>"numero_cuotas","class"=>"num-entero", "onblur"=>"maxNumeroCuotas()"]) !!}
                {!! Form::label("numero_cuotas","Número de cuotas",["class"=>"active"]) !!}
            </div>
            <div class="col s12 m6 input-field">
                {!! Form::select("tipo_periodicidad_notificacion",[""=>"Seleccione","días"=>"Días","meses"=>"Meses","nunca"=>"Nunca"],null,["id"=>"tipo_periodicidad_notificacion"]) !!}
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
    </div>

    <div class="modal-footer">
        <div class="progress hide" id="progress-action-form-forma-abono" style="top: 30px;margin-bottom: 30px;">
            <div class="indeterminate cyan"></div>
        </div>
        <div class="col s12" id="contenedor-botones-forma-abono">
            <a href="#!" class="modal-close cyan-text btn-flat" onclick="window.location.reload()">Cerrar</a>
            <a id="btn-action-form-forma-abono" class="red-text btn-flat" onclick="realizarCompraConNotificaciones()">Aceptar</a>
        </div>
    </div>
</div>