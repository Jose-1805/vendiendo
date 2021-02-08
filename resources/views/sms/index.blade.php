<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px;">
        <?php
            $plan = \Illuminate\Support\Facades\Auth::user()->plan();
            $cantidad_enviada = \App\Models\Sms::countSmsSendOk();
            $cantidad_permitida = $plan->n_promociones_sms;
        ?>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","sms","inicio"))
            @if($cantidad_permitida == 0 || ($cantidad_enviada < $cantidad_permitida))
                <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="{{url('/sms/create')}}"><i class="fa fa-plus"></i></a>
            @endif
        @endif
        <p class="titulo">Promociones SMS registradas</p>
            @if(!($cantidad_permitida == 0 || ($cantidad_enviada < $cantidad_permitida)))
                <div class="col s12 contenedor-confirmacion blue lighten-5 blue-text" id="contenedor-confirmacion-n_usuarios">
                    <i class='fa fa-close btn-cerrar-confirmacion'></i>
                    <ul>
                        <li>No es posible crear más mensajes de texto, ha registrado la cantidad máxima permitida en su plan.</li>
                    </ul>
                </div>
            @endif
            @include("templates.mensajes",['id_contenedor'=>'sms'])
            <div id="lista-compras" class="col s12 content-table-slide">
                @include('sms.lista')
            </div>

    </div>
@endsection

