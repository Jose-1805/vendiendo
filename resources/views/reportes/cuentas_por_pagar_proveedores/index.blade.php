<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        <p class="titulo">Cuentas por pagar general <label style="font-size: 16px">"A fecha {{ \App\General::fechaActualString() }}"</label></p>
        <div class="input-field col s12" >
            <button class="btn blue-grey darken-2 waves-effect waves-light" id="btn-proveedores-compras-lista" style="float: right;margin-top: -80px;padding: 0px 5px !important;">
                <i class="fa fa-eye" style=""></i><i class="fa fa-spin fa-spinner hide icono-load-buscar" style=""></i></button>
        </div>

        <div class="col s12 right-align" style="margin-top: -77px;margin-left: -45px;">
            <i class="fa fa-file-excel-o fa-2x margin-left-20 cyan-text tooltipped waves-effect waves-light" data-position="bottom" data-delay="50" data-tooltip="Reporte en Excel" style="cursor: pointer;" onclick="reporteExcelCPG()"></i>
        </div>
        <div id="contenedor-lista-compras-pagar" class="col s12">
            <div id="lista-compras-pagar">
                @include('reportes.cuentas_por_pagar_proveedores.lista')
            </div>
        </div>
    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/reportes/cuentas_X_pagar_proveedores.js')}}"></script>
@stop