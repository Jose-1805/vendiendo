<?php
    if(!isset($gasto))$gasto = new \App\Models\GastoDiario();
?>
{!! Form::model($gasto,["id"=>"form-gasto"]) !!}
    {!! Form::hidden("id",null) !!}
    <div class="input-field col s12">
        {!! Form::label("valor","Valor",["class"=>"active"]) !!}
        {!! Form::text("valor",null,["id"=>"valor","class"=>"num-entero"]) !!}
    </div>
    <div class="input-field col s12">
        {!! Form::label("descripcion","Descripción",["class"=>"active"]) !!}
        {!! Form::textarea("descripcion",null,["id"=>"descripcion","class"=>"materialize-textarea"]) !!}
    </div>
{!! Form::close() !!}