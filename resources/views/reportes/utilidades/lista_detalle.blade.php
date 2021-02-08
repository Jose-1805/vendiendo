<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        <p class="titulo">Estado de perdidas y ganancias</p>
        @if(!isset($reporte))
        {!! Form::hidden("fecha_inicio",$fecha_inicio,["id"=>"fecha_inicio"]) !!}
        {!! Form::hidden("fecha_fin",$fecha_fin,["id"=>"fecha_fin"]) !!}
        {!! Form::hidden("almacen",$almacen,["id"=>"almacen"]) !!}
        @endif
        <div class="col s12 right-align" style="margin-top: -60px;"><i class="fa fa-file-excel-o fa-2x margin-left-20 cyan-text tooltipped waves-effect waves-light" data-position="bottom" data-delay="50" data-tooltip="Reporte en Excel" style="cursor: pointer;" onclick="reporteExcel()"></i></div>
        <div class="col s12 content-table-slide" id="contenedor-utilidades">
   
       <table class="table centered highlight" id="tabla_reporte_detalle_utilidad" width="100%">
            <thead>
                <tr>
                    <th colspan="6" style="border-bottom:1px solid rgba(0,0,0,.5);">VENTAS</th>
                </tr>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Ventas</th>
                    <th>Costos compra</th>
                    <th>Utilidad</th>
                    <th>Utilidad [%]</th>
                </tr>
            </thead>
            <tbody>                
            </tbody>
            <tfoot>
                    <tr>
                        <th class="center-align">TOTAL DESCUENTOS</th><td></td>
                        <td class="f_total_descuentos"></td>
                        <td ></td>
                        <td class="f_total_descuentos"></td>
                        <td ></td>
                    </tr>
                    <tr>
                        <th class="center-align">TOTAL VALOR PUNTOS</th><td></td>
                        <td class="f_total_valor_puntos"></td>
                        <td ></td>
                        <td class="f_total_valor_puntos"></td>
                        <td ></td>
                    </tr>
                    <tr>
                        <th class="center-align">TOTAL</th><td></td>
                        <td id="f_total_facturas"></td>
                        <td id="f_total_compras"></td>
                        <td id="total_factura_compras"></td>
                        <td id="total_porciento"></td>
                    </tr>
            </tfoot>
        </table>
        <table class="table centered highlight margin-top-50" id="tabla_reporte_detalle_utilidad_cf">
            <thead>
                <tr>
                    <th colspan="7" style="border-bottom:1px solid rgba(0,0,0,.5);">COSTOS FIJOS</th>
                </tr>
                <tr>
                    <th>Fecha</th>
                    <th>Item</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody ></tbody>
            <tfoot>
                <tr>
                    <th class="center-align">TOTAL</th><td></td>
                    <td id="f_total_gastos"></td>
                </tr>
            </tfoot>
        </table>
        <table class="table centered highlight margin-top-50" id="tabla_reporte_detalle_utilidad_gd">
            <thead>
                <tr>
                    <th colspan="7" style="border-bottom:1px solid rgba(0,0,0,.5);">GASTOS DIARIOS</th>
                </tr>
                <tr>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Usuario</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody ></tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="center-align">TOTAL</th>
                    <td id="f_total_gastos_diarios"></td>
                </tr>
            </tfoot>
        </table>
        </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/reportes/utilidades.js')}}"></script>
@endsection
