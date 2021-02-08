<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
<?php
    if($accion == "Crear")
        $url = url("/pqrs/store");
    else if($accion == "Editar")
        $url = url("/pqrs/update");
?>
@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        <p class="titulo">{{$accion}} pqrs</p>
        @include("templates.mensajes",["id_contenedor"=>"action-pqrs"])
        {!! Form::model($pqrs,["url"=>$url,"id"=>"form-pqrs"]) !!}
            @include('pqrs.form')
        {!! Form::close() !!}
        <div class="col s12 right-align" id="contenedor-botones-action-pqrs">
            <a class="btn waves-effect waves-light blue-grey darken-2" onclick="action()">Guardar</a>
        </div>
        <div class="progress hide" id="progress-action-pqrs">
            <div class="indeterminate"></div>
        </div>
    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/pqrs.js')}}"></script>
@stop