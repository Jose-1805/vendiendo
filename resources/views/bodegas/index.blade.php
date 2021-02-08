<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","bodegas","inicio") && count(\App\Models\Bodega::permitidos()->get()) == 0 && (Auth::user()->plan()->n_bodegas == 0 || Auth::user()->plan()->n_bodegas > Auth::user()->countBodegasAdministrador()))
            <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped modal-trigger agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="#modal-bodegas"><i class="fa fa-plus"></i></a>
        @endif

        <p class="titulo">Bodegas</p>
        @if(!(Auth::user()->plan()->n_bodegas == 0 || Auth::user()->plan()->n_bodegas > Auth::user()->countBodegasAdministrador()))
                <div class="col s12 contenedor-confirmacion blue lighten-5 blue-text" id="contenedor-confirmacion-n_usuarios">
                    <i class='fa fa-close btn-cerrar-confirmacion'></i>
                    <ul>
                        <li>No es posible crear más bodegas, su plan alcanzó el tope máximo de compras permitidas.</li>
                    </ul>
                </div>
        @endif
        <div id="contenedor-lista-bodegas" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"bodegas"])
            <div id="lista-bodegas" class="col s12 content-table-slide">
                @include('bodegas.lista')
            </div>
        </div>
    </div>
@endsection

@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","bodegas","inicio") && count(\App\Models\Bodega::permitidos()->get()) == 0)
    <div id="modal-bodegas" class="modal modal-fixed-footer modal-small" style="max-height: 55% !important;">
        <div class="modal-content">
            <p class="titulo-modal">Crear</p>
            @include("templates.mensajes",["id_contenedor"=>"modal-crear-bodegas"])
            <div class="row" id="contenido-crear-bodegas">
                @include('bodegas.form')
            </div>
        </div>

        <div class="modal-footer">
            <a href="#!" class="cyan-text btn-flat" onclick="crear();">Aceptar</a>
            <a href="#!" class="red-text modal-close btn-flat">Cancelar</a>
        </div>
    </div>
@endif

@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","bodegas","inicio"))
    <div id="modal-editar-bodegas" class="modal modal-fixed-footer modal-small" style="max-height: 55% !important;">
        <div class="modal-content">
            <p class="titulo-modal">Editar</p>
            @include("templates.mensajes",["id_contenedor"=>"modal-editar-bodegas"])
            <div class="row" id="contenido-editar-bodegas">
            </div>
        </div>

        <div class="modal-footer">
            <a href="#!" class="cyan-text btn-flat" onclick="editar();">Aceptar</a>
            <a href="#!" class="red-text modal-close btn-flat">Cancelar</a>
        </div>
    </div>
@endif



@section('js')
    @parent
    <script src="{{asset('js/bodegasAction.js')}}"></script>
    <script>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","bodegas","inicio"))
            setPermisoEditar(true);
        @endif
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","bodegas","inicio"))
            setPermisoEliminar(true);
        @endif
    </script>
@stop