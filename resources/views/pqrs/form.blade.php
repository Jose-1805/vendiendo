<?php
    if(!isset($pqrs))
        $pqrs = new \App\Models\Pqrs();
?>
<div class="col s12 m6 input-field">
    {!! Form::select("tipo_identificacion",["C.C"=>"C.C","C.E"=>"C.E","NIT"=>"NIT","T.I"=>"T.I"],null,["id"=>"tipo_identificacion"]) !!}
    {!! Form::label("tipo_identificacion","Tipo de identificación") !!}
</div>
<div class="col s12 m6 input-field">
    {!! Form::text("identificacion",null,["id"=>"identificacion"]) !!}
    {!! Form::label("identificacion","Identificación") !!}
</div>
<div class="col s12 m6 input-field">
    {!! Form::text("nombre",null,["id"=>"nombre"]) !!}
    {!! Form::label("nombre","Nombre") !!}
</div>
<div class="col s12 m6 input-field">
    {!! Form::text("email",null,["id"=>"email"]) !!}
    {!! Form::label("email","Email") !!}
</div>
<div class="col s12 m6 input-field">
    {!! Form::text("direccion",null,["id"=>"direccion"]) !!}
    {!! Form::label("direccion","Dirección") !!}
</div>
<div class="col s12 m6 input-field">
    {!! Form::text("telefono",null,["id"=>"telefono"]) !!}
    {!! Form::label("telefono","Teléfono") !!}
</div>
<div class="col s12 m6 input-field">
    {!! Form::select("tipo",["Queja"=>"Queja","Sugerencia"=>"Sugerencia","Consulta"=>"Consulta","Petición"=>"Petición","Solicitud de información"=>"Solicitud de información"],null,["id"=>"tipo"]) !!}
    {!! Form::label("tipo","Tipo de pqrs") !!}
</div>
<div class="col s12 input-field">
    {!! Form::textarea("queja",null,["id"=>"queja","class"=>"materialize-textarea"]) !!}
    {!! Form::label("queja","Queja") !!}
</div>