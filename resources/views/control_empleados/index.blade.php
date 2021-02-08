<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","Empleados","configuracion"))
            <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="#" onclick="getEdicionEmpleado(null)"><i class="fa fa-plus"></i></a>
        @endif
        <p class="titulo">Empleados</p>
        <div id="contenedor-lista-controlEmpleados" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"controlEmpleados"])
            <div id="lista-controlEmpleados" class="content-table-slide col s12">
                @include('control_empleados.lista')
            </div>
        </div>

    </div>

 @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","Empleados","configuracion") || \Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","Empleados","configuracion"))
    <div id="modal-accion-controlEmpleados" class="modal modal-fixed-footer ">
        <div class="modal-content">
            <div id="contenido-accion-controlEmpleados">
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
@endif

<div id="modal-ver-controlEmpleados" class="modal modal-fixed-footer " style="width: 60%;min-height: 80% !important;">
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

@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Cerrar sesion","Empleados","configuracion"))
    <div id="modal-abrirCerrar-controlEmpleados" class="modal modal-fixed-footer " style="width: 30%;min-height: 20% !important;">
        <div class="modal-content">   
        <strong style="margin-bottom: 20% !important;"><h4 class="titulo-modal">Por favor confirme</h4></strong> 
        <center> 
            <b><p>¿Está Seguro de realizar esta acción?</p></b> 
            <div class="item-menu"> 
                 <div class="circulo-item-menu orange darken-4" style="margin-top: 10% !important;margin-bottom: 10% !important;">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                </div> 
            </div>
            <p>Cerrará todas las sesiones que tenga abiertas el usuario</p>
        </center>
            {!! Form::hidden("id_empleado",null,["name"=>"id_empleado","id"=>"id_empleado"]) !!}
            {!! Form::hidden("fecha_inicio_sesion",null,["name"=>"fecha_inicio_sesion","id"=>"fecha_inicio_sesion"]) !!}
            {!! Form::hidden("estado_check",null,["name"=>"estado_check","id"=>"estado_check"]) !!}
        </div>
         <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-cierra-session-controlEmpleados">
                <a href="#!" class="btn-flat waves-effect waves-block green-text" id="btn-cierra-todas-las-sesion-controlEmpleados">Aceptar</a>
                <a href="#!" class="btn-flat waves-effect waves-block modal-close cyan-text">Cancelar</a>
            </div>
            <div class="progress hide" id="progres-cierra-session-controlEmpleados">
                <div class="indeterminate"></div>
            </div>
        </div>
    </div>
@endif

@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Cambiar estado del empleado","Empleados","configuracion"))
    <div id="modal-estadoEmpleado-controlEmpleados" class="modal modal-fixed-footer " style="width: 30%;min-height: 20% !important;">
        <div class="modal-content">   
        <strong style="margin-bottom: 20% !important;"><h4 class="titulo-modal">Por favor confirme</h4> </strong> 
        <center style="margin-top: 20% !important;"> 
        
            <div class="item-menu"> 
                 <div class="circulo-item-menu orange darken-4" style="margin-top: 10% !important;margin-bottom: 10% !important;">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                </div> 
            </div>
            <b><p>¿Está Seguro de cambiar el estado actual del empleado?</p></b>
            {!! Form::hidden("id_empleado_c",null,["name"=>"id_empleado_c","id"=>"id_empleado_c"]) !!}
            {!! Form::hidden("estado_empleado_c",null,["name"=>"estado_empleado_c","id"=>"estado_empleado_c"]) !!}
        </center>
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
@endif



<div id="modal-estadoEmpleado-ver" class="modal modal-fixed-footer " style="width: 30%;min-height: 20% !important;">
        <div class="modal-content" id="ver_empleado"> 
        </div>
         <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-ver-controlEmpleados">
                <a href="#!" class="btn-flat waves-effect waves-block modal-close cyan-text">Aceptar</a>
            </div>
        </div>
    </div>



@endsection

@section('js')
    @parent
    <script src="{{asset('js/controlEmpleadosAction.js')}}"></script>
    <script type="text/javascript">
      @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","Empleados","configuracion"))
            setPermisoEditarEmpleado(true);
        @else
            setPermisoEditarEmpleado(false);
        @endif

        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Cambiar estado del empleado","Empleados","configuracion"))
            setPermisoEstadoEmpleado(true);
        @else
            setPermisoEstadoEmpleado(false);
        @endif

        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Cerrar sesion","Empleados","configuracion"))
            setPermisoEstadoSesion(true);
        @else
            setPermisoEstadoSesion(false);
        @endif
</script>
    <script type="text/javascript">
        $(function(){
            inicializarMaterialize();    
        });
    </script>
@stop
