<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        <p class="titulo">Registro de Entrada y Salida de Empleados</p>
        <p id="mensaje"></p>
        <div id="contenedor-lista-control-empleados" class="col s12" style="margin-bottom: 50px;">
            @include("templates.mensajes",["id_contenedor"=>"controlEmpleados"])
            <div id="inicio-control-empleados" class="content-table-slide col s12">
                <div class="container">
                 <center>
                 <label>Hora actual en el sistema</label>
                 <div id="hora" class="flow-text blue-text text-darken-2"></div></center>
                 <div>
                      {!! Form::text("codigo_barras_empleado",null,["id"=>"codigo_barras_empleado","placeholder"=>"Por favor pase el  código de barras del empleado"]) !!}
                 </div>
                </div>
            </div>
        </div>
        <b><small>Usuarios ingresados hoy</small></b><hr>
        <div id="contenedor-lista-controlEmpleados" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"controlEmpleados"])
            <div id="lista-controlEmpleados" class="content-table-slide col s12">
                @include('control_empleados.control.lista')
            </div>
        </div>

    </div>

<div id="modal-accion-controlEmpleados" class="modal modal-fixed-footer ">
    <div class="modal-content">
        <div id="contenido-accion-controlEmpleados">
         @include('control_empleados.form')
        </div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-accion-controlEmpleados">
            <a href="#!" class="btn-flat waves-effect waves-block green-text" id="btn-accion-controlEmpleados">Guardar</a>
            <a href="#!" class="btn-flat waves-effect waves-block modal-close cyan-text">Cancelar</a>
        </div>
        <div class="progress hide" id="progres-accion-controlEmpleados">
            <div class="indeterminate"></div>
        </div>
    </div>
</div>

<div id="modal-ver-controlEmpleados" class="modal modal-fixed-footer " style="width: 95%;min-height: 80% !important;">
    <div class="modal-content">
        <div id="contenido-ver-controlEmpleados">
        </div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-ver-controlEmpleados">
            <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cerrar</a>
        </div>
        <div class="progress hide" id="progres-ver-controlEmpleados">
            <div class="indeterminate"></div>
        </div>
    </div>
</div>

@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Cerrar sesion","Control de empleados","inicio"))
    <div id="modal-abrirCerrar-controlEmpleados" class="modal modal-fixed-footer " style="width: 30%;min-height: 20% !important;">
        <div class="modal-content">   
            <strong style="margin-bottom: 20% !important;"><h4 class="titulo-modal">Por favor confirme</h4></strong>
            <center style="margin-top: 20% !important;"> 
                <b><p>¿Está Seguro de realizar esta acción?</p></b> 
                <div class="item-menu"> 
                     <div class="circulo-item-menu orange darken-4" style="margin-top: 10% !important;margin-bottom: 10% !important;">
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                    </div> 
                </div>
            </center>
            {!! Form::hidden("id_empleado",null,["name"=>"id_empleado","id"=>"id_empleado"]) !!}
            {!! Form::hidden("fecha_inicio_sesion",null,["name"=>"fecha_inicio_sesion","id"=>"fecha_inicio_sesion"]) !!}
            {!! Form::hidden("estado_check",null,["name"=>"estado_check","id"=>"estado_check"]) !!}
        </div>
         <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-cierra-session-controlEmpleados">
                <a href="#!" class="btn-flat waves-effect waves-block green-text" id="btn-cierra-sesion-controlEmpleados">Aceptar</a>
                <a href="#!" class="btn-flat waves-effect waves-block modal-close cyan-text">Cancelar</a>
            </div>
            <div class="progress hide" id="progres-cierra-session-controlEmpleados">
                <div class="indeterminate"></div>
            </div>
        </div>
    </div>
@endif

<div id="modal-estadoEmpleado-controlEmpleados" class="modal modal-fixed-footer " style="width: 30%;min-height: 20% !important;">
    <div class="modal-content">   
    <h4 class="titulo-modal">Por favor confirme</h4> 
        <p>¿Está Seguro de cambiar el estado de este empleado?</p>
        {!! Form::hidden("id_empleado_c",null,["name"=>"id_empleado_c","id"=>"id_empleado_c"]) !!}
        {!! Form::hidden("estado_empleado_c",null,["name"=>"estado_empleado_c","id"=>"estado_empleado_c"]) !!}
    </div>
     <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-estado-empleado-controlEmpleados">
            <a href="#!" class="btn-flat waves-effect waves-block green-text" id="btn-cambia-estado-controlEmpleados">Aceptar</a>
            <a href="#!" class="btn-flat waves-effect waves-block modal-close cyan-text">Cancelar</a>
        </div>
        <div class="progress hide" id="progres-estado-empleado-controlEmpleados">
            <div class="indeterminate"></div>
        </div>
    </div>
</div>

@endsection

@section('js')
    @parent
    <script src="{{asset('js/controlEmpleadosAction.js')}}"></script>
    <script type="text/javascript">
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Cerrar sesion","Control de empleados","inicio"))
            setPermisoEstadoSesion(true);
        @else
            setPermisoEstadoSesion(false);
        @endif

        window.onload=hora;
        var fecha_php ="<?php echo date('Y-m-d H:i:s'); ?>";
        fecha = new Date(fecha_php);
        function hora(){
            var hora=fecha.getHours();
            var minutos=fecha.getMinutes();
            var segundos=fecha.getSeconds();
            if(hora<10){ hora='0'+hora;}
            if(minutos<10){minutos='0'+minutos; }
            if(segundos<10){ segundos='0'+segundos; }
            fech=hora+":"+minutos+":"+segundos;
            document.getElementById('hora').innerHTML=fech;
            fecha.setSeconds(fecha.getSeconds()+1);
            setTimeout("hora()",1000);
        }


        $(function(){
            $("#codigo_barras_empleado").focus();
            cargarTablaControlEmpleadosInicio();
            inicializarMaterialize();    
        });
    </script>
@stop
