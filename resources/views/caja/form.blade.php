<?php
if(!isset($caja))
    $caja = new \App\Models\Cajas();
?>

{!! Form::model($caja,["id"=>"form-caja"]) !!}
    <div id="" style="width: 100%" class="padding-20">

        @if(!$caja->exists || ($caja->exists && $caja->permitirAccion()))
            <div class="input-field col s12">
                {!! Form::text("nombre",null,["id"=>"nombre","placeholder"=>"Ingrese el nombre de la caja"]) !!}
                <label for="nombre" class="active">Nombre</label>
            </div>
            <div class="input-field col s12">
                {!! Form::text("prefijo",null,["id"=>"prefijo","placeholder"=>"Identificación única para la caja"]) !!}
                <label for="prefijo" class="active">Prefijo</label>
            </div>
        @endif

        @if($caja->exists && !$caja->permitirAccion())
            <div class="input-field col s12">
                {!! Form::select("estado",["abierta"=>"Abierta","cerrada"=>"Cerrada","otro"=>"otro"],$caja->estado,["id"=>"estado"]) !!}
                <label for="estado" class="">Estado</label>
            </div>

            <div class="input-field col s12 hide" id="contenedor-razon-estado">
                {!! Form::textarea("razon_estado",null,["id"=>"razon_estado","class"=>"materialize-textarea"]) !!}
                <label for="estado" class="">Razón de estado</label>
            </div>
        @endif

        {!! Form::hidden("caja",$caja->id,["name"=>"caja"]) !!}
    </div>
{!! Form::close() !!}