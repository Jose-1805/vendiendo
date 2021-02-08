<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
<?php
    $fecha_inicio = date("Y-m-d",strtotime("-1month",strtotime(date("Y-m-d"))));
    $fecha_fin= date("Y-m-d");
        if(!isset($tipo)){
            $tipo = "Todo";
        }
?>
@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        <p class="titulo">Reporte abonos</p>

            <div class="col s12 right-align" style="margin-top: -60px;"><i class="fa fa-file-excel-o fa-2x margin-left-20 cyan-text tooltipped waves-effect waves-light" data-position="bottom" data-delay="50" data-tooltip="Reporte en Excel" style="cursor: pointer;" onclick="reporteExcel()"></i></div>
            <div class="col s12">
                {!! Form::open(["id"=>"form-filtros-abonos"]) !!}

                    @if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
                        <div class="col s12 m6 l2 input-field">
                    @else
                        <div class="col s12 m6 l2 offset-l1 input-field">
                    @endif
                        <select id="tipo" name="tipo">
                            <option value="factura">Ventas</option>
                            <option value="compra">Compras</option>
                        </select>
                        {!! Form::label("tipo","Abonos a",["class"=>"active","style"=>"top:10px;"]) !!}
                    </div>

                    <div class="col s12 m6 l1 input-field">
                        {!! Form::date("fecha_inicio",$fecha_inicio,["id"=>"fecha_inicio","class"=>"datepicker"]) !!}
                        {!! Form::label("fecha_inicio","Fecha inicio",["class"=>"active"]) !!}
                    </div>
                    <div class="col s12 m6 l1 input-field">
                        {!! Form::date("fecha_fin",$fecha_fin,["id"=>"fecha_fin","class"=>"datepicker"]) !!}
                        {!! Form::label("fecha_fin","Fecha fin",["class"=>"active"]) !!}
                    </div>

                    <div class="col s12 m6 l2 input-field">
                        {!! Form::text("nombre_cliente",null,["id"=>"nombre_cliente","Placeholder"=>"Nombre del cliente","title"=>"Nombre del cliente"]) !!}
                        {!! Form::label("nombre_cliente","Nombre",["class"=>"active"]) !!}
                    </div>

                    <div class="col s12 m6 l2 input-field">
                        {!! Form::text("identificacion_cliente",null,["id"=>"identificacion_cliente","placeholder"=>"Identificación del cliente","title"=>"Identificación del cliente"]) !!}
                        {!! Form::label("identificacion_cliente","Identificación",["class"=>"active"]) !!}
                    </div>

                    <div class="col s12 m6 l2 input-field hide">
                        {!! Form::text("nombre_proveedor",null,["id"=>"nombre_proveedor","placeholder"=>"Nombre del proveedor","title"=>"Nombre del proveedor"]) !!}
                        {!! Form::label("nombre_proveedor","Nombre",["class"=>"active"]) !!}
                    </div>

                    <div class="col s12 m6 l2 input-field hide">
                        {!! Form::text("identificacion_proveedor",null,["id"=>"identificacion_proveedor","placeholder"=>"Identificación del proveedor","title"=>"Identificación del proveedor"]) !!}
                        {!! Form::label("identificacion_proveedor","Identificación",["class"=>"active"]) !!}
                    </div>

                    @if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
                        <div class="col s12 m6 l2 input-field">
                            {!! Form::label("almacen","Almacén",["class"=>"active"]) !!}
                            {!! Form::select("almacen",['Todos']+\App\Models\Almacen::permitidos()->lists('nombre','id'),null,['id'=>'almacen']) !!}
                        </div>
                    @endif

                {!! Form::close() !!}
                <div class="col s12 l2 center-align">
                    <a class="btn blue-grey darken-2 waves-effect waves-light margin-top-20" id="btn-ver">Ver</a>
                </div>


            </div>
            @include("templates.mensajes",["id_contenedor"=>"abonos"])
            <div class="progress hide" id="progress-abonos"><div class="indeterminate"></div></div>
            <div class="divider col s12 margin-top-40"></div>
            <div class="col s12 content-table-slide hide" id="contenedor-reporte-abonos">
                <table class="table centered highlight" id="tabla_reporte_abonos">
                    <thead>
                        <th>{!! \App\TildeHtml::TildesToHtml('Número') !!}</th>
                        <th id="th_abono_2"></th>
                        <th>Fecha</th>
                        <th>Valor</th>
                        <th>Saldo</th>
                        <th>Cantidad de cuotas</th>
                        <th>Cuotas pagadas</th>
                        <th>{!! \App\TildeHtml::TildesToHtml('Fecha última cuota') !!}</th>
                    </thead>
                        <tbody>
                        </tbody>
                    <tfoot>
                         <tr>
                            <td colspan="4" style="text-align: center;"><strong>Total valores</strong></td>
                            <td colspan="4" style="text-align: center;" id="t_footer_tv"></td>
                        </tr>
                        <tr>
                            <td colspan="4" style="text-align: center;"><strong>Total saldos</strong></td>
                            <td colspan="4" style="text-align: center;" id="t_footer_ts"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/reportes/abonos.js')}}"></script>
@endsection