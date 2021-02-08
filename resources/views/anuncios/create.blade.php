<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white margin-bottom-50" style="margin-top: 85px">
        <p class="titulo">Crear anuncio</p>
        <div id="contenedor-lista-anuncios" class="col s12">
            @include('anuncios.form')
            <div class="col s12 center-align margin-bottom-40 margin-top-30">
                <a class="btn blue-grey darken-2" onclick="agregar()">Guardar</a>
            </div>
        </div>

    </div>
@endsection


@section('js')
    @parent
    <script src="{{asset('js/anuncioAction.js')}}"></script>
    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
@stop