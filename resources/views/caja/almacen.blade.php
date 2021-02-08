<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('js')
    @parent
    <script src="{{asset('js/caja/cajaAlmacen.js')}}"></script>
    <script>
        $(function(){
            cargarTablaAlmacen();
        })
    </script>
@stop

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        <p class="titulo">Caja almacén</p>
        <p class="font-large"><strong>Saldo actual: </strong><span class="cyan-text">$ {{number_format($caja->efectivo_final,2,',','.')}}</span></p>
        @include("templates.mensajes",["id_contenedor"=>"lista-almacenes"])

        <table class="bordered highlight centered" id="tabla_almacen" style="width: 100%;">
            <thead>
            <tr>
                <th>Almacén</th>
                <th>Fecha asignación</th>
                <th>Efectivo inicial</th>
                <th>Efectivo final</th>
                <th>Opciones</th>
            </tr>
            </thead>
        </table>
    </div>

    <div id="modal-iniciar-caja-almacen" class="modal modal-fixed-footer modal-small" style="min-height: 40%;" >
        <div class="modal-content">
            <p class="titulo-modal">Inicializar caja de almacén</p>
            @include('templates.mensajes',["id_contenedor"=>"iniciar-caja-almacen"])
            {!! Form::open(["id"=>"form-iniciar-caja-almacen","class"=>"col s12"]) !!}
                <div class="input-field">
                    {!! Form::text("valor",null,["id"=>"valor","class"=>"num-entero"]) !!}
                    {!! Form::label("valor","Valor",["class"=>"active"]) !!}
                </div>
            {!! Form::close() !!}
        </div>
        <div class="modal-footer">
            <div class="col s12">
                <a href="#!" class="green-text btn-flat" onclick="iniciarCajaAlmacen()">Aceptar</a>
                <a href="#!" class="modal-close cyan-text btn-flat" onclick="">Cerrar</a>
            </div>
        </div>
    </div>

    <div id="modal-movimiento-caja-maestra" class="modal modal-fixed-footer modal-small" style="min-height: 40%;" >
        <div class="modal-content">
            <p class="titulo-modal">Enviar efectivo</p>
            @include('templates.mensajes',["id_contenedor"=>"movimiento-caja-maestra"])
            {!! Form::open(["id"=>"form-modal-movimiento-caja-maestra","class"=>"col s12"]) !!}
                <div class="input-field">
                    {!! Form::text("valor",null,["id"=>"valor","class"=>"num-entero"]) !!}
                    {!! Form::label("valor","Valor",["class"=>"active"]) !!}
                </div>
                <div class="input-field">
                    {!! Form::textarea("observacion",null,["id"=>"observacion","class"=>"materialize-textarea","maxLength"=>255]) !!}
                    {!! Form::label("observacion","Observación",["class"=>"active"]) !!}
                </div>
            {!! Form::close() !!}
        </div>
        <div class="modal-footer">
            <div class="col s12">
                <a href="#!" class="green-text btn-flat" onclick="realizarMovimientoCajaMaestra()">Aceptar</a>
                <a href="#!" class="modal-close cyan-text btn-flat" onclick="">Cerrar</a>
            </div>
        </div>
    </div>

    <div id="modal-lista-movimientos-caja-maestra" class="modal modal-fixed-footer" style="min-width: 80%;" >
        <div class="modal-content">
            <p class="titulo-modal">Historial de movimientos de caja maestra</p>
            <div id="contenedor-lista-movimientos-caja-maestra">
            </div>
        </div>
        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-lista-movimientos-caja-maestra">
                <a href="#!" class="modal-close cyan-text btn-flat">Cerrar</a>
            </div>
        </div>
    </div>

    <div id="modal-cerrar-caja-master" class="modal modal-fixed-footer modal-small" >
        <div class="modal-content">
            <p class="titulo-modal">¿Está seguro de cerrar la caja maestra?</p>
            <p>También se cerrarán todas las cajas abiertas, siempre y cuando no exista un usuario asignado a una de ellas.</p>
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