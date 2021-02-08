<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
<?php
    use App\Models\Tarea;
    $user = \Illuminate\Support\Facades\Auth::user();
    $usuarios_aux = \App\User::permitidos()->get();
    $usuarios = [];
    foreach ($usuarios_aux as $aux_u){
        if(!$aux_u->permitirModulo($modulo->nombre,$modulo->seccion))
            $usuarios[] = $aux_u;
    }
?>
@section('contenido')
<div class="col s12 {{$size_medium}} white padding-top-20 padding-bottom-20" style="margin-top: 85px;">
    <p class="titulo">Agregar permisos</p>
    @include("templates.mensajes",["id_contenedor"=>"agregar-relacion-modulo"])
    <?php $class_m = "m6"; ?>
    @if($user->perfil->nombre != "superadministrador")
        <?php $class_m = "m12"; ?>
    @endif
    <div class="col s12 {{$class_m}}"><p><strong>Módulo: </strong><br>{{$modulo->seccion." - ".$modulo->nombre}}</p></div>
    {!!Form::open(["id"=>"form-modulo-relacion"])!!}
        <div class="col s12 m6">
            @if($user->perfil->nombre == "superadministrador")
                <p><strong>Caducidad para administradores: </strong></p>
                <?php $dato = "administradores"; ?>
            @else
                 <p><strong>Caducidad para usuarios: </strong></p>
                <?php $dato = "usuarios"; ?>
            @endif
                <input type="date" class="datepicker" value="{{date('Y-m-d')}}" id="caducidad_usuario" name="caducidad_usuario" placeholder="Caducidad {{$dato}}">
        </div>

        @if($user->perfil->nombre != "superadministrador")
            <div class="col s12 m6">
                <?php
                $fecha = $modulo->fechaCaducidad($user->id);
                if(!$fecha) $fecha = "";
                else $fecha = date("Y-m-d",strtotime($fecha));
                ?>
                <p>El valor máximo para la fecha de caducidad del modulo, en relación con los usuarios, es <strong>{{$fecha}}</strong>.</p>
                <a href="#!" class="btn blue-grey darken-2 waves-effect waves-light right" onclick="copiarFecha('{{$fecha}}')">Copiar fecha</a>
            </div>
        @endif
        {!! Form::hidden("modulo",$modulo->id) !!}
    <div class="divider col s12 margin-bottom-50 white"></div>
        <div class="col s12 content-table-slide">
        <table class="table bordered highlight centered" style="min-width: 400px;">
            <thead>
                <th>Tareas/Funciones</th>
                @if($modulo->funciones->count())
                    @foreach($modulo->funciones as $f)
                        @if($user->perfil->nombre == "superadministrador" || $user->permitirFuncion($f->nombre,$modulo->nombre,$modulo->seccion))
                            <th>{{$f->nombre}}</th>
                        @endif
                    @endforeach
                @else
                    <th>No existen funciones relacionadas con este modulo</th>
                @endif
            </thead>

            <tbody>
                @if($modulo->funciones->count())
                    <tr>
                        <td></td>
                        @foreach($modulo->funciones as $f)
                            <td>
                            @if($user->perfil->nombre == "superadministrador" || $user->permitirFuncion($f->nombre,$modulo->nombre,$modulo->seccion))
                                <div class="switch">
                                    <label>
                                        Off
                                        <input type="checkbox" name="funcion_{{$f->id}}" id="funcion_{{$f->id}}" class="check-parent">
                                        <span class="lever"></span>
                                        On
                                    </label>
                                </div>
                                {{--<input type="date" class="datepicker center-align funcion_{{$f->id}}" id="hasta_{{$f->id}}" name="hasta_{{$f->id}}" placeholder="Seleccione la fecha de caducidad">--}}
                                    <input type="date" class="datepicker center-align" name="hasta_{{$f->id}}" value="{{date('Y-m-d')}}"  placeholder="Seleccione la fecha de caducidad">
                            @endif
                            </td>
                        @endforeach
                    </tr>

                    @foreach($modulo->allTareas() as $t)
                        <?php $cont = 0; ?>
                        @foreach($modulo->funciones as $f)
                            @if($f->hasTarea($t->id) && ($user->permitirTarea($t->nombre,$f->nombre,$modulo->nombre,$modulo->seccion)||$user->perfil->nombre == "superadministrador"))
                                <?php $cont++; ?>
                            @endif
                        @endforeach
                        @if($cont > 0)
                            <tr>
                                <td>{{$t->nombre}}</td>
                                @foreach($modulo->funciones as $f)
                                    <td>
                                    @if($f->hasTarea($t->id) && ($user->permitirTarea($t->nombre,$f->nombre,$modulo->nombre,$modulo->seccion)||$user->perfil->nombre == "superadministrador"))
                                         <div class="switch">
                                             <label>
                                                 Off
                                                 <input type="checkbox" name="tarea_{{$t->id.'_'.$f->id}}" class="funcion_{{$f->id}}" disabled="disabled">
                                                 <span class="lever"></span>
                                                 On
                                             </label>
                                         </div>
                                    @endif
                                    </td>
                                @endforeach

                            </tr>
                        @endif
                    @endforeach
                @endif
            </tbody>
        </table>
        </div>

        <div class="col s12 margin-top-50 content-table-slide">
            <p class="titulo-modal">Seleccione los usuarios</p>
            @if(count($usuarios))
                <table class="table table-bordered centered col 12 m10 offset-m1" style="min-width: 400px;">
                    <thead>
                        <th></th>
                        <th>Nombre</th>
                        <th>Correo</th>
                    </thead>

                    <tbody>
                        @foreach($usuarios as $u)
                            <tr>
                                <td>
                                    <p class="center-align">
                                        <input type="checkbox" class="filled-in" id="usuario_{{$u->id}}" name="usuario_{{$u->id}}"/>
                                        <label for="usuario_{{$u->id}}"></label>
                                    </p>
                                </td>
                                <td>{{$u->nombres." ".$u->apellidos}}</td>
                                <td>{{$u->email}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="progress hide" id="progress-agregar-relacion-modulo">
                    <div class="indeterminate"></div>
                </div>
                <div class="col s12 right-align padding-top-20" id="contenedor-botones-agregar-relacion-modulo">
                    <a class="btn blue-grey darken-2 waves-effect waves-light" id="btn-agregar-relacion-modulo">Guardar</a>
                </div>
            @else
                <p class="center-align">No existen usuarios disponibles para relacionar con este modulo.</p>
            @endif
        </div>
    {!!Form::close()!!}
</div>
@stop

@section('js')
    @parent
    <script src="{{asset("js/moduloAction.js")}}"></script>
@stop