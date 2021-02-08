<?php
    $caja = \App\Models\Caja::cajasPermitidas()->where('fecha',date("Y-m-d"))->first();
?>
<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","gastos diarios","inicio") && \App\Models\Caja::abierta())
            <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped modal-trigger agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="#modal-gastos-diario"><i class="fa fa-plus"></i></a>
        @endif

        @if(\Illuminate\Support\Facades\Auth::user()->perfil->nombre == "administrador" && $caja)
            <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla" style="margin-top: 50px;" data-position="bottom" data-delay="50" data-tooltip="Agregar a caja" href="#" id="btn-agregar-a-caja"><i class="fa fa-usd"></i></a>
        @endif
        <p class="titulo">Gastos</p>

        <div id="contenedor-lista-gastos-diarios" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"gastos-diarios"])
            <div id="lista-gastos-diarios" class="col s12 content-table-slide">
                @include('gastos.lista')
            </div>
        </div>
    </div>
@endsection

@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","gastos diarios","inicio"))
    <div id="modal-gastos-diario" class="modal modal-fixed-footer modal-small" style="max-height: 55% !important;">
        <div class="modal-content">
            <p class="titulo-modal">Crear</p>
            @include("templates.mensajes",["id_contenedor"=>"modal-crear-gastos-diarios"])
            <div class="row" id="contenido-crear-gastos-diarios">
                @include('gastos.form.form')
            </div>
        </div>

        <div class="modal-footer">
            <a href="#!" class="cyan-text btn-flat" onclick="crear();">Aceptar</a>
            <a href="#!" class="red-text modal-close btn-flat">Cancelar</a>
        </div>
    </div>
@endif

@if(\Illuminate\Support\Facades\Auth::user()->perfil->nombre == "administrador" && $caja)
    <div id="modal-agregar-a-caja" class="modal modal-fixed-footer modal-small" style="max-height: 55% !important;">
        <div class="modal-content">
            <p class="titulo-modal">Agregar efectivo a caja</p>
            @include("templates.mensajes",["id_contenedor"=>"modal-agregar-a-caja"])
            <div id="agregar_efectivo_caja"></div>
        </div>

        <div class="modal-footer">
            <a href="#!" class="cyan-text btn-flat" onclick="agregarEfectivoCaja();">Agregar</a>
            <a href="#!" class="red-text modal-close btn-flat">Cancelar</a>
        </div>
    </div>
@endif
@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","gastos diarios","inicio"))
    <div id="modal-editar-gastos-diarios" class="modal modal-fixed-footer modal-small" style="max-height: 55% !important;">
        <div class="modal-content">
            <p class="titulo-modal">Editar</p>
            @include("templates.mensajes",["id_contenedor"=>"modal-editar-gastos-diarios"])
            <div class="row" id="contenido-editar-gastos-diarios">
            </div>
        </div>

        <div class="modal-footer">
            <a href="#!" class="cyan-text btn-flat" onclick="editar();">Aceptar</a>
            <a href="#!" class="red-text modal-close btn-flat">Cancelar</a>
        </div>
    </div>
@endif


@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","gastos diarios","inicio"))
    <div id="modal-eliminar-gasto" class="modal modal-fixed-footer modal-small">
        <div class="modal-content">
            <p class="titulo-modal">Eliminar</p>
            <p>¿Está seguro de eliminar este gasto?</p>
        </div>

        <div class="modal-footer">
                <a href="#!" class="red-text btn-flat" onclick="javascript: eliminar()">Aceptar</a>
                <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
        </div>
    </div>
@endif

@section('js')
    @parent
    <script src="{{asset('js/GastosAction.js')}}"></script>
    <script>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","gastos diarios","inicio"))
            setPermisoEditar(true);
        @endif
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","gastos diarios","inicio"))
            setPermisoEliminar(true);
        @endif
    </script>
@stop