<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
<?php
$meses = [
        1 => "Enero",
        2 => "Febrero",
        3 => "Marzo",
        4 => "Abril",
        5 => "Mayo",
        6 => "Junio",
        7 => "Julio",
        8 => "Agosto",
        9 => "Septiembre",
        10 => "Octubre",
        11 => "Noviembre",
        12 => "Diciembre",
];

$primer_objetivo = \App\Models\ObjetivoVenta::permitidos()->orderBy("anio","ASC")->orderBy("mes","ASC")->first();
$ultimo_objetivo = \App\Models\ObjetivoVenta::permitidos()->orderBy("anio","DESC")->orderBy("mes","DESC")->first();
if($primer_objetivo)
    $min_anio = $primer_objetivo->anio;
else
    $min_anio = date("Y");
if($ultimo_objetivo)
    $max_anio = $ultimo_objetivo->anio;
else
    $max_anio = date("Y");

for($i = $min_anio;$i <= $max_anio;$i++)
    $anios[$i] = $i;
?>
@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        <p class="titulo">Reporte de objetivos de ventas</p>

            <div class="col s12 right-align" style="margin-top: -60px;">
                <i class="fa fa-line-chart fa-2x margin-right-10 cyan-text tooltipped waves-effect waves-light" data-delay="50" data-tooltip="Generar gráfica" style="cursor: pointer;"  id="btn-grafica"></i>
                <i class="fa fa-file-excel-o fa-2x cyan-text tooltipped waves-effect waves-light" data-position="bottom" data-delay="50" data-tooltip="Reporte en Excel" style="cursor: pointer;" onclick="reporteExcel()"></i>
            </div>
            <div class="col s12">
                {!! Form::open(["id"=>"form-filtos-objetivos-ventas"]) !!}
                @if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
                    <div class="col s12 m5 l1 offset-l1 input-field">
                @else
                    <div class="col s12 m5 l1 offset-l2 input-field">
                @endif
                        {!! Form::select("anio_inicio",$anios,date("Y"),["id"=>"anio_inicio"]) !!}
                        {!! Form::label("anio_inicio","Año inicio") !!}
                    </div>
                    <div class="col s12 m7 l2 input-field">
                        {!! Form::select("mes_inicio",$meses,date("m"),["id"=>"mes_inicio"]) !!}
                        {!! Form::label("mes_inicio","Mes inicio") !!}
                    </div>
                    <div class="col s12 m5 l1 input-field">
                        {!! Form::select("anio_fin",$anios,date("Y"),["id"=>"anio_fin"]) !!}
                        {!! Form::label("anio_fin","Año fin") !!}
                    </div>
                    <div class="col s12 m7 l2 input-field">
                        {!! Form::select("mes_fin",$meses,date("m"),["id"=>"mes_fin"]) !!}
                        {!! Form::label("mes_fin","Mes fin") !!}
                    </div>

                    @if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
                        <div class="col s12 l2 input-field">
                            {!! Form::label("almacen","Almacén",["class"=>"active"]) !!}
                            {!! Form::select("almacen",['Todos']+\App\Models\Almacen::permitidos()->lists('nombre','id'),null,['id'=>'almacen']) !!}
                        </div>
                    @endif
                {!! Form::close() !!}
                <div class="col s12 l2 center-align">
                    <a class="btn blue-grey darken-2 waves-effect waves-light margin-top-20" id="btn-ver">Ver</a>
                </div>


            </div>
            @include("templates.mensajes",["id_contenedor"=>"objetivos-ventas"])
            <div class="progress hide" id="progress-objetivos-ventas"><div class="indeterminate"></div></div>
            <div class="divider col s12 margin-top-40"></div>
            <div class="col s12 content-table-slide scroll-style" id="contenedor-objetivos-ventas">
            </div>
    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/reportes/objetivos_ventas.js')}}"></script>

@endsection