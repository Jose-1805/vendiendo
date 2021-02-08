<?php
    if(!isset($almacen))$almacen = new \App\Models\Almacen();
?>
@include("templates.mensajes",["id_contenedor"=>"almacenes-action"])
{!! Form::model($almacen,["id"=>"form-almacen"]) !!}
    {!! Form::hidden("id",null) !!}

    <div class="col s12 m6">
        <div class="input-field col s12">
            {!! Form::label("nombre","Nombre",["class"=>"active"]) !!}
            {!! Form::text("nombre",null,["id"=>"nombre","class"=>""]) !!}
        </div>
        <div class="input-field col s12">
            {!! Form::label("prefijo","Prefijo para factura",["class"=>"active"]) !!}
            {!! Form::text("prefijo",null,["id"=>"prefijo","class"=>""]) !!}
        </div>
        <div class="input-field col s12">
            {!! Form::label("direccion","Dirección",["class"=>"active"]) !!}
            {!! Form::text("direccion",null,["id"=>"direccion","class"=>""]) !!}
        </div>
        <div class="input-field col s12">
            {!! Form::label("telefono","Teléfono",["class"=>"active"]) !!}
            {!! Form::text("telefono",null,["id"=>"telefono","class"=>"num-entero"]) !!}
        </div>
        <div class="input-field col s12">
            {!! Form::label("administrador","Admnistrador",["class"=>"active"]) !!}
            {!! Form::select("administrador",[''=>'Seleccione un administrador']+\App\User::permitidos(false,true)->select(\Illuminate\Support\Facades\DB::raw('CONCAT(nombres," ",apellidos) as full_name'),'usuarios.id')->lists('full_name','id'),null,["id"=>"administrador","class"=>""]) !!}
        </div>
        {!! Form::hidden("longitud",null,["id"=>"longitud","class"=>""]) !!}
        {!! Form::hidden("latitud",null,["id"=>"latitud","class"=>""]) !!}
    </div>


    <div class="col s12 m6" >
        <p class="font-small">Arrastre el marcador hasta la ubicación del almacén</p>
        <div id="mapa" style="width: 100%; max-width: 100%; height: 300px; max-height: 300px;"></div>
    </div>


    <div class="col s12 center" style="margin-top: 20px;">
        <a href="#!" class="btn blue-grey darken-2 waves-effect waves-light" onclick="actionAlmacen()">Guardar</a>
    </div>

{!! Form::close() !!}