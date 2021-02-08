<?php
    if(!isset($costo_fijo))
        $costo_fijo = new \App\Models\CostoFijo();
?>
<div class="col s12 input-field">
    {!! Form::text("nombre",null,["id"=>"nombre","maxlength"=>"50"]) !!}
    {!! Form::label("nombre","Nombre",["id"=>"label-nombre"]) !!}
</div>
<div class="col s12 input-field">
    {!! Form::select("estado",[""=>"seleccione","habilitado"=>"habilitado","inhabilitado"=>"inhabilitado"],$costo_fijo->estado ,["id"=>"estado"]) !!}
    {!! Form::label("estado","Estado",["id"=>"label-estado"]) !!}
</div>