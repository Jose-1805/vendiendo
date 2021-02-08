<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
<?php
    $fecha_inicio = date("Y-m-d",strtotime("-1month",strtotime(date("Y-m-d"))));
    $fecha_fin= date("Y-m-d");
    $top= 10;
    $rep_categoria= null;
        $list_top = ["10"=>10,"20"=>20,"30"=>30,"40"=>40,"50"=>50,"60"=>60,"70"=>70,"80"=>80,"90"=>90,"100"=>100];
?>
@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        <p class="titulo">Reporte de movimientos bancarios</p>

            <div class="col s12 right-align" style="margin-top: -60px;">
                <i class="fa fa-file-excel-o fa-2x cyan-text tooltipped waves-effect waves-light" data-position="bottom" data-delay="50" data-tooltip="Reporte en Excel" style="cursor: pointer;" onclick="reporteExcel()"></i>
            </div>
            <div class="col s12">
                {!! Form::open(["id"=>"form-filtos-movimientos-bancarios"]) !!}
                <div class="col s12 m6 l2 input-field">
                    {!! Form::date("fecha_inicio",$fecha_inicio,["id"=>"fecha_inicio","class"=>"datepicker"]) !!}
                    {!! Form::label("fecha_inicio","Fecha inicio",["class"=>"active"]) !!}
                </div>
                <div class="col s12 m6 l2 input-field">
                    {!! Form::date("fecha_fin",$fecha_fin,["id"=>"fecha_fin","class"=>"datepicker"]) !!}
                    {!! Form::label("fecha_fin","Fecha fin",["class"=>"active"]) !!}
                </div>
                <div class="col s12 m6 l2 input-field">
                    {!! Form::select("desde",["todo"=>"Todo","caja maestra"=>"Caja maestra","usuario"=>"Usuario","facturas"=>"Facturas"],null,["id"=>"desde"]) !!}
                    {!! Form::label("desde","Desde",["class"=>"active","style"=>"top:10px;"]) !!}
                </div>
                <div class="col s12 m6 l2 input-field">
                    {!! Form::select("tipo",["todo"=>"Todo","retiros"=>"Retiros","consignaciones"=>"Consignaciones"],null,["id"=>"tipo"]) !!}
                    {!! Form::label("tipo","Tipo de movimiento",["class"=>"active","style"=>"top:10px;"]) !!}
                </div>
                <div class="col s12 m6 l2 input-field">
                    {!! Form::select("cuenta",[""=>"Todas"]+\App\Models\CuentaBancaria::lista(),null,["id"=>"cuenta"]) !!}
                    {!! Form::label("cuenta","Cuenta",["class"=>"active","style"=>"top:10px;"]) !!}
                </div>
                {!! Form::close() !!}
                <div class="col s12 l2 center-align">
                    <a class="btn blue-grey darken-2 waves-effect waves-light margin-top-20" id="btn-ver">Ver</a>
                </div>


            </div>
            @include("templates.mensajes",["id_contenedor"=>"movimientos-bancarios"])
            <div class="progress hide" id="progress-movimientos-bancarios"><div class="indeterminate"></div></div>
            <div class="divider col s12 margin-top-40"></div>
            <div class="col s12 content-table-slide scroll-style" id="contenedor-movimientos-bancarios">
            </div>
    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/reportes/movimientos_bancarios.js')}}"></script>

@endsection