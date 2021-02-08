<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
@section('js')
    @parent
    <script src="{{asset('js/cuentasBancariasAction.js')}}"></script>

    <script>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Consignar","cuentas bancarias","configuracion"))
            setPermisoConsignar(true);
        @endif
    </script>
@stop

@section('contenido')
<div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
    @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","cuentas bancarias","configuracion"))
            <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped modal-trigger agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="#modal-crear-cuenta-bancaria"><i class="fa fa-plus"></i></a>
    @endif
            <p class="titulo">Cuentas bancarias</p>
            <div id="contenedor-lista-cuentas-bancarias" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"cuentas-bancarias"])
            <div id="lista-cuentas-bancarias" class="content-table-slide col s12">
                @include('cuentas_bancarias.lista')
            </div>
        </div>

    </div>

<div id="modal-crear-cuenta-bancaria" class="modal modal-fixed-footer modal-small" style="min-height: 70%;">
    <div class="modal-content">
        <div id="contenido-crear-cuenta-bancaria">
            @include('cuentas_bancarias.form')
        </div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-crear-cuenta-bancaria">
            <a href="#!" class="btn-flat waves-effect waves-block green-text" onclick="crear()">Guardar</a>
            <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cancelar</a>
        </div>
    </div>
</div>


    @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Consignar","cuentas bancarias","configuracion"))
        <div id="modal-consignar" class="modal modal-fixed-footer modal-small" style="min-height: 50%;">
            <div class="modal-content">
                <p class="titulo-modal">Realizar consignación</p>
                @include('templates.mensajes',["id_contenedor"=>"consignar"])
                <div id="contenido-consignar">
                    {!! Form::label("valor","Valor") !!}
                    {!! Form::text("valor",0,["id"=>"valor","class"=>"num-entero"]) !!}
                </div>
            </div>

            <div class="modal-footer">
                <div class="col s12" id="contenedor-botones-consignar">
                    <a href="#!" class="btn-flat waves-effect waves-block green-text" onclick="consignar()">Consignar</a>
                    <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cancelar</a>
                </div>
            </div>
        </div>
        <div id="modal-historial-consignaciones" class="modal modal-fixed-footer " style="min-height: 80%;">
            <div class="modal-content">
                <p class="titulo-modal">Historial de consignaciones</p>

                <table id="tabla_historial_consignaciones">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Valor</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                </table>


            </div>

            <div class="modal-footer">
                <div class="col s12" id="contenedor-botones-historial-consignaciones">
                    <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cerrar</a>
                </div>
            </div>
        </div>
    @endif
@endsection

