<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    @if(!isset($filtro))
        <?php $filtro=""; ?>
    @endif
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","Web Cam","inicio"))
            <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped modal-trigger agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="#modal-accion-webCam"><i class="fa fa-plus"></i></a>
        @endif
        <p class="titulo">Web Cam</p>

        <div id="contenedor-lista-webCams" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"webCams"])
            <div id="lista-webCams" class="content-table-slide col s12">
                @include('webcam.lista')
            </div>
        </div>

    </div>

<div id="modal-accion-webCam" class="modal modal-fixed-footer ">
    <div class="modal-content">
        <div id="contenido-accion-webCam">
         @include('webcam.form')
        </div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-accion-webCam">
            <a href="#!" class="btn-flat waves-effect waves-block" id="btn-accion-webCam">Guardar</a>
            <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cancelar</a>
        </div>
        <div class="progress hide" id="progres-accion-webCam">
            <div class="indeterminate"></div>
        </div>
    </div>
</div>

<div id="modal-ver-webCam" class="modal modal-fixed-footer " style="width: 95%;min-height: 80% !important;">
    <div class="modal-content">
        <div id="contenido-ver-webCam">
        </div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-ver-webCam">
            <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cerrar</a>
        </div>
        <div class="progress hide" id="progres-ver-webCam">
            <div class="indeterminate"></div>
        </div>
    </div>
</div>

@endsection

@section('js')
    @parent
    <script src="{{asset('js/webCamAction.js')}}"></script>
@stop
