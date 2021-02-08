<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","pqrs","inicio"))
            <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="{{url("/pqrs/create")}}"><i class="fa fa-plus"></i></a>
        @endif
        <p class="titulo">Pqrs</p>

        <div id="contenedor-lista-pqrs" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"lista-pqrs"])
            <div id="lista-pqrs" class="col s12 content-table-slide">
                @include('pqrs.lista')
            </div>
        </div>

    </div>

    <div id="modal-detalle-pqrs" class="modal modal-fixed-footer">
        <div class="modal-content">
            <p class="titulo-modal">Detalle de pqrs</p>
            @include("templates.mensajes",["id_contenedor"=>"detalle-pqrs"])
            <div id="load-info-pqrs"><p class="center-align">Cargando información <i class="fa fa-spin fa-spinner"></i></p></div>
            <div id="info-pqrs"></div>
        </div>

        <div class="modal-footer">
                <a href="#!" class="modal-close cyan-text btn-flat">Cerrar</a>
        </div>
    </div>

@endsection

@section('js')
    @parent
    <script src="{{asset('js/pqrs.js')}}"></script>
@stop