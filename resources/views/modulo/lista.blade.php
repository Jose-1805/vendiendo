@if(count($modulos))
<ul class="collection">
    @foreach($modulos as $modulo)
        <!--<a data-id="{{$modulo->id}}" class="trigger-modal-admin-modulo modal-trigger" href="#modal-admin-modulo">
            <div class="collapsible-header">
                <i class="{{$modulo->icon_class}} grey lighten-2 blue-grey-text text-darken-1" style="display: inline !important;"></i>
                {{$modulo->seccion." - ".$modulo->nombre}}
            </div>
        </a>-->
        @if($modulo->nombre != "planes" &&
            (
                (\Illuminate\Support\Facades\Auth::user()->bodegas == 'no')
                || (\Illuminate\Support\Facades\Auth::user()->bodegas == 'si' && $modulo->asignable_ab == 'si')
            )
        )
            @if(($modulo->nombre == "módulos" && \Illuminate\Support\Facades\Auth::user()->perfil->nombre == "superadministrador")
             || ($modulo->nombre == "módulos" && \Illuminate\Support\Facades\Auth::user()->perfil->nombre == "administrador" && \Illuminate\Support\Facades\Auth::user()->bodegas == "si" && \Illuminate\Support\Facades\Auth::user()->admin_bodegas == "si")
             || $modulo->nombre != "módulos")
            <li class="collection-item avatar trigger-modal-admin-modulo modal-trigger" href="#modal-admin-modulo" data-id="{{$modulo->id}}">
                <i class="circle {{$modulo->icon_class}} grey lighten-2 blue-grey-text text-darken-1"></i>
                <?php
                    $seccion = $modulo->seccion;
                    $nombre = ucwords($modulo->nombre);
                    if($seccion == "configuracion")$seccion = "Configuración";
                    $seccion = ucwords($seccion);

                    if($nombre == "Facturacion")$nombre = "Facturación";
                ?>
                <span class="title margin-left-50">{{$seccion." - ".$nombre}}</span>
                <p class="margin-left-50">Funciones: {{$modulo->funciones->count()}}</p>
            </li>
            @endif
        @endif
    @endforeach

    @if(Auth::user()->permiso_reportes == 'si' && Auth::user()->admin_bodegas == 'si')
        <li class="collection-item avatar modal-trigger" href="#modal-reportes-habilitados">
            <i class="circle fa fa-eye grey lighten-2 blue-grey-text text-darken-1"></i>
            <span class="title margin-left-50">Reportes</span>
            <p class="margin-left-50">Reportes visibles para los usuarios</p>
        </li>
    @endif

    @if(
        (Auth::user()->permiso_reportes == 'si' && Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
         || (Auth::user()->permiso_reportes == 'si' && Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
         || Auth::user()->perfil->nombre == 'superadministrador')
        <li class="collection-item avatar modal-trigger" href="#modal-permiso-reportes">
            <i class="circle fa fa-list-alt grey lighten-2 blue-grey-text text-darken-1"></i>
            <span class="title margin-left-50">Reportes</span>
            <p class="margin-left-50">Usuarios que pueden ver reportes</p>
        </li>
    @endif


</ul>
@else
    <p class="col s12 center">No existen modulos relacionados con su cuenta de usuario</p>
@endif

<div id="modal-admin-modulo" class="modal modal-fixed-footer" style="height: auto !important;">
    <!--<div class="modal-content">
        <h4>Modal Header</h4>
        <p>A bunch of text</p>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat ">Agree</a>
    </div>-->
    <div id="contenido"></div>
    <div class="col s12 hide" id="load-modal-admin-modulo">
        <p class="center">Un momento, la información se esta cargando...</p>
        <div class="progress">
            <div class="indeterminate"></div>
        </div>
    </div>
</div>

@if(Auth::user()->permiso_reportes == 'si' || Auth::user()->perfil->nombre == 'superadministrador')
    <div id="modal-permiso-reportes" class="modal modal-fixed-footer modal-small" style="min-height: 50%;" >
        <div class="modal-content">
            <p class="titulo-modal">Permiso módulo de reportes</p>
            {!! Form::open(['id'=>'form-permiso-reportes']) !!}
            @forelse(\App\User::permitidos()->get() as $u)
                <?php
                    $checked = $u->permiso_reportes == 'si'?'checked="checked"':'';
                ?>
                <p>
                    <input type="checkbox" id="usuario_{{$u->id}}" name="permiso_reportes[]" value="{{$u->id}}" {{$checked}}/>
                    <label for="usuario_{{$u->id}}">{{$u->nombres.' '.$u->apellidos}}</label>
                </p>
            @empty
                <p class="text-info">No existen usuarios permitidos para la gestión del módulo</p>
            @endforelse
            {!! Form::close() !!}
        </div>
        <div class="modal-footer">
            <a href="#!" class="modal-action btn-flat " id="btn-guardar-permiso-reportes">Guardar</a>
            <a href="#!" class="modal-action btn-flat modal-close">Cerrar</a>
        </div>
    </div>
@endif

@if(Auth::user()->permiso_reportes == 'si' && Auth::user()->admin_bodegas == 'si')
    <div id="modal-reportes-habilitados" class="modal modal-fixed-footer modal-small" style="min-height: 80%;" >
        <div class="modal-content">
            <p class="titulo-modal">Reportes habilitados para los usuarios</p>
            {!! Form::open(['id'=>'form-reportes-habilitados']) !!}
            @forelse($reportesPermitidos as $r)
                <?php
                    $checked = Auth::user()->reporteHabilitado($r->reporte_id)?'checked="checked"':'';
                ?>
                <p>
                    <input type="checkbox" id="reporte_{{$r->reporte_id}}" name="reportes_habilitados[]" value="{{$r->reporte_id}}" {{$checked}}/>
                    <label for="reporte_{{$r->reporte_id}}">{{$r->nombre}}</label>
                </p>
            @empty
                <p class="text-info">No existen reportes permitidos para su usuario</p>
            @endforelse
            {!! Form::close() !!}
        </div>
        <div class="modal-footer">
            <a href="#!" class="modal-action btn-flat " id="btn-guardar-reportes-habilitados">Guardar</a>
            <a href="#!" class="modal-action btn-flat modal-close">Cerrar</a>
        </div>
    </div>
@endif