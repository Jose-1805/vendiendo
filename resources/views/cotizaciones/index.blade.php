<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","cotizaciones","inicio"))
            <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="{{url('/cotizacion/create')}}"><i class="fa fa-plus"></i></a>
        @endif

        <p class="titulo">Cotizaciones</p>
        <div id="contenedor-lista-facturas" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"facturas"])
            <div id="lista-facturas" class="col s12 content-table-slide">
                @include('cotizaciones.lista')
            </div>
        </div>

    </div>

    <div id="modal-historial" class="modal modal-fixed-footer">
        <div class="modal-content">
            <p class="titulo-modal">Historial</p>
            <div id="contenedor-historiales">

            </div>
        </div>

        <div class="modal-footer">
            <a href="#!" class="modal-close btn-flat">Cerrar</a>
        </div>
    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/cotizacionAction.js')}}"></script>

    @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","cotizaciones","inicio"))
        <script>
            setPermisoEditar(true);
        </script>
    @endif
@stop
