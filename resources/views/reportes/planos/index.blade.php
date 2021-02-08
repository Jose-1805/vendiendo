<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
<?php
$fecha_inicio = date("Y-m-d",strtotime("-1month",strtotime(date("Y-m-d"))));
$fecha_fin= date("Y-m-d");
?>
@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        <p class="titulo">Reportes planos</p>
        <a class="btn-floating waves-effect waves-light blue-grey darken-2 agregar-elemento-tabla tooltipped" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="{{url('/reporte-plano/create')}}"><i class="fa fa-plus"></i></a>

        <!--<div class="col s12 right-align" style="margin-top: -60px;"><i class="fa fa-file-excel-o fa-2x margin-left-20 cyan-text tooltipped waves-effect waves-light" data-position="bottom" data-delay="50" data-tooltip="Reporte en Excel" style="cursor: pointer;" onclick="reporteExcel()"></i></div>-->

        @include("templates.mensajes",["id_contenedor"=>"reportes-planos"])
        <div class="progress hide" id="progress-reportes-planos"><div class="indeterminate"></div></div>
        <div class="divider col s12 margin-top-40"></div>
        <div class="col s12 content-table-slide" id="contenedor-reportes-planos">
            @include('reportes.planos.lista')
        </div>
    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/reportes/reportes_planos.js')}}"></script>

@endsection