<?php
if(!isset($posicion))
    $posicion = "Compras";
?>
<div class="row">
    <div id="div-posicion"></div>
    <div class="col s12 m12">
        <div class="col s12 margin-bottom-20">
            <label>Seleccione la operación de caja</label>
        </div>
        <div class="col s6 m4">
            <input name="tipo_movimiento" type="radio" value="Adicionar" id="Adicionar" checked/>
            <label for="Adicionar">Adicionar</label>
        </div>
        @if($posicion != "Compras")
            <div class="col s6 m4">
                <input name="tipo_movimiento" type="radio" value="Reducir" id="Reducir" />
                <label for="Reducir">Reducir</label>
            </div>
            <div class="col s6 m4">
                <input name="tipo_movimiento" type="radio" value="Consignacion" id="Consignacion" />
                <label for="Consignacion">Consignación</label>
            </div>
        @endif

        <div class="col s6 margin-bottom-30"></div>
        <div class="col s12 m12">
            {!!Form::label("valor","Valor:")!!}
            {!!Form::text("valor",null,["id"=>"valor_caja","class"=>"cantidad-numerico num-real","placeholder"=>"Ingrese valor de la operacióm","maxlength"=>"100"])!!}
        </div>
        <div class=" col s12 m12">
            {!! Form::label("comentario","Comentario:") !!}
            {!! Form::textarea("comentario",'',["id"=>"comentario_caja","class"=>'materialize-textarea','size' => '30x5']) !!}
        </div>
        <div class="col s12 m12 text-center">
            <a href="#!" class="btn blue-grey darken-2 waves-effect waves-light" id="" onclick="entrarDineroCaja()">Ejecutar operación</a>
        </div>
    </div>
</div>