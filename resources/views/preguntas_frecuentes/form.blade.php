<?php
    if(!isset($pregunta)){
        $pregunta = new \App\Models\PreguntaFrecuente();
        $id = "form-crear-pregunta-frecuente";
    }else{
        $id = "form-editar-pregunta-frecuente";
    }
?>
{!! Form::model($pregunta,["id"=>$id]) !!}
{!! Form::hidden("id",$pregunta->id) !!}
<div class="col s12 input-field">
    {!! Form::textarea("pregunta",null,["id"=>"pregunta","class"=>"materialize-textarea"]) !!}
    {!! Form::label("pregunta","Pregunta",["class"=>"active"]) !!}
</div>
<div class="col s12 input-field">
    {!! Form::textarea("respuesta",null,["id"=>"respuesta","class"=>"materialize-textarea"]) !!}
    {!! Form::label("respuesta","Respuesta",["class"=>"active"]) !!}
</div>
<div class="col s12 input-field">
    {!! Form::textarea("embebido",null,["id"=>"embebido","class"=>"materialize-textarea"]) !!}
    {!! Form::label("embebido","Codigo Embebido:(Solo el src)",["class"=>"active"]) !!}
</div>

<div class="col s12 input-field">
    {!! Form::textarea("enlace",null,["id"=>"enlace","class"=>"materialize-textarea"]) !!}
    {!! Form::label("enlace","Enlace relacionado:(Solor el href)",["class"=>"active"]) !!}
</div>
{!! Form::close() !!}