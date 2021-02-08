@if(!isset($filtro))
    <?php $filtro = ""; ?>
@endif
<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-40" style="margin-top: 85px">
        <p class="titulo">Resoluciones</p>

        <div class="input-field col m6 l4 right hide-on-small-only" style="margin-top: -70px;">
            <i class="fa fa-search prefix" style="font-size: 20px;line-height: 45px !important;margin-left: 10px;"></i>
            <input type="text" name="busqueda" id="busqueda" placeholder="Buscar" value="{{$filtro}}" style="border: none !important;">
        </div>

        <div class="input-field col s12 hide-on-med-and-up" >
            <i class="fa fa-search prefix" style="font-size: 20px;line-height: 55px !important;margin-left: 10px;"></i>
            <input type="text" name="busqueda2" id="busqueda2" placeholder="Buscar" value="{{$filtro}}">
        </div>

        <div class="col s12 content-table-slide" id="contenedor-lista-resoluciones">
            @include('facturacion.lista_resoluciones')
        </div>
    </div>
@endsection


@section('js')
    @parent
    <script src="{{asset('js/facturacionAction.js')}}"></script>
@stop