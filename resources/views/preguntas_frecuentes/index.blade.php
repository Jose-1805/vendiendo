<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>
@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px;">
        @if(\Illuminate\Support\Facades\Auth::check())
            @if(\Illuminate\Support\Facades\Auth::user()->perfil->nombre == "superadministrador")
                <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla modal-trigger" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="#modal-crear-pregunta-frecuente"><i class="fa fa-plus"></i></a>
            @endif
        @endif
            <div class="row titulo" style="margin-top: 10px; margin-bottom: 10px">
                <div style="display:inline;margin-right: 30px;" class="col s12 m3">Preguntas frecuentes</div>
                <div style="display:inline;margin-top: -2px; margin-bottom: -15px;" class="col s12 m8">
                    <select id="select-preguntas" name="select-preguntas" onchange="filtrarPregunta()">
                        <option value="" disabled selected>Seleccione una pregunta</option>
                        <option value="0">Todas las preguntas</option>
                        @foreach($preguntas as $pr)
                            <option value="{{$pr->id}}">{{$pr->pregunta}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @include('templates.mensajes',["id_contenedor"=>"preguntas_frecuentes"])
       <div id="lista-preguntas" class="col s12 center-align">
           @include('preguntas_frecuentes.lista')
       </div>
    </div>
    @if(\Illuminate\Support\Facades\Auth::check())
        @if(\Illuminate\Support\Facades\Auth::user()->perfil->nombre == "superadministrador")
            <div id="modal-crear-pregunta-frecuente" class="modal modal-fixed-footer">
                <div class="modal-content">
                    <p class="titulo-modal">Crear pregunta frecuente</p>
                    @include('templates.mensajes',["id_contenedor"=>"crear-pregunta-frecuente"])
                    @include('preguntas_frecuentes.form')
                </div>

                <div class="modal-footer">
                    <div class="col s12" id="contenedor-botones-crear-pregunta-frecuente">
                        <a href="#!" class="green-text btn-flat" onclick="storePregunta()">Guardar</a>
                        <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
                    </div>

                    <div class="progress hide" id="progress-crear-pregunta-frecuente">
                        <div class="indeterminate cyan"></div>
                    </div>
                </div>
            </div>

            <div id="modal-editar-pregunta-frecuente" class="modal modal-fixed-footer">
                <div class="modal-content">
                    <p class="titulo-modal">Editar pregunta frecuente</p>
                    @include('templates.mensajes',["id_contenedor"=>"editar-pregunta-frecuente"])
                    <div id="load-info-pregunta-frecuente"><p class="center-align">Cargando información <i class="fa fa-spin fa-spinner"></i></p></div>
                    <div id="info-pregunta-frecuente"></div>
                </div>

                <div class="modal-footer">
                    <div class="col s12 hide" id="contenedor-botones-editar-pregunta-frecuente">
                        <a href="#!" class="green-text btn-flat" onclick="updatePregunta()">Guardar</a>
                        <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
                    </div>

                    <div class="progress hide" id="progress-editar-pregunta-frecuente">
                        <div class="indeterminate cyan"></div>
                    </div>
                </div>
            </div>

            <div id="modal-eliminar-pregunta-frecuente" class="modal modal-fixed-footer modal-small">
                <div class="modal-content">
                    <p class="titulo-modal">Eliminar</p>
                    <p>¿Está seguro de eliminar esta pregunta?</p>
                </div>

                <div class="modal-footer">
                    <div class="col s12" id="contenedor-botones-eliminar-pregunta-frecuente">
                        <a href="#!" class="red-text btn-flat" onclick="deletePregunta()">Aceptar</a>
                        <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
                    </div>

                    <div class="progress hide" id="progress-eliminar-pregunta-frecuente">
                        <div class="indeterminate cyan"></div>
                    </div>
                </div>
            </div>
        @endif
    @endif
@endsection
<script src="{{asset('js/preguntasFrecuentes.js')}}"></script>