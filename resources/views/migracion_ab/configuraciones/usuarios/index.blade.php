<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>
@extends("templates.master")

@section('titulo')
    Vendiendo.co - Inicio
@stop

@section('css')
    @parent
@stop

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        <p class="titulo">Configuración de usuarios</p>
        <div id="contenedor-lista-productos" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"usuarios"])
            <div class="col s12 grey lighten-3">
                <p class="font-small blue-text text-accent-2"><strong>Importante! </strong> La informacón que se registre no podrá ser editada nuevamente durante el proceso de configuración, un almacén puede tener únicamente un administrador.</p>
            </div>
            {!! Form::open(['id'=>'form-lista-usuarios']) !!}
            <div id="lista-usuarios" class="content-table-slide col s12">
                <table class="bordered highlight centered no-material-select" id="tabla_usuarios">
                    <thead>
                    <tr>
                        <th >Nombre</th>
                        <th >Perfil</th>
                        <th >Seudónimo</th>
                        <th >Correo</th>
                        <th >Teléfono</th>
                        <th width="200">Almacén</th>
                        <th >Administrador</th>
                    </tr>
                    </thead>
                </table>
            </div>
            {!! Form::close() !!}
            <div class="col s12 padding-top-20 grey lighten-3">
                <div class="col s12 m6 l8 blue-text text-accent-2">
                    <p class="font-small"><strong>Nota: </strong>
                    A continuación encuentra un elemento ayudante para selección rápida de los almacenes, acompañado del botón de acción
                    para guardar la información seleccionada en la tabla de usuarios
                    </p>
                </div>
                <div class="col s12 m3 l2">
                    {!! Form::select('almacenes_global',[''=>'Seleccione un almacén']+\App\Models\Almacen::permitidos()->select('nombre','id')->lists('nombre','id'),null,['id'=>'almacen_global']) !!}
                </div>
                <div class="col s12 m3 l2 padding-top-10">
                    <a class="btn blue-grey darken-2" id="btn-guardar">Guardar</a>
                </div>
            </div>
        </div>

    </div>
@stop

@section('js')
    @parent
    <script src="{{asset('js/migracion_ab/usuarios.js')}}"></script>
@endsection
