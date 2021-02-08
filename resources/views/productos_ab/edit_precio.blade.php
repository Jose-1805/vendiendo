<?php
    $admin = \App\User::find(\Illuminate\Support\Facades\Auth::user()->userAdminId());
?>
@if($producto->aparicionesVentasCompras() || (!$producto->aparicionesVentasCompras() && count($producto->proveedores()->groupBy("proveedores.id")->get()) == 1) || $producto->tipo_producto !=  "Terminado")
    {!!Form::model($historial,["id"=>"form-edit-precio"])!!}
    <div class="row" style="padding: 20px;">
        @if(!$producto->aparicionesVentasCompras())
            <div class="input-field col s12 m6">
                {!!Form::label("stock","Cantidad",["class"=>"active"])!!}
                {!!Form::text("stock",null,["id"=>"stock","class"=>"num-real","placeholder"=>"Ingrese la cantidad existente en stock"])!!}
            </div>
        @endif

        <div class="input-field col s12 m6">
            {!!Form::label("precio_costo_nuevo","Precio costo",["class"=>"active"])!!}
            {!!Form::text("precio_costo_nuevo",null,["id"=>"precio_costo","class"=>"num-real","placeholder"=>"Ingrese precio costo"])!!}
        </div>
        @if($admin->regimen == "común")
            <div class="input-field col s12 m6">
                {!!Form::label("iva_nuevo","Iva % en compra",["class"=>"active"])!!}
                {!!Form::text("iva_nuevo",null,["id"=>"iva","class"=>"num-real","placeholder"=>"Ingrese iva"])!!}
            </div>
        @endif


        {!!Form::hidden("id",$producto->id)!!}
@else
    <p>
        Este producto está relacionado con más de un proveedor y no existen registros de ventas o compras del mismo, la edición
        del precio de costo o la cantidad en stock, en relación a uno de sus proveedores, producirá la actualización del promedio ponderado de
        éste producto. Para editar la información del producto <a href="{{url('/productos/edit/'.$producto->id)}}">CLICK AQUÍ</a>
    </p>
@endif
</div>
{!! Form::close() !!}