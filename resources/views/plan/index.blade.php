<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","planes","configuracion"))
            <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="{{url('plan/create')}}"><i class="fa fa-plus"></i></a>
        @endif
        <p class="titulo">Planes</p>

        <div id="contenedor-lista-planes" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"planes"])
            <div id="lista-planes" class="content-table-slide col s12">
                @include('plan.lista')
            </div>
        </div>

    </div>

<div id="modal-accion-categoria" class="modal modal-fixed-footer ">
    <div class="modal-content">
        <div id="contenido-accion-categoria">
        @include('categoria.form')
        </div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-accion-categoria">
            <a href="#!" class="btn-flat waves-effect waves-block" id="btn-accion-categoria">Guardar</a>
            <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cancelar</a>
        </div>
        <div class="progress hide" id="progres-accion-categoria">
            <div class="indeterminate"></div>
        </div>
    </div>
</div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/categoriaAction.js')}}"></script>
@stop
