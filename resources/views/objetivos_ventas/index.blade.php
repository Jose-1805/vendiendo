<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    @if(!isset($filtro))
        <?php $filtro=""; ?>
    @endif
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        @if(\Illuminate\Support\Facades\Auth::user()->perfil->nombre == "administrador" && \Illuminate\Support\Facades\Auth::user()->plan()->objetivos_ventas == "si")
            <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="#" id="btn-nuevo-objetivo-venta"><i class="fa fa-plus"></i></a>
        @endif
        <p class="titulo-seccion titulo">Objetivos de ventas</p>

        <div id="contenedor-lista-objetivos_ventas" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"objetivos-ventas"])
            <div id="lista-objetivos_ventas" class="col s12 content-table-slide">
                @include('objetivos_ventas.lista')
            </div>
        </div>

    </div>

    <div id="modal-accion-objetivo-venta" class="modal modal-fixed-footer modal-small" style="min-height: 60%;">
        <div class="modal-content">
            <p id="load-info-action-objetivo-venta" class="center-align">Cargando <i class="fa fa-spin fa-spinner"></i></p>
            <div id="contenido-accion-objetivo-venta">
            </div>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-accion-objetivo-venta">
                <a href="#!" class="btn-flat waves-effect waves-block" id="guardar-objetivo-venta">Guardar</a>
                <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cancelar</a>
            </div>
            <div class="progress hide" id="progres-accion-objetivo-venta">
                <div class="indeterminate"></div>
            </div>
        </div>
    </div>

    <div id="modal-eliminar-objetivo" class="modal modal-fixed-footer modal-small">
        <div class="modal-content">
            <p class="titulo-modal">Eliminar</p>
            @include('templates.mensajes',["id_contenedor"=>"eliminar-objetivo"])
            <p>¿Está seguro de eliminar el objetivo de venta?</p>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-accion-objetivo-venta">
                <a href="#!" class="btn-flat waves-effect waves-block red-text" onclick="eliminarObjetivo()">Aceptar</a>
                <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cancelar</a>
            </div>
            <div class="progress hide" id="progres-accion-objetivo-venta">
                <div class="indeterminate"></div>
            </div>
        </div>
    </div>
@endsection


@section('js')
    @parent
    <script src="{{asset('js/objetivosVentasAction.js')}}"></script>
@stop
