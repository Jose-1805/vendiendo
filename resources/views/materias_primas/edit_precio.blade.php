<?php
    $admin = \App\User::find(\Illuminate\Support\Facades\Auth::user()->userAdminId());
?>
@if($materia_prima->aparicionesProductosCompras() || (!$materia_prima->aparicionesProductosCompras() && count($materia_prima->proveedores_eloquent()->groupBy("proveedores.id")->get()) == 1))
    {!!Form::model($historial,["id"=>"form-edit-precio-materia-prima"])!!}
    <div class="row" style="padding: 20px;">
        @if(!$materia_prima->aparicionesProductosCompras())
            <div class="input-field col s12 m6">
                {!!Form::label("stock","Cantidad",["class"=>"active"])!!}
                {!!Form::text("stock",null,["id"=>"stock","class"=>"num-real","placeholder"=>"Ingrese la cantidad existente en stock"])!!}
            </div>
        @endif

        <div class="input-field col s12 m6">
            {!!Form::label("precio_costo_nuevo","Precio costo",["class"=>"active"])!!}
            {!!Form::text("precio_costo_nuevo",null,["id"=>"precio_costo","class"=>"num-real","placeholder"=>"Ingrese precio costo"])!!}
        </div>
        {!!Form::hidden("id",$materia_prima->id)!!}
@else
    <p>
        Esta materia prima está relacionada con más de un proveedor y no existen registros de compras o productos compuestos por la misma, la edición
        del precio de costo o la cantidad en stock, en relación a uno de sus proveedores, producirá la actualización del promedio ponderado de
        ésta materia prima. Para editar la información de la materia prima <a href="{{url('/materia-prima/edit/'.$materia_prima->id)}}">CLICK AQUÍ</a>
    </p>
@endif
</div>
{!! Form::close() !!}