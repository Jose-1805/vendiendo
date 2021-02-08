<?php
if(!isset($cliente))
    $cliente = new \App\Models\Cliente();
?>
{!! Form::model($cliente,["id"=>"form-cliente","class"=>"row"]) !!}
{!! Form::hidden("id_cliente",$cliente->id) !!}
<div class="input-field col s12">
    {!! Form::text("nombre",null,["id"=>"nombre"]) !!}
    {!! Form::label("nombre","Nombre",["class"=>"active"]) !!}
</div>

<div class="input-field col s12 m6">
    {!! Form::select("tipo_identificacion",[""=>"Seleccione","C.C"=>"C.C","NIT"=>"NIT","T.I"=>"T.I"],null,["id"=>"tipo_identificacion"]) !!}
    {!! Form::label("tipo_identificacion","Tipo de identificación",["class"=>"active","style"=>"top:10px !important;"]) !!}
</div>

<div class="input-field col s12 m6">
    {!! Form::text("identificacion",null,["id"=>"identificacion"]) !!}
    {!! Form::label("identificacion","Identificación",["class"=>"active"]) !!}
</div>

<div class="input-field col s12 m6">
    {!! Form::text("telefono",null,["id"=>"telefono","class"=>"num-tel","maxlength"=>"10"]) !!}
    {!! Form::label("telefono","Teléfono",["class"=>"active"]) !!}
</div>

<div class="input-field col s12 m6">
    {!! Form::text("correo",null,["id"=>"correo"]) !!}
    {!! Form::label("correo","Correo",["class"=>"active"]) !!}
</div>

<div class="input-field col s12">
    {!! Form::text("direccion",null,["id"=>"direccion"]) !!}
    {!! Form::label("direccion","Dirección",["class"=>"active"]) !!}
</div>
{!! Form::close() !!}