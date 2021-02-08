<?php
$secciones = [];

$modulos_inicio = [];
$modulos_configuracion = [];

$margenes = [0,0.53,0.96,1.38,1.8,2.22,2.64,3,3,3,3,1.85];
$clases_columns = ["one-wide","one-wide","two-wide","three-wide","four-wide","five-wide","six-wide","seven-wide","eight-wide","nine-wide","ten-wide"];

$obj_mod_inicio = [];
$obj_mod_configuracion = [];

//dd(\Illuminate\Support\Facades\Auth::user()->modulosActivos());
$modulos = \Illuminate\Support\Facades\Auth::user()->modulosActivos();

for($i = 0;$i < count($modulos);$i++){
    $modulo = $modulos[$i];
    if(!in_array($modulo->seccion,$secciones)){
        $secciones[] = $modulo->seccion;
    }

    switch($modulo->seccion){
        case "inicio": $modulos_inicio[] = $modulo;
            $obj_mod_inicio[] = $modulo;
            break;
        case "configuracion": $modulos_configuracion[] = $modulo;
            $obj_mod_configuracion[] = $modulo;
            break;
    }
}

$ultima_actualizacion = \App\Models\ActualizacionSistema::ultimaActualizacion();
$actualizar = false;
if($ultima_actualizacion && !$ultima_actualizacion->usuarioTieneActualizacion()){
    $actualizar = true;
}
?>
@extends("templates.master")

@section('titulo')
    Vendiendo.co - Inicio
@stop

@section('css')
@parent
    <link rel="stylesheet" href="{{ asset('css/inicio.css') }}">
@stop

@section('contenido')
    <div class="hide-on-med-and-down" style="">
        @include('templates.view_secciones')
    </div>

    <div class="row hide-on-large-only white" style="padding-top: 90px;">
        @if(count($secciones))
            @if(count($modulos_inicio))
                <p class="titulo-modal text-center" style="font-size: x-large;">Inicio</p>
                @include("menu.inicio")
            @endif

            @if(count($modulos_configuracion))
                    <p class="titulo-modal text-center" style="font-size: x-large;">Configuración</p>
                @include("menu.configuracion",["hide"=>""])
            @endif

            @if(\Illuminate\Support\Facades\Auth::user()->perfil->nombre != "superadministrador" && \Illuminate\Support\Facades\Auth::user()->perfil->nombre != "proveedor" && Auth::user()->permiso_reportes == 'si')
                <p class="titulo-modal text-center" style="font-size: x-large;">Reportes</p>
                @include('menu.reportes',["hide"=>""])
            @endif
        @else
            <p class="center-align">No existen modulos relacionados con su cuenta de usuario.</p>
        @endif
    </div>

    @if($actualizar)
        <div id="modal-actualizar" class="modal modal-fixed-footer modal-small" style="min-height: 40%;">
            <div class="modal-content">
                <p class="titulo-modal">Actualización de sistema</p>
                <p>{{$ultima_actualizacion->mensaje}}</p>
                <p><strong>Versión: </strong>{{$ultima_actualizacion->version}}</p>
            </div>
            <div class="modal-footer">
                <a href="#" id="btn-actualizar" class="waves-effect waves-light blue-grey darken-2 btn">Actualizar</a>
            </div>
        </div>
    @endif
@stop

@section('js')
    @parent
    <script>
        @if($actualizar)
            $(function () {
                $('#modal-actualizar').openModal({
                    dismissible: false,
                    opacity: .8
                })

                $('#btn-actualizar').click(function () {
                    DialogCargando('Actualizando ...');
                    var params = {_token:$('#general-token').val()};
                    var url = $('#base_url').val()+'/home/guardar-usuario-actualizacion';
                    $.post(url,params,function (data) {
                        if(data.success){
                            window.location.reload(true);
                        }
                    })
                })
            })
        @endif
    </script>
@endsection
