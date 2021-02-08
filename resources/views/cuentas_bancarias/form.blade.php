<?php
    if(!isset($accion))
        $accion = "Agregar";

    if(!isset($cuenta))
        $cuenta = new \App\Models\CuentaBancaria();
?>
    <p class="titulo-modal">{{$accion}} cuenta bancaria</p>
    @include("templates.mensajes",["id_contenedor"=>"form-cuenta-bancaria"])
    {!! Form::model($cuenta,["id"=>"form-cuenta-bancaria"]) !!}
        <div id="" style="width: 100%" class="">
            <div class="input-field col s12">
                {!! Form::select("banco",[""=>"Seleccione un banco"]+\App\Models\Banco::lists("nombre","id"),["id"=>"banco"]) !!}
                <label for="banco" class="">Banco</label>
            </div>
            <div class="input-field col s12">
                {!! Form::text("titular",null,["id"=>"titular","placeholder"=>"Ingrese el titular de la cuenta"]) !!}
                <label for="titular" class="active">Titular</label>
            </div>
            <div class="input-field col s12">
                {!! Form::text("numero",null,["id"=>"numero","placeholder"=>"Ingrese el numero de la cuenta"]) !!}
                <label for="numero" class="active">Número</label>
            </div>
            <div class="input-field col s12 margin-top-30">
                {!! Form::text("saldo",null,["id"=>"saldo","class"=>"num-entero ","placeholder"=>"Ingrese el saldo de la cuenta"]) !!}
                <label for="saldo" class="active">Saldo</label>
            </div>

            {!! Form::hidden("accion",$accion,["name"=>"accion"]) !!}
            {!! Form::hidden("cuenta-bancaria",$cuenta->id,["name"=>"cuenta-bancaria"]) !!}
        </div>
    {!! Form::close() !!}