<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('js')
    @parent
    <script src="{{asset('js/caja/cajaAction.js')}}"></script>
    <script>
        $(function(){
            cargarTablaCajaMayor();
        })
    </script>
@stop

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        @if(!\App\Models\Caja::abierta() && (Auth::user()->bodegas == 'no' || (Auth::user()->bodegas == 'si' && AUth::user()->admin_bodegas == 'si')))
            <a style="/*margin-top: 50px;*/" class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Iniciar caja mayor" href="{{url("caja/create")}}"><i class="fa fa-play"></i></a>
        @else
            @if(\Illuminate\Support\Facades\Auth::user()->bodegas == 'si' && \Illuminate\Support\Facades\Auth::user()->admin_bodegas =='si')
                <a style="/*margin-top: 50px;*/" class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Caja de almacén" href="{{url("caja/almacen")}}"><i class="fa fa-inbox"></i></a>
            @endif
        @endif
        <p class="titulo">Caja maestra</p>
        @include("templates.mensajes",["id_contenedor"=>"lista-cajas"])

        <table class="bordered highlight centered" id="tabla_caja_mayor" style="width: 100%;">
            <thead>
            <tr>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Efectivo inicial</th>
                <th>Efectivo final</th>
                <th>Usuario</th>
                <th>Opciones</th>
            </tr>
            </thead>
        </table>
    </div>

    <div id="modal-operacion-caja" class="modal modal-fixed-footer modal-small" style="min-height: 60%;" >
        <div class="modal-content">
            <p class="titulo-modal">Operación caja maestra/banco</p>
            @include('templates.mensajes',["id_contenedor"=>"operacion-caja"])
            @if(count(\App\Models\CuentaBancaria::lista()))
                {!! Form::open(["id"=>"form-operacion-caja","class"=>"col s12"]) !!}
                    @if(!(\Illuminate\Support\Facades\Auth::user()->bodegas == 'si' && \Illuminate\Support\Facades\Auth::user()->admin_bodegas == 'no'))
                        <div class="input-field">
                            {!! Form::select("tipo",[""=>"Seleccione el tipo de operación","Retiro"=>"Retiro","Consignación"=>"Consignación"],null,["id"=>"tipo","class"=>""]) !!}
                            {!! Form::label("tipo","Tipo de operación") !!}

                            <p class="cyan-text font-small hide" id="msj-consignacion"><strong>Nota: </strong> pasa una cantidad de dinero de la caja mayor al saldo de la cuenta bancaria seleccionada</p>
                            <p class="cyan-text font-small hide" id="msj-retiro"><strong>Nota: </strong> pasa una cantidad de dinero del saldo de la cuenta bancaria seleccionada a la caja maestra</p>
                        </div>
                    @endif

                    <div class="input-field margin-top-30">
                        {!! Form::select("cuenta_bancaria",[""=>"Seleccione una cuenta bancaria"]+\App\Models\CuentaBancaria::lista(),null,["id"=>"cuenta_bancaria","class"=>""]) !!}
                        {!! Form::label("cuenta_bancaria","Cuenta bancaria") !!}
                    </div>
                    <div class="input-field">
                        {!! Form::text("valor",null,["id"=>"valor","class"=>"num-entero"]) !!}
                        {!! Form::label("valor","Valor",["class"=>"active"]) !!}
                    </div>
                    <div class="input-field">
                        {!! Form::textarea("observacion",null,["id"=>"observacion","class"=>"materialize-textarea"]) !!}
                        {!! Form::label("observacion","Observación",["class"=>"active"]) !!}
                    </div>
                {!! Form::close() !!}
            @else
                <p>En este momento no existen cuentas bancarias relacionadas con su información.</p>
                <p>Click <a href="{{url('/cuenta-bancaria')}}">AQUÍ</a> para crear cuentas bancarias </p>
            @endif
        </div>
        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-operacion-caja">
                @if(count(\App\Models\CuentaBancaria::lista()))
                    <a href="#!" class="green-text btn-flat" onclick="guardarOperacionCajaMaestra()">Aceptar</a>
                @endif
                <a href="#!" class="modal-close cyan-text btn-flat" onclick="">Cerrar</a>
            </div>
        </div>
    </div>

    <div id="modal-cerrar-caja-master" class="modal modal-fixed-footer modal-small" >
        <div class="modal-content">
            <p class="titulo-modal">¿Está seguro de cerrar la caja maestra?</p>
            @if(\Illuminate\Support\Facades\Auth::user()->bodegas == 'si')

                <p>También se cerrarán todas las cajas del almacén, siempre y cuando no exista un usuario asignado a una de ellas.</p>
            @else
                <p>También se cerrarán todas las cajas abiertas, siempre y cuando no exista un usuario asignado a una de ellas.</p>
            @endif
        </div>
        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-cerrar-caja-master">
                <a href="#!" class="modal-close red-text btn-flat" onclick="cerrarCajaMaestra(false)">Aceptar</a>
                <a href="#!" class="modal-close cyan-text btn-flat">Cerrar</a>
            </div>
        </div>
    </div>

    <div id="modal-historial-operaciones-caja" class="modal modal-fixed-footer" style="min-width: 80%;" >
        <div class="modal-content">
            <p class="titulo-modal">Historial de operaciones de caja</p>
            <div id="contenedor-historial-operaciones-caja">
            </div>
        </div>
        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-historial-operaciones-caja">
                <a href="#!" class="modal-close cyan-text btn-flat">Cerrar</a>
            </div>
        </div>
    </div>
@endsection