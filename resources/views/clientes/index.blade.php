<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
@section('js')
    @parent
    <script src="{{asset('js/clientesAction.js')}}"></script>
    <script>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","clientes","inicio"))
            setPermisoEditar(true);
        @endif
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","clientes","inicio"))
            setPermisoEliminar(true);
        @endif
    </script>
@stop

@section('contenido')
<div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
            <p class="titulo">Clientes</p>

            @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","clientes","inicio") && (Auth::user()->plan()->n_clientes == 0 || Auth::user()->plan()->n_clientes > Auth::user()->countClientesAdministrador()))
                <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla modal-trigger" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="#modal-crear-cliente"><i class="fa fa-plus"></i></a>
            @endif

            @if(!(Auth::user()->plan()->n_clientes == 0 || Auth::user()->plan()->n_clientes > Auth::user()->countClientesAdministrador()))
                <div class="col s12 contenedor-confirmacion blue lighten-5 blue-text" id="contenedor-confirmacion-n_usuarios">
                    <i class='fa fa-close btn-cerrar-confirmacion'></i>
                    <ul>
                        <li>No es posible crear más clientes, su plan alcanzó el tope máximo de clientes permitidos.</li>
                    </ul>
                </div>
            @endif
            <div id="contenedor-lista-clientes" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"clientes"])
            <div id="lista-clientes" class="content-table-slide col s12">
                @include('clientes.lista')
            </div>
        </div>

    </div>

<div id="modal-accion-cliente" class="modal modal-fixed-footer ">
    <div class="modal-content">
        <p class="titulo-modal">Edición cliente</p>
        @include("templates.mensajes",["id_contenedor"=>"modal-accion-cliente"])
        <div id="contenido-accion-cliente">
        </div>
    </div>

    <div class="modal-footer">
            <a href="#!" class="btn-flat waves-effect waves-block" onclick="editar()">Guardar</a>
            <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cancelar</a>
    </div>
</div>


@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","clientes","inicio"))
    <div id="modal-crear-cliente" class="modal modal-fixed-footer ">
        <div class="modal-content">
            <p class="titulo-modal">Crear cliente</p>
            @include("templates.mensajes",["id_contenedor"=>"modal-crear-cliente"])
            @include('clientes.form')
        </div>

        <div class="modal-footer">
            <a href="#!" class="btn-flat waves-effect waves-block" onclick="guardar()">Guardar</a>
            <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cancelar</a>
        </div>
    </div>
@endif
@endsection

