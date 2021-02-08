<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    @if(!isset($filtro))
        <?php $filtro=""; ?>
    @endif
<div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
    <a class="btn-floating waves-effect waves-light blue-grey darken-2 agregar-elemento-tabla tooltipped" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="{{url('/productos-proveedor/create')}}"><i class="fa fa-plus"></i></a>
    <a style="margin-top: 50px;" class="btn-floating waves-effect waves-light blue-grey darken-2 agregar-elemento-tabla tooltipped" data-position="bottom" data-delay="50" data-tooltip="Importación de productos" href="{{url('/productos-proveedor/importacion')}}"><i class="fa fa-cloud"></i></a>

    <p class="titulo">Productos</p>
    <div class="input-field col m6 l4 right hide-on-small-only" style="margin-top: -70px;">
        <input type="text" name="busqueda" id="busqueda" placeholder="Buscar" value="{{$filtro}}" style="border: none !important;">
        <button class="btn blue-grey darken-2 waves-effect waves-light btn-buscar" style="float: right;margin-top: -55px;padding: 0px 5px !important;"><i class="fa fa-search icono-buscar" style=""></i><i class="fa fa-spin fa-spinner hide icono-load-buscar" style=""></i></button>
    </div>

    <div class="input-field col s12 hide-on-med-and-up" >
       <input type="text" name="busqueda2" id="busqueda2" placeholder="Buscar" value="{{$filtro}}">
        <button class="btn blue-grey darken-2 waves-effect waves-light btn-buscar" style="float: right;margin-top: -55px;padding: 0px 5px !important;"><i class="fa fa-search icono-buscar" style=""></i><i class="fa fa-spin fa-spinner hide icono-load-buscar" style=""></i></button>
    </div>
    @include("templates.mensajes",["id_contenedor"=>"productos"])
    <div id="contenedor-lista-productos" class="col s12 content-table-slide">
        @include('productos_proveedor.lista')
    </div>

</div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/productos/productosProveedorAction.js')}}"></script>
@stop