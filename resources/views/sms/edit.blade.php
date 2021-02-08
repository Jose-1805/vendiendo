<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-10" style="margin-top: 85px;" >
            <p class="titulo">Actualizar mensaje de texo</p>
            <div class="col s12" style="background-color: #dffffd; color: #2bbbad;">
                <ul>
                    <li>
                        <p>
                            Al momento de crear un mensaje debe tener en cuenta que esté no debe sobrepasar los 160 carácteres y no debe contener carácteres especiales
                        </p>
                    </li>
                </ul>
            </div>
        <div id="carga"></div>
            @include("templates.mensajes",['id_contenedor'=>'sms'])
            <div class="col s12" style="padding: 20px;">
                {!! Form::open([ 'url' => 'sms/update/' ,'data-toggle'=>'validator', 'class' => 'form-inline','role'=> 'form','method' => 'POST', 'novalidate', 'id' => 'form-sms',  'autocomplete' =>'off'] ) !!}
                <input type="hidden" id="id-sms" value="{{$sms->id}}">
                <div class="col s12" style="padding: 20px;">
                    <div class="input-field col s12 m6">
                        {!!Form::label("titulo","Nombre negocio")!!}
                        {!! Form::text("titulo",$sms->titulo,["id"=>"titulo_sms","placeholder"=>"Ingrese el nombre del negocio","class"=>"validate active"]) !!}
                        <span id="span_titulo" style="display: none; color: #00aba9;"><b>El titulo debe tener máximo 20 carácteres!</b></span>
                    </div>
                    <div id="numero-caracteres-titulo" class="right-align hide" style="position: absolute;margin-left: 380px;background-color: #dffffd; color: #2bbbad;">

                    </div>

                    <div class="input-field col s12 m6">
                        {!!Form::label("mensaje","Mensaje")!!}
                        {!! Form::textarea("mensaje",$sms->mensaje,["id"=>"mensaje_sms","class"=>"materialize-textarea","placeholder"=>"Describa brevemente el mensaje","onkeyup"=>"validaCantidadCaracteres()"]) !!}
                        <span id="span_mensaje" style="display: none; color: #00aba9;"><b>El mensaje debe tener máximo 120 carácteres!</b></span>
                    </div>
                    <div id="numero-caracteres-mensaje" class="right-align hide" style="position: absolute;margin-left: 900px;background-color: #dffffd; color: #2bbbad;">

                    </div>
                    <div class="col s12 " id="lista-telefonos">
                        @include('sms.list_phones')
                    </div>

                    <div class="col s12 center" id="contenedor-action-form-sms" style="margin-top: 30px;">
                        <a class="btn cyan waves-effect waves-light" id="btn-action-form-sms-edit">Guardar</a>
                    </div>

                    <div class="progress hide" id="progress-action-form-sms" style="top: 30px;margin-bottom: 30px;">
                        <div class="indeterminate cyan"></div>
                    </div>
                </div>
                {!!Form::close()!!}
            </div>
    </div>
@endsection

<div id="modal-agregar-telefonos" class="modal small">
    <div class="modal-content">
        <p>Desea programar esté mensaje</p>
    </div>
    <div class="modal-footer">
        <a onclick="viewAsignartelefonos()" class=" modal-action modal-close waves-effect waves-green btn-flat">Aceptar</a>
        <a href="#!" class=" modal-action modal-close waves-effect waves-green btn-flat">Cancelar</a>
    </div>
</div>



@section('js')
    @parent
    <script src="{{asset('js/sms/smsAction.js')}}"></script>
@stop