<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="{{url('/anuncio/create')}}"><i class="fa fa-plus"></i></a>
        <p class="titulo">Anuncios</p>
        <div id="contenedor-lista-anuncios" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"anuncios"])
            <div id="lista-anuncios" class="content-table-slide col s12">
                @include('anuncios.lista')
            </div>
        </div>

    </div>
@endsection


@section('js')
    @parent
    <script src="{{asset('js/anuncioAction.js')}}"></script>
    <script>
        $(function () {
            cargarTablaAnuncios();
        })
    </script>
@stop