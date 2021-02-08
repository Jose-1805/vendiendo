<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
<?php
    $fecha_inicio = date("Y-m-d",strtotime("-1month",strtotime(date("Y-m-d"))));
    $fecha_fin= date("Y-m-d");
?>
@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        <p class="titulo">Reporte cierre de caja</p>

            <div class="col s12 right-align" style="margin-top: -60px;"><i class="fa fa-file-excel-o fa-2x margin-left-20 cyan-text tooltipped waves-effect waves-light" data-position="bottom" data-delay="50" data-tooltip="Reporte en Excel" style="cursor: pointer;" onclick="reporteExcel()"></i></div>
            <div class="col s12">
                {!! Form::open(["id"=>"form-filtros-cierre-caja"]) !!}
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
            @include("templates.mensajes",["id_contenedor"=>"cierre-caja"])
            <div class="progress hide" id="progress-cierre-caja"><div class="indeterminate"></div></div>
            <div class="divider col s12 margin-top-40"></div>
            <div class="col s12 content-table-slide" id="contenedor-cierre-caja">
                <table id="tabla_cierre_caja" class="centered highlight table-rotate">
                    <thead>
                    <th>FECHA</th>
                    <th>SALDO EN EFECTIVO</th>
                    <th></th>
                    <th>VENTAS</th>
                    <th>Ventas en efectivo</th>
                    <th>Descuento en ventas</th>
                    <th>Ventas pagos por otros medios</th>
                    <th>Ventas a credito</th>
                    <th>Puntos</th>
                    <th>TOTAL EN VENTAS</th>
                    <th></th>
                    <th>COMPRAS</th>
                    <th>Compras en efectivo</th>
                    <th>Compras a crédito</th>
                    <th>TOTAL COMPRAS</th>
                    <th>Devolución de compras(devolución de efectivo)</th>
                    <th></th>
                    <th>ABONOS</th>
                    <th>Abonos de clientes</th>
                    <th>Abonos a proveedores</th>
                    <th>TOTAL ABONOS</th>
                    <th></th>
                    <th>COSTOS</th>
                    <th>Gastos diarios</th>
                    <th>Costos fijos</th>
                    <th>TOTAL COSTOS</th>
                    <th></th>
                    <th>CONSIGNACIONES</th>
                    <th>Consignaciones al banco</th>
                    <th>Consignaciones a caja</th>
                    <th></th>
                    <th>CIERRE TOTAL DE CAJA GENERAL</th>
                    <th>Efectivo</th>
                    <th>Otros medios de pago</th>
                    <th>TOTALES</th>
                    </thead>
                </table>
            </div>
    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/reportes/cierre_caja.js')}}"></script>
@endsection