<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
<?php
    $select_nombre= [""=>"Todos"]+\App\Models\ControlEmpleados::select('nombre','id')->lists('nombre','id');
    $select_cedula= [""=>"Todos"]+\App\Models\ControlEmpleados::select('cedula','id')->lists('cedula','id');
    $fecha_inicio = date("Y-m-d",strtotime("-1month",strtotime(date("Y-m-d"))));
    $fecha_fin= date("Y-m-d");
?>
@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        <p class="titulo">Control empleados</p>
            <div class="col s12 right-align" style="margin-top: -60px;"><i class="fa fa-file-excel-o fa-2x margin-left-20 cyan-text tooltipped waves-effect waves-light" data-position="bottom" data-delay="50" data-tooltip="Reporte en Excel" style="cursor: pointer;" onclick="reporteExcel()"></i></div>
            <div class="col s12">
                {!! Form::open(["id"=>"form-filtos-control-empleados"]) !!}
                    @if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
                        <div class="col s12 m6 l2 offset-l1 input-field">
                    @else
                        <div class="col s12 m6 l2 offset-l2 input-field">
                    @endif
                        {!! Form::select('select_nombre', $select_nombre,null, array('id'=>'select_nombre')) !!}
                        {!! Form::label("select_nombre","Nombre del empleado") !!}
                    </div>
                    <div class="col s12 m6 l2 input-field">
                        {!! Form::date("fecha_inicio",$fecha_inicio,["id"=>"fecha_inicio","class"=>"datepicker"]) !!}
                        {!! Form::label("fecha_inicio","Fecha inicio",["class"=>"active"]) !!}
                    </div>
                    <div class="col s12 m6 l2 input-field">
                        {!! Form::date("fecha_fin",$fecha_fin,["id"=>"fecha_fin","class"=>"datepicker"]) !!}
                        {!! Form::label("fecha_fin","Fecha fin",["class"=>"active"]) !!}
                    </div>
                    @if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
                        <div class="col s12 m6 l2 input-field">
                            {!! Form::label("almacen","Almacén",["class"=>"active"]) !!}
                            {!! Form::select("almacen",['Todos','bodega'=>'Bodega']+\App\Models\Almacen::permitidos()->lists('nombre','id'),null,['id'=>'almacen']) !!}
                        </div>
                    @endif

                {!! Form::close() !!}
                <div class="col s12 l2 center-align">
                    <a class="btn blue-grey darken-2 waves-effect waves-light margin-top-20" id="btn-ver">Ver</a>
                </div>


            </div>
            @include("templates.mensajes",["id_contenedor"=>"control-empleados"])
            <div class="progress hide" id="progress-control-empleados"><div class="indeterminate"></div></div>
            <div class="divider col s12 margin-top-40"></div>
            <div class="col s12 content-table-slide hide" id="contenedor-control-empleados">
                @include('reportes.control_empleados.lista')
            </div>
    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/reportes/control_empleados.js')}}"></script>
@endsection