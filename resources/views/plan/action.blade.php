<?php
if(!isset($accion))
    $accion = "Crear";

if($accion == "Crear"){
    $url = url('/plan/store');
}else{
    $url = url('/plan/update');
}

if(!isset($plan))
    $plan = new \App\Models\Plan();
    $modulos = \App\Models\Modulo::where('estado', 'activo')->orderBy("seccion")->orderBy("nombre")->get();
?>

<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('css')
    @parent
    <link rel="stylesheet" href="{{ asset('css/planes.css') }}">
@endsection

@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px;">
        <p class="titulo-modal">{{$accion}} plan</p>
        @include("templates.mensajes",["id_contenedor"=>"accion-plan"])
        {!! Form::model($plan,["id"=>"form-plan","url"=>$url]) !!}
            {!! Form::hidden("plan",$plan->id) !!}
            <div class="col s12 m5 l4">
                <p class="titulo-modal">Lista de módulos</p>
                @if(count(\App\Models\Modulo::all()))
                    <p >
                        <input type="checkbox" id="check-seleccionar-todo"/>
                        <label class="truncate" for="check-seleccionar-todo">Seleccionar todo</label>
                    </p>
                @endif
                <div class="collapsible" id="contenedor-lista-check-modulos" data-collapsible="accordion">

                <?php
                    $aux = 0;
                    $nombre = "";
                    $aux_nombre = "";
                    $primero = false;
                ?>
                @foreach($modulos as $m)
                    @if($m->nombre != "planes")
                        <?php $aux_nombre = $nombre; ?>
                        @if($nombre != $m->seccion)
                            <?php $nombre =  $m->seccion;$primero = true; ?>
                            <li>
                                <div class="collapsible-header"><i class="material-icons">filter_drama</i>{{$m->seccion}}</div>
                                <div class="collapsible-body">
                        @else
                            <?php $primero = false; ?>
                        @endif
                         <div class="contenedor-item-lista">
                    <p class="contenedor-check-nivel-1 contenedor-check">
                        @if(count($m->funciones))
                            <i class="fa fa-angle-right controlador-lista-nivel-1 controlador-lista"></i>
                        @else
                            <i class="fa fa-circle controlador-lista-disabled"></i>
                        @endif
                        @if($plan->exists && $plan->hasModulo($m->id))
                            <input type="checkbox" id="mod_{{$m->id}}" class="check check_level_1" name="modulos[]" value="{{$m->id}}" checked="checked"/>
                        @else
                            <input type="checkbox" id="mod_{{$m->id}}" class="check check_level_1" name="modulos[]" value="{{$m->id}}"/>
                        @endif
                        <label class="truncate" for="mod_{{$m->id}}">{{$m->nombre}}</label>
                    </p>
                    @if(count($m->funciones))
                        <div class="contenedor-lista contenedor-lista-nivel-2 hide">
                            @foreach($m->funciones as $f)
                                <div class="contenedor-item-lista">
                                    <p class="contenedor-check-nivel-2 contenedor-check">
                                        @if(count($f->tareas))
                                            <i class="fa fa-angle-right controlador-lista-nivel-2 controlador-lista"></i>
                                        @else
                                            <i class="fa fa-circle controlador-lista-disabled"></i>
                                        @endif

                                        @if($plan->exists && $plan->hasFuncion($f->id))
                                            <input type="checkbox" id="fun_{{$f->id}}" class="check check_level_2" name="funciones_{{$m->id}}[]" value="{{$f->id}}" checked="checked"/>
                                        @else
                                            <input type="checkbox" id="fun_{{$f->id}}" class="check check_level_2" name="funciones_{{$m->id}}[]" value="{{$f->id}}"/>
                                        @endif
                                        <label class="truncate" for="fun_{{$f->id}}">{{$f->nombre}}</label>
                                    </p>
                                    @if(count($f->tareas))
                                        <div class="contenedor-lista contenedor-lista-nivel-3 hide">
                                            @foreach($f->tareas as $t)
                                                <div class="contenedor-item-lista">
                                                    <p class="contenedor-check-nivel-3 contenedor-check">
                                                        <!--<i class="fa fa-angle-right controlador-lista-nivel-3 controlador-lista"></i>-->
                                                        <i class="fa fa-circle controlador-lista-disabled"></i>
                                                        @if($plan->exists && $plan->hasTarea($f->id,$t->id))
                                                            <input type="checkbox" id="mod_{{$t->id.'_'.$f->id}}" class="check check_level_3" name="tareas_{{$f->id}}[]" value="{{$t->id}}" checked="checked"/>
                                                        @else
                                                            <input type="checkbox" id="mod_{{$t->id.'_'.$f->id}}" class="check check_level_3" name="tareas_{{$f->id}}[]" value="{{$t->id}}"/>
                                                        @endif
                                                        <label class="truncate" for="mod_{{$t->id.'_'.$f->id}}">{{$t->nombre}}</label>
                                                    </p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                    </div>

                        @if($nombre != $aux_nombre && !$primero)
                            </li>
                        @endif

                    @endif
                @endforeach
                    @if(count($modulos))
                        </li>
                    @endif
                </div>
            </div>

            <div class="col s12 m7 l8">
                <p class="titulo-modal">Parámetros del plan</p>
                <div class="col s12 input-field">
                    {!! Form::text("nombre",null,["id"=>"nombre"]) !!}
                    {!! Form::label("nombre","Nombre",["class"=>"truncate"]) !!}
                </div>
                <div class="col s12 m6 input-field">
                    {!! Form::text("valor",null,["id"=>"valor"]) !!}
                    {!! Form::label("valor","Valor [pesos]",["class"=>"truncate"]) !!}
                </div>
                <div class="col s12 m6 input-field">
                    {!! Form::select("duracion",["1"=>"1","3"=>"3","6"=>"6","9"=>"9","12"=>"12"],null,["id"=>"duracion"]) !!}
                    {!! Form::label("duracion","Duración (meses)",["class"=>"truncate"]) !!}
                </div>
                <div class="col s12 m6 input-field">
                    {!! Form::text("n_usuarios",null,["id"=>"n_usuarios"]) !!}
                    {!! Form::label("n_usuarios","Número de usuarios",["class"=>"truncate"]) !!}
                </div>
                <div class="col s12 m6 input-field">
                    {!! Form::text("n_compras",null,["id"=>"n_compras"]) !!}
                    {!! Form::label("n_compras","Número de compras",["class"=>"truncate"]) !!}
                </div>
                <div class="col s12 m6 input-field">
                    {!! Form::text("n_facturas",null,["id"=>"n_facturas"]) !!}
                    {!! Form::label("n_facturas","Número de facturas",["class"=>"truncate"]) !!}
                </div>
                <div class="col s12 m6 input-field">
                    {!! Form::text("n_almacenes",null,["id"=>"n_almacenes"]) !!}
                    {!! Form::label("n_almacenes","Número de almacenes",["class"=>"truncate"]) !!}
                </div>
                <div class="col s12 m6 input-field">
                    {!! Form::text("n_bodegas",null,["id"=>"n_bodegas"]) !!}
                    {!! Form::label("n_bodegas","Número de bodegas",["class"=>"truncate"]) !!}
                </div>
                <div class="col s12 m6 input-field">
                    {!! Form::text("n_proveedores",null,["id"=>"n_proveedores"]) !!}
                    {!! Form::label("n_proveedores","Número de proveedores",["class"=>"truncate"]) !!}
                </div>
                <div class="col s12 m6 input-field">
                    {!! Form::text("n_clientes",null,["id"=>"n_clientes"]) !!}
                    {!! Form::label("n_clientes","Número de clientes",["class"=>"truncate"]) !!}
                </div>
                <div class="col s12 m6 input-field">
                    {!! Form::text("n_productos",null,["id"=>"n_productos"]) !!}
                    {!! Form::label("n_productos","Número de productos",["class"=>"truncate"]) !!}
                </div>
                <div class="col s12 m6 input-field">
                    {!! Form::text("n_promociones_sms",null,["id"=>"n_promociones_sms"]) !!}
                    {!! Form::label("n_promociones_sms","Número de promociones sms",["class"=>"truncate"]) !!}
                </div>
                <div class="col s12" style=""></div>
                <div class="col s12 l6 input-field">
                    <p class="contenedor-check">
                        @if($plan->exists && $plan->puntos == "si")
                            <input type="checkbox" id="puntos" name="puntos" value="si" checked="checked"/>
                        @else
                            <input type="checkbox" id="puntos" name="puntos" value="si"/>
                        @endif
                        <label class="truncate" for="puntos">Puntos</label>
                    </p>
                </div>
                <div class="col s12 l6 input-field ">
                    <p class="contenedor-check">
                        @if($plan->exists && $plan->cliente_predeterminado == "si")
                            <input type="checkbox" id="cliente_predeterminado" name="cliente_predeterminado" value="si" checked="checked"/>
                        @else
                            <input type="checkbox" id="cliente_predeterminado" name="cliente_predeterminado" value="si"/>
                        @endif
                        <label class="truncate" for="cliente_predeterminado">Cliente predeterminado</label>
                    </p>
                </div>
                <div class="col s12 l6 input-field">
                    <p class="contenedor-check">
                        @if($plan->exists && $plan->notificaciones == "si")
                            <input type="checkbox" id="notificaciones" name="notificaciones" value="si" checked="checked"/>
                        @else
                            <input type="checkbox" id="notificaciones" name="notificaciones" value="si"/>
                        @endif
                        <label class="truncate" for="notificaciones">Notificaciones mínimos/pagos</label>
                    </p>
                </div>
                <div class="col s12 l6 input-field">
                    <p class="contenedor-check">
                        @if($plan->exists && $plan->objetivos_ventas == "si")
                            <input type="checkbox" id="objetivos_ventas" name="objetivos_ventas" value="si" checked="checked"/>
                        @else
                            <input type="checkbox" id="objetivos_ventas" name="objetivos_ventas" value="si"/>
                        @endif
                        <label class="truncate" for="objetivos_ventas">Objetivos de ventas</label>
                    </p>
                </div>
                <div class="col s12 l6 input-field">
                    <p class="contenedor-check">
                        @if($plan->exists && $plan->factura_abierta == "si")
                            <input type="checkbox" id="factura_abierta" name="factura_abierta" value="si" checked="checked"/>
                        @else
                            <input type="checkbox" id="factura_abierta" name="factura_abierta" value="si"/>
                        @endif
                        <label class="truncate" for="factura_abierta">Factura abierta</label>
                    </p>
                </div>
                <div class="col s12 l6 input-field">
                    <p class="contenedor-check">
                        @if($plan->exists && $plan->importacion_productos == "si")
                            <input type="checkbox" id="importacion_productos" name="importacion_productos" value="si" checked="checked"/>
                        @else
                            <input type="checkbox" id="importacion_productos" name="importacion_productos" value="si"/>
                        @endif
                        <label class="truncate" for="importacion_productos">Importación productos</label>
                    </p>
                </div>
                <div class="col s12 l6 input-field">
                    <p class="contenedor-check">
                        @if($plan->exists && $plan->validacion_stock == "si")
                            <input type="checkbox" id="validacion_stock" name="validacion_stock" value="si" checked="checked"/>
                        @else
                            <input type="checkbox" id="validacion_stock" name="validacion_stock" value="si"/>
                        @endif
                        <label class="truncate" for="validacion_stock">Validación de stock</label>
                    </p>
                </div>
            </div>

            <div class="col s12">
                <p class="titulo-modal margin-top-40">Reportes</p>
                @forelse(\App\Models\Reporte::all() as $r)
                    <div class="col s12 m6 l4 input-field">
                        <p class="contenedor-check">
                            @if($plan->exists && $plan->hasReporte($r->id))
                                <input type="checkbox" id="rep_{{$r->id}}" name="reportes[]" value="{{$r->id}}" checked="checked"/>
                            @else
                                <input type="checkbox" id="rep_{{$r->id}}" name="reportes[]" value="{{$r->id}}"/>
                            @endif
                            <label class="truncate" for="rep_{{$r->id}}">{{$r->nombre}}</label>
                        </p>
                    </div>
                @empty
                    <p class="center-align">No existen reportes registrados</p>
                @endforelse

            </div>
        {!! Form::close() !!}
        <div class="col s12 right-align" id="contenedor-botones-plan">
            <a class="btn waves-effect waves-light blue-grey darken-2 margin-top-30" style="cursor: pointer;" onclick="action();">Guardar</a>
        </div>
        <div class="col s12 hide progress margin-top-40" id="progress-plan">
            <div class="indeterminate"></div>
        </div>
    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/planes.js')}}"></script>
@endsection