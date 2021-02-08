<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    @if(!isset($filtro))
        <?php $filtro=""; ?>
    @endif
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","Remision salida","inicio"))
            <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="{{url('/remision-salida/create')}}"><i class="fa fa-plus"></i></a>
        @endif

        <p class="titulo">Remisiones de salida</p>

        <div id="contenedor-lista-remisiones-salida" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"remisiones-salida"])
            <div id="lista-remisiones-salida">
                @include('remisiones_salida.lista')
            </div>
        </div>
    </div>
@endsection


@section('js')
    @parent
    <script src="{{asset('js/remisiones_salida/index.js')}}"></script>
@stop