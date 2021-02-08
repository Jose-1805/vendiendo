<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    {!! Form::open([ 'url' => 'caja/store' ,'data-toggle'=>'validator', 'class' => 'form-inline','role'=> 'form','method' => 'POST', 'novalidate', 'id' => 'form-caja',  'autocomplete' =>'off'] ) !!}
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px;">
        <p class="titulo">Iniciar caja mayor</p>
        <div class="col s12" style="padding: 20px;">

            @include('templates.mensajes',['id_contenedor'=>'caja'])
            <div class="input-field col s12 m6 offset-m2 center-block">
                @if(!$ultima_caja)
                    {!!Form::label("efectivo_inicial","Efectivo inicial:")!!}
                    {!!Form::text("efectivo_inicial",null,["id"=>"efectivo_inicial","class"=>"cantidad-numerico num-entero","placeholder"=>"Ingrese efectivo inicial","maxlength"=>"100"])!!}
                @else
                    <p class="center-align" style="font-size: 25px;margin-top: 0px;">La caja maestra se iniciará con <strong>${{number_format($ultima_caja->efectivo_final,0,",",".")}}</strong></p>
                @endif
            </div>
            <div class="col s12 m2 center" id="contenedor-action-form-caja" style="margin-top: 20px;">
                <a class="btn blue-grey darken-2 waves-effect waves-light" id="btn-action-form-caja">Iniciar</a>
            </div>
            <div class="progress hide" id="progress-action-form-caja" style="top: 30px;margin-bottom: 30px;">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
        <input type="hidden" id="valor_input_hidden" value="123456789">
    </div>
    {!! Form::close() !!}
@endsection

@section('js')
    @parent
    <script src="{{asset('js/productos/funciones.js')}}"></script>
    <script src="{{asset('js/caja/cajaAction.js')}}"></script>

    <script>
        $(document).ready(function (){
            /*$('.cantidad-numerico').keyup(function (){
             this.value = (this.value + '').replace(/[^0-9]/g, '');
             });*/
            $('.cantidad-numerico').numeric();
            $('.cantidad-decimal').numeric(".");

        });
    </script>
@stop