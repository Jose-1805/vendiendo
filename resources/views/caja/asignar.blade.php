<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
@section('js')
    @parent
    <script src="{{asset('js/caja/cajaAsignar.js')}}"></script>
@stop

@section('css')
    @parent
    <link href="{{asset('css/cajas.css')}}" rel="stylesheet">
@endsection

@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-50" style="margin-top: 85px">
        <p class="titulo">Asignar cajas</p>
        @include("templates.mensajes",["id_contenedor"=>"cajas"])
        <p>Arrastre el usuario deseado hasta la caja a la cual lo quiere asignar.</p>
        <div id="contenedor-lista-productos" class="col s12">
            <div id="" class="col s12">
                <div class="col s12 m12 l6">
                    <div class="padding-30 z-depth-1">
                        <p class="titulo-modal">Usuarios</p>
                            <ul class="lista-elementos contenedor-usuarios inicial padding-bottom-10">
                                @foreach($usuarios as $u)
                                    <li class="usuario" data-usuario="{{$u->id}}">
                                        <i class="avatar fa fa-user circle green"></i>
                                        <span class="font-small">{{$u->nombres." ".$u->apellidos}}</span>
                                    </li>
                                @endforeach
                            </ul>
                    </div>
                </div>

                <div class="col s12 m12 l6">
                    <div class="padding-30 z-depth-1">
                        <p class="titulo-modal">Cajas</p>
                            <ul class="lista-elementos">
                                @foreach($cajas as $c)
                                    <?php
                                        $class_color_icon = "blue lighten-2";
                                        $class_color_item = "white";
                                        $class_item = "contenedor-usuarios destino";
                                        $razon_estado = "";
                                        if($c->estado == "otro" || $c->estado == "cerrada"){
                                            $class_color_icon = "grey";
                                            $class_item = "";
                                            $class_color_item = "grey lighten-4";
                                            if($c->estado == "otro"){
                                                $hitorial = $c->ultimoHistorial();
                                                if($hitorial){
                                                    $razon_estado = "(".$hitorial->razon_estado.")";
                                                }
                                            }
                                        }

                                        $usuario_relacion = $c->relacionUsuarioActual();
                                        if($usuario_relacion){
                                            $class_item .= " asignado";
                                        }
                                        $permitir_opciones = false;
                                        if(!$c->permitirAccion() && \Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","caja","configuracion"))
                                            $permitir_opciones = true;
                                        $opciones = "";
                                        if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","caja","configuracion"))
                                            $opciones = "<a class='dropdown-button right' href='#' data-activates='dropdown_opciones_caja_$c->id'><i class='fa fa-ellipsis-v grey-text'></i></a>";
                                    ?>
                                    <li data-caja="{{$c->id}}" class="{{$class_item." ".$class_color_item}} caja">
                                        <div>
                                            <i class="avatar fa fa-laptop circle {{$class_color_icon}}"></i>
                                            <span class="font-small">{{$c->nombre}} <i> {{$razon_estado}}</i></span>
                                            {!! $opciones !!}
                                        </div>

                                        @if($usuario_relacion)
                                            <span class="usuario hide" data-usuario="{{$usuario_relacion->id}}">
                                                <i class="avatar fa fa-user circle green"></i>
                                                <span class="font-small">{{$usuario_relacion->nombres." ".$usuario_relacion->apellidos}}</span>
                                            </span>
                                        @endif
                                    </li>
                                    @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","caja","configuracion"))
                                        <ul id='dropdown_opciones_caja_{{$c->id}}' class='dropdown-content'>
                                            @if($c->estado == "otro")
                                                <li onclick="abrir({{$c->id}})"><a href="#!">Abrir</a></li>
                                                <li onclick="cerrar({{$c->id}})"><a href="#!">Cerrar</a></li>
                                            @else
                                                @if($c->estado == "cerrada")
                                                    <li onclick="abrir({{$c->id}})"><a href="#!">Abrir</a></li>
                                                @else
                                                    <li onclick="cerrar({{$c->id}})"><a href="#!">Cerrar</a></li>
                                                @endif
                                                @if($c->estado == "abierta" && $c->relacionUsuarioActual())
                                                    <li><a href="#modal-realizar-transaccion" onclick="caja_select = {{$c->id}}" class="modal-trigger">Envío a caja maestra</a></li>
                                                @endif
                                            @endif
                                        </ul>
                                    @endif
                                @endforeach
                            </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","caja","configuracion"))
        <div id="modal-realizar-transaccion" class="modal modal-fixed-footer modal-small" style="min-height: 70%;">
            <div class="modal-content">
                <p class="titulo-modal">Enviar a caja maestra</p>
                @include('templates.mensajes',["id_contenedor"=>"realizar-transaccion"])
                {!! Form::open(["id"=>"form-realizar-transaccion","class"=>"col s12"]) !!}
                    <div class="input-field">
                        {!! Form::text("valor",null,["id"=>"valor","class"=>"num-entero"]) !!}
                        {!! Form::label("valor","Valor",["class"=>"active"]) !!}
                    </div>
                    <!--<div class="input-field">
                        {!! Form::select("tipo",[""=>"Seleccione el tipo de transacción","Retiro"=>"Retiro","Deposito"=>"Deposito","Envio a caja maestra"=>"Envio a caja maestra"],null,["id"=>"tipo","class"=>""]) !!}
                        {!! Form::label("tipo","Tipo de transacción") !!}
                    </div>-->
                    {!! Form::hidden("tipo","Envio a caja maestra") !!}
                    <div class="input-field">
                        {!! Form::textarea("comentario",null,["id"=>"comentario","class"=>"materialize-textarea"]) !!}
                        {!! Form::label("comentario","Comentario",["class"=>"active"]) !!}
                    </div>
                {!! Form::close() !!}
            </div>

            <div class="modal-footer ">
                <div class="col s12 right-align">
                    <a href="#!" style="float: none;" class="btn-flat waves-effect modal-close">Cancelar</a>
                    <a href="#!" style="float: none;" class="btn-flat waves-effect green-text" onclick="realizarTransaccion();">Guardar</a>
                </div>
            </div>
        </div>

        <div id="modal-cerrar-caja" class="modal modal-fixed-footer modal-small" style="min-height: 80%;">
            <div class="modal-content">
                <p class="titulo-modal">Cerrar caja</p>
                @include('templates.mensajes',["id_contenedor"=>"cerrar-caja"])
                <div id="contenido-cerrar-caja" class="row"></div>
            </div>

            <div class="modal-footer ">
                <div class="col s12 right-align">
                    <a href="#!" style="float: none;" class="btn-flat waves-effect modal-close">Cancelar</a>
                    <a href="#!" style="float: none;" class="btn-flat waves-effect red-text" onclick="cerrar_send();">Cerrar caja</a>
                </div>
            </div>
        </div>
    @endif
    <div id="modal-asignar" class="modal modal-fixed-footer modal-small">
        <div class="modal-content">
            <p class="titulo-modal">Ingresar</p>
            <p>Ingrese el valor inicial de la caja</p>
            <div class="input-field">
                {!! Form::text("valor",0,["id"=>"valor","class"=>"num-entero"]) !!}
                {!! Form::label("valor","Valor",["class"=>"active"]) !!}
            </div>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-asignar">
                <a href="#!" class="red-text btn-flat" onclick="javascript: sendAsignacion()">Aceptar</a>
                <a href="#!" class="cyan-text btn-flat" onclick="window.location.reload()">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-asignar">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>
@endsection

