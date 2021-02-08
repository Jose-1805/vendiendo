<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla modal-trigger" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="#modal-store-promocion"><i class="fa fa-plus"></i></a>
        <p class="titulo">Promociones</p>
        @include("templates.mensajes",["id_contenedor"=>"promociones"])
        <div id="contenedor-lista-promociones" class="col s12">

            <div id="lista-promociones" class="content-table-slide col s12">
                @include('promociones_proveedores.lista')
            </div>
        </div>

    </div>

    <div id="modal-store-promocion" class="modal modal-fixed-footer ">
        <div class="modal-content scroll-style">
            <p class="titulo">Crear promoción</p>
            @include("templates.mensajes",["id_contenedor"=>"store-promocion"])
            <div id="contenido-store-promocion">
                @include('promociones_proveedores.form',["promocion"=>new \App\Models\PromocionProveedor()])
            </div>
        </div>

        <div class="modal-footer">
            <a href="#!" class="btn-flat waves-effect waves-block" id="btn-action-promocion" onclick="guardarPromocion()">Guardar</a>
            <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cancelar</a>
        </div>
    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/promocionesProveedoresAction.js')}}"></script>
@stop
