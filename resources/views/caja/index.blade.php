<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
@section('js')
    @parent
    <script src="{{asset('js/caja/cajaAction.js')}}"></script>
    <script>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","caja","configuracion"))
            setPermisoEditarCaja(true);
        @endif
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","caja","configuracion"))
            setPermisoEliminarCaja(true);
        @endif
    </script>
@stop

@section('contenido')
    <?php
        $top = 50;
    ?>
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","caja","configuracion"))
            <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped modal-trigger agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="#modal-crear-caja"><i class="fa fa-plus"></i></a>
        @endif
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Asignar","caja","configuracion")
        && (
                (\App\Models\Caja::cajasPermitidas()->where('estado', 'abierta')->first() && \Illuminate\Support\Facades\Auth::user()->bodegas == 'no')
                ||(\App\Models\Caja::cajasPermitidasAlmacen()->where('estado', 'abierta')->first())
            )
        )
            <a style="margin-top: {{$top}}px;" class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Asignar cajas" href="{{url("caja/asignar")}}"><i class="fa fa-space-shuttle"></i></a>
            <?php
                $top += 50;
            ?>
        @endif
        @if (\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Iniciar mayor","caja","configuracion"))
            <a style="margin-top: {{$top}}px;" class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Caja maestra" href="{{url("caja/maestra")}}"><i class="fa fa-calculator"></i></a>
        @endif
        <p class="titulo">Cajas</p>
        <div id="contenedor-lista-productos" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"cajas"])
            <div id="lista-categorias" class="content-table-slide col s12">
                @include('caja.lista')
            </div>
        </div>
    </div>

    <div id="modal-crear-caja" class="modal modal-fixed-footer modal-small" style="min-height: 70%;">
        <div class="modal-content">
            <p class="titulo-modal">Crear caja</p>
            @include("templates.mensajes",["id_contenedor"=>"crear-caja"])
            @include('caja.form')
        </div>

        <div class="modal-footer">
            <div class="col s12">
                <a href="#!" class="btn-flat waves-effect waves-block" onclick="guardar();">Guardar</a>
                <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cancelar</a>
            </div>
        </div>
    </div>

    <div id="modal-editar-caja" class="modal modal-fixed-footer modal-small" style="min-height: 70%;">
        <div class="modal-content">
            <p class="titulo-modal">Editar caja</p>
            @include("templates.mensajes",["id_contenedor"=>"editar-caja"])
            <div id="contenido-editar-caja"></div>
        </div>

        <div class="modal-footer">
            <div class="col s12">
                <a href="#!" class="btn-flat waves-effect waves-block" onclick="editar();">Guardar</a>
                <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cancelar</a>
            </div>
        </div>
    </div>

    <div id="modal-historial" class="modal modal-fixed-footer" style="min-height: 70%;min-width: 80%;">
        <div class="modal-content">
            <p class="titulo-modal">Historial de asignaciones</p>
            <div id="contenedor-historial"></div>
        </div>

        <div class="modal-footer">
            <div class="col s12">
                <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cerrar</a>
            </div>
        </div>
    </div>

    <div id="modal-historial-estados" class="modal modal-fixed-footer" style="min-height: 70%;min-width: 80%;">
        <div class="modal-content">
            <p class="titulo-modal">Historial de estados</p>

            <div id="contenedor-historial-estados"></div>
        </div>

        <div class="modal-footer">
            <div class="col s12">
                <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cerrar</a>
            </div>
        </div>
    </div>

    <div id="modal-transacciones" class="modal modal-fixed-footer" style="min-height: 70%;min-width: 80%;">
        <div class="modal-content">
            <p class="titulo-modal">Transacciones en caja</p>
            <div id="contenedor-transacciones"></div>
        </div>

        <div class="modal-footer">
            <div class="col s12">
                <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cerrar</a>
            </div>
        </div>
    </div>
@endsection

