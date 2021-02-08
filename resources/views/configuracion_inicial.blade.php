<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-10" style="margin-top: 85px;">
        @if($opciones['perfil_log'] == 'administrador')
        <p class="titulo">Configuración inicial</p>
        <div class="col s12" style="background-color: #dffffd; color: #2bbbad;" id="mensaje-configuracion-inicial">
            <ul>
                <li>
                    <p>
                        Para dar inicio a la operación de los servicios de <b>Vendiendo.co</b> es necesaria la configuración inicial de lo
                        siguiente:
                    </p>
                </li>
            </ul>
        </div>
        <?php
            $posicion = \Illuminate\Support\Facades\Session::get("posicion");

            $activo_unidades = '';
            $activo_categorias = $activo_proveedores = $activo_resoluciones = '';

            if ($posicion == 'Unidades'){
                $activo_unidades = "class='active'";
                $activo_categorias = $activo_proveedores = $activo_resoluciones = '';
            }
            if ($posicion == 'Categorias'){
                $activo_unidades = $activo_proveedores = $activo_resoluciones = '';
                $activo_categorias = "class='active'";
            }
            if ($posicion == 'Proveedores'){
                $activo_proveedores = "class='active'";
                $activo_unidades = $activo_categorias = $activo_resoluciones = '';
            }
            if ($posicion == 'Resoluciones'){
                $activo_resoluciones = "class='active'";
                $activo_unidades = $activo_categorias = $activo_proveedores = '';
            }
            $checked_u = $checked_c = $checked_p = $checked_r = '';

            if (isset($opciones['unidades']) && isset($opciones['categorias'])&& isset($opciones['proveedores'])){
                if ($opciones['unidades'] >0)$checked_u = "<i class='fa fa-check-circle fa-2x green-text margin-left-20' aria-hidden='true'></i>";
                if ($opciones['categorias'] >0)$checked_c = "<i class='fa fa-check-circle fa-2x green-text margin-left-20' aria-hidden='true'></i>";
                if ($opciones['proveedores'] >0)$checked_p = "<i class='fa fa-check-circle fa-2x green-text margin-left-20' aria-hidden='true'></i>";
            }
            if (isset($opciones['resoluciones']))
                if ($opciones['resoluciones'] >0)$checked_r = "<i class='fa fa-check-circle fa-2x green-text margin-left-20' aria-hidden='true'></i>";
        ?>
        @include("templates.mensajes",['id_contenedor'=>'configuracion_inicial'])
        <div class="col s12" style="padding: 20px;">
            <div class="row">
                <div class="col s12">
                    <ul class="tabs">
                        @if(isset($opciones['unidades']) && isset($opciones['categorias'])&& isset($opciones['proveedores']))
                            <li class="tab col s3"><a {!! $activo_unidades !!} href="#configurar-unidades">Crear unidades {!! $checked_u !!}</a></li>
                            <li class="tab col s3"><a {!! $activo_categorias !!} href="#configurar-categorias">Crear categorias {!! $checked_c !!}</a></li>
                            <li class="tab col s3"><a {!! $activo_proveedores !!} href="#configurar-proveedores">Crear proveedores {!! $checked_p !!}</a></li>
                        @endif
                        @if(isset($opciones['resoluciones']))
                            <li class="tab col s3"><a {!! $activo_resoluciones !!} href="#configurar-resoluciones">Crear resolución {!! $checked_r !!}</a></li>
                        @endif
                    </ul>
                </div>
                @if(isset($opciones['unidades']) && isset($opciones['categorias'])&& isset($opciones['proveedores']))
                    <div id="configurar-unidades" class="col s12">@include('configuracion_inicial.form_unidades')</div>
                    <div id="configurar-categorias" class="col s12">@include('configuracion_inicial.form_categorias')</div>
                    <div id="configurar-proveedores" class="col s12">@include('configuracion_inicial.form_proveedores')</div>
                @endif
                @if(isset($opciones['resoluciones']))
                    <div id="configurar-resoluciones" class="col s12">@include('configuracion_inicial.form_resolucion')</div>
                @endif
            </div>
        </div>
        @else
            <div class="col s12 " style="background-color: #dffffd; color: #2bbbad" id="mensaje-configuracion-inicial-usuario">
                <ul>
                    <li>
                        <p>
                            Para dar inicio a la operación de los servicios de <b>Vendiendo.co</b> es necesaria la configuración inicial, comuniquese con el administrador del sistema.
                        </p>
                    </li>
                </ul>
            </div>
        @endif
    </div>
@endsection