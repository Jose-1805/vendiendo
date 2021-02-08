<?php
    if(!isset($bodega))$bodega = new \App\Models\Bodega();
?>
{!! Form::model($bodega,["id"=>"form-bodega"]) !!}
    {!! Form::hidden("id",null) !!}
    <div class="input-field col s12">
        {!! Form::label("nombre","Nombre",["class"=>"active"]) !!}
        {!! Form::text("nombre",null,["id"=>"nombre","class"=>""]) !!}
    </div>
    <div class="input-field col s12">
        {!! Form::label("direccion","Dirección",["class"=>"active"]) !!}
        {!! Form::textarea("direccion",null,["id"=>"direccion","class"=>"materialize-textarea"]) !!}
    </div>
{!! Form::close() !!}