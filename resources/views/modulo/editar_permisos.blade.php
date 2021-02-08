<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
<?php
    use App\Models\Tarea;
    $user = \Illuminate\Support\Facades\Auth::user();
?>
@section('contenido')
<div class="col s12 {{$size_medium}} white padding-top-20 padding-bottom-20" style="margin-top: 85px;">
    <p class="titulo">Editar permisos</p>
    @include("templates.mensajes",["id_contenedor"=>"edicion-relacion-modulo"])
    <div class="col s12 m6"><p><strong>Módulo: </strong><br>{{$modulo->seccion." - ".$modulo->nombre}}</p></div>
    <div class="col s12 m6"><p><strong>Usuario: </strong><br>{{$usuario->nombres." ".$usuario->apellidos." (".$usuario->perfil->nombre.")"}}</p></div>
    {!!Form::open(["id"=>"form-modulo-relacion"])!!}
        @if($user->perfil->nombre == "superadministrador")

        <div class="col s12 m6">
            <p><strong>Caducidad para administrador: </strong></p>
            <?php
            $fecha = $modulo->fechaCaducidad($usuario->id);
            if(!$fecha) $fecha = "";
                else $fecha = date("Y-m-d",strtotime($fecha));
            ?>
            <input type="date" class="datepicker" value="{{$fecha}}" id="caducidad_usuario" name="caducidad_usuario" placeholder="Caducidad administrador">
        </div>
        @else
            <!--<p><strong>Caducidad para administrador: </strong></p>-->
            <div class="col s12 m6">
                <p><strong>Caducidad para usuario: </strong></p>
                <?php
                $fecha = $modulo->fechaCaducidad($usuario->id);
                if(!$fecha) $fecha = "";
                    else $fecha = date("Y-m-d",strtotime($fecha));
                ?>
                <input type="date" class="datepicker" value="{{$fecha}}" id="caducidad_usuario" name="caducidad_usuario" placeholder="Caducidad usuario">
            </div>

            <div class="col s12 m6">
                <?php
                    $fecha = $modulo->fechaCaducidad($user->id);
                    if(!$fecha) $fecha = "";
                    else $fecha = date("Y-m-d",strtotime($fecha));
                ?>
                <p>El valor máximo para la fecha de caducidad del modulo, en relación con el usuario {{$usuario->nombres." ".$usuario->apellidos}}, es <strong>{{$fecha}}</strong>.</p>
                    <a href="#!" class="btn blue-grey darken-2 waves-effect waves-light right" onclick="copiarFecha('{{$fecha}}')">Copiar fecha</a>
            </div>
        @endif

        {!! Form::hidden("modulo",$modulo->id) !!}
        {!! Form::hidden("usuario",$usuario->id) !!}
    <div class="divider col s12 margin-bottom-50 white"></div>
        <table class="table bordered highlight responsive-table centered">
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
                                    <?php
                                        $relacion = \App\Models\UsuarioFuncion::where("usuario_id",$usuario->id)->where("funcion_id",$f->id)->first();
                                        $hasta = "";
                                        if($relacion)
                                            $hasta = date("Y-m-d",strtotime($relacion->hasta));
                                    ?>
                                    <label>
                                        Off
                                        @if($usuario->permitirFuncion($f->nombre,$modulo->nombre,$modulo->seccion))
                                            <input type="checkbox" name="funcion_{{$f->id}}" id="funcion_{{$f->id}}" class="check-parent" checked>
                                        @else
                                            <input type="checkbox" name="funcion_{{$f->id}}" id="funcion_{{$f->id}}" class="check-parent">
                                        @endif
                                        <span class="lever"></span>
                                        On
                                    </label>
                                </div>
                                <input type="date" class="datepicker center-align funcion_{{$f->id}}" value="{{$hasta}}" id="hasta_{{$f->id}}" name="hasta_{{$f->id}}" placeholder="Caducar">
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
                                                     @if($usuario->permitirTarea($t->nombre,$f->nombre,$modulo->nombre,$modulo->seccion))
                                                         <input type="checkbox" name="tarea_{{$t->id.'_'.$f->id}}" class="funcion_{{$f->id}}" checked>
                                                     @else
                                                         <input type="checkbox" name="tarea_{{$t->id.'_'.$f->id}}" class="funcion_{{$f->id}}">
                                                     @endif
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
    {!!Form::close()!!}
    <div class="progress hide" id="progress-edicion-relacion-modulo">
        <div class="indeterminate"></div>
    </div>
    <div class="col s12 right-align padding-top-20" id="contenedor-botones-edicion-relacion-modulo">
        <a class="btn blue-grey darken-2 waves-effect waves-light" id="btn-editar-relacion-modulo">Guardar</a>
    </div>
</div>
@stop

@section('js')
    @parent
    <script src="{{asset("js/moduloAction.js")}}"></script>
@stop