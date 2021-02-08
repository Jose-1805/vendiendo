<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla hide" id="undo-categoria" data-position="bottom" data-delay="50" data-tooltip="Ir a Categorias" href="#" style="position: fixed;"><i class="fa fa-arrow-left"></i></a>
        <div id="div-lista-categorias">
            <p class="titulo">Seleccione una categoría</p>
            <div class="row">
                @foreach($categorias as $key => $categoria)
                    <div class="col s12 m3 fade_categoria div-categoria" data-color="{{config('options.colores')[$key]}}" style="cursor: pointer" id="div-categoria-{{$categoria->id}}">
                        <div class="card-panel categoria_card {{config('options.colores')[$key]}} text-center">
                      <span class="white-text" style="font-size: 35px">
                          {{$categoria->nombre}}
                      </span>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="input-field col s12 m6 center-align"></div>
        </div>

        <div id="lista-productos-categoria" class="input-field col s12 center-align hide">

        </div>
    </div>
@endsection
@section('js')
    @parent
    <script>
        $(document).ready(function () {
            $("#nav-breadcrump").addClass('hide');
        })
    </script>
@stop
