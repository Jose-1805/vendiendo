<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px;">
        <p class="titulo">Módulos
            @if((Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
                || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            )
                <i class="fa fa-calendar right blue-grey-text text-darken-2 tooltipped modal-trigger" href="#caducidad-funciones" data-position="bottom" data-delay="50" data-tooltip="Cambiar caducidad de funciones" style="cursor: pointer;"></i>
            @endif
        </p>

        <div id="contenedor-lista-modulos" class="col s12">
            @include('modulo.lista')
        </div>
    </div>

    @if((Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
                || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            )
      <div id="caducidad-funciones" class="modal modal-fixed-footer modal-small" style="min-height: 80%;">
        <div class="modal-content">
            <p class="titulo-modal">Caducidad de módulos</p>
            @include('templates.mensajes',['id_contenedor'=>'caducidad'])
            <p class="text-info">Reestablezca la caducidad de todas las funcionalidades del sistema para sus usuarios. Fecha máxima <strong>{{date('Y-m-d',strtotime(Auth::user()->caducidadPlan()))}}.</strong></p>
            {!! Form::open(['id'=>'form-caducidad']) !!}
                {!! Form::label('fecha_caducidad','Nueva fecha de caducidad') !!}
                {!! Form::date('fecha_caducidad',date('Y-m-d',strtotime(Auth::user()->caducidadPlan())),['id'=>'fecha_caducidad','class'=>'']) !!}
                <?php
                    $users = \App\User::permitidos()->get();
                ?>
                <div class="collection">
                @forelse($users as $u)
                    <a href="#!" class="collection-item">
                        <p>
                            <input type="checkbox" id="user-{{$u->id}}" value="{{$u->id}}" name="usuarios[]" checked="checked"/>
                            <label for="user-{{$u->id}}">{{$u->nombres.' '.$u->apellidos}}</label>
                        </p>
                    </a>
                @empty
                    <p class="center-align">No existen usuarios para seleccionar</p>
                @endforelse
                </div>
            {!! Form::close() !!}
        </div>
        <div class="modal-footer">
            @if(count($users)>0)
                <a href="#!" class="waves-effect waves-darken btn-flat" id="btn-actualizar-caducidad">Guardar</a>
            @endif
            <a href="#!" class="modal-action modal-close waves-effect waves-darken btn-flat ">Cerrar</a>
        </div>
    </div>
    @endif
@stop

@section('js')
@parent
    <script src="{{asset('js/moduloAction.js')}}"></script>
@stop

@section('css')
@parent
    <link href="{{asset('css/modulo.css')}}" rel="stylesheet">
@stop