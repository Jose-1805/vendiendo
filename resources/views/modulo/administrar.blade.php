<?php
    $user = \Illuminate\Support\Facades\Auth::user();
    $relacion = \App\User::permitidos()->select("usuarios.*")->join("usuarios_modulos","usuarios.id","=","usuarios_modulos.usuario_id")
        ->join("modulos","usuarios_modulos.modulo_id","=","modulos.id");

    if($user->perfil->nombre != 'superadministrador')
        $relacion = $relacion->join("perfiles","usuarios.perfil_id","=","perfiles.id");

    $relacion = $relacion->where("modulos.id",$modulo->id);


    /*if($user->perfil->nombre == "administrador"){
        $relacion = $relacion->where("usuario_creador_id", $user->id);
    }else{
        $relacion = $relacion->where("perfiles.nombre","administrador");
    }*/

    $usuarios = $relacion->get();
?>
<div class="modal-content">
    <div class="col s12">
        <p class="titulo">Administrar ({{$modulo->seccion." - ".$modulo->nombre}})</p>
        @include("templates.mensajes",["id_contenedor"=>"relacion-modulo"])
        @if($user->permitidos()->get()->count())
        <div id="" class="col s12">
            <table class="table bordered highlight responsive-table centered">
                <thead>
                    <th>Nombre</th>
                    <th>Perfil</th>
                    <th>Funciones/Tareas</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                </thead>

                <tbody>
                    @if(count($usuarios))
                        @foreach($usuarios as $us)
                        <tr>
                            <td>{{$us->nombres." ".$us->apellidos}}</td>
                            <td>{{$us->perfil->nombre}}</td>
                            <td>
                                @foreach($us->funciones()->where("modulo_id",$modulo->id)->get() as $f)
                                    <p style="margin: 0px !important;">{{$f->nombre}}/
                                        <?php $aux = 1;?>
                                        @foreach($us->tareasFuncion($f->id) as $t)
                                            @if($aux > 1),@endif
                                            {{$t->nombre}}
                                        @endforeach
                                    </p>
                                @endforeach
                            </td>
                            <td><a href="{{url('/modulo/editar-permisos/'.$modulo->id.'/'.$us->id)}}" class=""><i class="fa fa-pencil-square-o fa-2x" style="cursor: pointer;"></i></a></td>
                            <td><a id="btn-eliminar-relacion-modulo" href="#modal-eliminar-relacion-modulo" class="modal-trigger" onclick="javascript: id_usuario = {{$us->id}};id_modulo = {{$modulo->id}}"><i class="fa fa-trash fa-2x" style="cursor: pointer;"></i></a></td>
                        </tr>
                        @endforeach
                    @else
                        <td colspan="6"><p class="center">No existen usuarios relacionados con este modulo</p></td>
                    @endif
                </tbody>
            </table>
        </div>
        @else
            <p class="center">Antes de administrar las funciones de los modulos debe agregar usuarios</p>
        @endif
    </div>
</div>
<div class="modal-footer">
    <a href="{{url('/modulo/agregar-permisos/'.$modulo->id)}}" class="waves-effect waves-darken btn-flat">Agregar</a>
    <a href="#!" class="waves-effect waves-darken btn-flat" onclick="$('#modal-admin-modulo').closeModal();">Cancelar</a>
</div>

<div id="modal-eliminar-relacion-modulo" class="modal modal-fixed-footer modal-small">
    <div class="modal-content">
        <p class="titulo-modal">Eliminar</p>
        <p>¿Está seguro de eliminar la relación entre el usuario y el modulo?</p>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-eliminar-relacion-modulo">
            <a href="#!" class="red-text btn-flat" onclick="javascript: eliminar()">Aceptar</a>
            <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-eliminar-relacion-modulo">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>

@section('js')
    @parent
    <script src="{{asset('js/moduloAction.js')}}"></script>
@stop