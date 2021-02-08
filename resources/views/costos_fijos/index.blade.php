<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
<?php

    $data_meses = ["01"=>"Enero","02"=>"Febrero","03"=>"Marzo","04"=>"Abril","05"=>"Mayo","06"=>"Junio"
    ,"07"=>"Julio","08"=>"Agosto","09"=>"Septiembre","10"=>"Octubre","11"=>"Noviembre","12"=>"Diciembre"];
    $data_anios = [];

    for ($i = date("Y")-8; $i <= date("Y");$i++){
        $data_anios[$i] = $i;
    }
?>

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","costos fijos","inicio"))
            <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped modal-trigger agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="#modal-crear-costo-fijo"><i class="fa fa-plus"></i></a>
        @endif
        <p class="titulo">Costos Fijos - {{$data_meses[$mes]}} de {{$anio}}</p>
            <div class="col s12">
                <div class="input-field col s12 m3 offset-m6 l2 offset-l8 ">
                    {!! Form::select("anio",$data_anios,$anio,["id"=>"anio"]) !!}
                    {!! Form::label("anio","Año") !!}
                </div>

                <div class="input-field col s12 m3 l2">
                    {!! Form::select("mes",$data_meses,$mes,["id"=>"mes"]) !!}
                    {!! Form::label("mes","Mes") !!}
                </div>
            </div>
        <div id="contenedor-lista-costos-fijos" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"costos-fijos"])
            <div id="lista-costos-fijos" class="col s12 content-table-slide">
                @include('costos_fijos.lista')
            </div>
        </div>

    </div>
@endsection

@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","costos fijos","inicio"))
    <div id="modal-crear-costo-fijo" class="modal modal-fixed-footer modal-small" style="max-height: 55% !important;">
        <div class="modal-content">
            <p class="titulo-modal">Crear</p>
            @include("templates.mensajes",["id_contenedor"=>"modal-crear-costos-fijos"])
            <div class="row" id="contenido-crear-costo-fijo">
                @include('costos_fijos.form')
            </div>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-crear-costo-fijo">
                <a href="#!" class="cyan-text btn-flat" onclick="crear();">Aceptar</a>
                <a href="#!" class="red-text modal-close btn-flat">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-crear-costo-fijo">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>
@endif

@section('js')
    @parent
    <script src="{{asset('js/costosFijosAction.js')}}"></script>
@stop