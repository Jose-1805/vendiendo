<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
<?php
$fecha_inicio = date("Y-m-d",strtotime("-1month",strtotime(date("Y-m-d"))));
$fecha_fin= date("Y-m-d");
?>
@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        <p class="titulo">Reporte Compras</p>

        <div class="col s12 right-align" style="margin-top: -60px;"><i class="fa fa-file-excel-o fa-2x margin-left-20 cyan-text tooltipped waves-effect waves-light" data-position="bottom" data-delay="50" data-tooltip="Reporte en Excel" style="cursor: pointer;" onclick="reporteExcel()"></i></div>
        <div class="col s12">
            {!! Form::open(["id"=>"form-filtros-reporte-compras"]) !!}
            <div class="col s12 m6 l2 offset-l3 input-field">
                {!! Form::date("fecha_inicio",$fecha_inicio,["id"=>"fecha_inicio","class"=>"datepicker"]) !!}
                {!! Form::label("fecha_inicio","Fecha inicio",["class"=>"active"]) !!}
            </div>
            <div class="col s12 m6 l2 input-field">
                {!! Form::date("fecha_fin",$fecha_fin,["id"=>"fecha_fin","class"=>"datepicker"]) !!}
                {!! Form::label("fecha_fin","Fecha fin",["class"=>"active"]) !!}
            </div>
            {!! Form::close() !!}
            <div class="col s12 l2 center-align">
                <a class="btn blue-grey darken-2 waves-effect waves-light margin-top-20" id="btn-ver">Ver</a>
            </div>
        </div>

        @include("templates.mensajes",["id_contenedor"=>"reporte-compras"])
        <div class="progress hide" id="progress-reporte-compras"><div class="indeterminate"></div></div>
        <div class="divider col s12 margin-top-40"></div>
        <div class="col s12 content-table-slide" id="contenedor-reporte-compras">
            <table id="tabla_reporte_compras" class="centered highlight">
                <thead>
                    <th>Número</th>
                    <th>Valor</th>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th>Usuario</th>
                    <th>Estado compra</th>
                    <th>Estado pago</th>
                    <th>Devoluciones</th>
                    <th>Detalle</th>
                </thead>
            </table>
        </div>
    </div>

    <div id="modal-detalle-compra" class="modal modal-fixed-footer" style="min-height: 70%;min-width: 80%;">
        <div class="modal-content">
            <p class="titulo-modal">Detalle de la compra</p>
            <div class="col s12 right-align" style="margin-top: -50px;"><i class="fa fa-file-excel-o fa-2x margin-left-20 cyan-text tooltipped waves-effect waves-light" data-position="bottom" data-delay="50" data-tooltip="Reporte en Excel" style="cursor: pointer;" onclick="reporteDetalleExcel()"></i></div>
            <div id="contenedor-detalle-compra"></div>
        </div>

        <div class="modal-footer">
            <div class="col s12">
                <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cerrar</a>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/reportes/compras.js')}}"></script>
@endsection