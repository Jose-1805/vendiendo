<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","Traslados","inicio"))
            <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="{{url('/traslado/create')}}"><i class="fa fa-plus"></i></a>
        @endif

        <p class="titulo">Traslados</p>
        <div id="contenedor-lista-facturas" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"traslados"])
            <div id="lista-traslados" class="col s12 content-table-slide">
                @include('traslados.lista')
            </div>
        </div>

    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/trasladoAction.js')}}"></script>
@stop
