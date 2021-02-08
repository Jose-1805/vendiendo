<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')

    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        <a class="btn-floating waves-effect waves-light blue-grey darken-2 agregar-elemento-tabla tooltipped" data-position="bottom" data-delay="50" data-tooltip="Importar" href="#" onclick="openImportar()"><i class="fa fa-cloud-upload"></i></a>
        <a style="margin-top: 50px;" class="btn-floating waves-effect waves-light blue-grey darken-2 agregar-elemento-tabla tooltipped" data-position="bottom" data-delay="50" data-tooltip="Formato de importación" href="{{url('/productos-proveedor/formato-importacion')}}"><i class="fa fa-file-excel-o"></i></a>
        <p class="titulo">Importación de productos</p>

        @include("templates.mensajes",["id_contenedor"=>"importacion-productos"])
        <div id="contenedor-lista-importaciones" class="col s12 content-table-slide">
            @include('productos_proveedor.lista_importacion')
        </div>

    </div>

    <div id="modal-importar-productos" class="modal modal-fixed-footer modal-small" style="min-height: 55%;">
        <div class="modal-content">
            <p class="titulo-modal">Importar productos</p>
            @include('templates.mensajes',["id_contenedor"=>"modal-importacion"])
            <div class="file-field input-field">
                <div class="btn cyan waves-effect waves-light">
                    <i class="fa fa-folder-open"></i>
                    {!! Form::open(["id"=>"form-importar","enctype"=>"multipart/form-data"]) !!}
                        <input type="file" name="archivo" id="archivo">
                    {!! Form::close() !!}
                </div>
                <div class="file-path-wrapper">
                    <input class="file-path" type="text" placeholder="Seleccione un archivo" disabled>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-importar-productos">
                <a href="#!" class="cyan-text btn-flat" onclick="importar()">Importar</a>
                <a href="#!" class="modal-close btn-flat">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-importar-productos">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/importacionProductosProveedor.js')}}"></script>
@stop