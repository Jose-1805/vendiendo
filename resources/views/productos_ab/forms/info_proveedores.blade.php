<?php
$admin = \App\User::find(\Illuminate\Support\Facades\Auth::user()->userAdminId());
$lastColumns = 3;
$r = "s";
if($admin->regimen == "común"){
    $r = "c";
    $lastColumns = 2;
}
        if(!isset($producto))$producto = new \App\Models\Producto();
?>
<div class="input-field col s12 m12" id="proveedor-div" >
        <p class="titulo col s12">Proveedores</p>
                    <?php $aux = 1; ?>
                    @foreach($detalle_producto as $obj)
                        <div id="proveedor-{{$aux}}" class="proveedores col s12">
                            <p class="titulo-modal">Proveedor #{{$aux}}</p>
                            <?php
                            if($obj->proveedor_id == $producto->proveedor_actual)
                                $checked = "checked='checked'";
                            else
                                $checked = "";
                            ?>
                            <p class="right" style="margin-top: -50px;">
                                <input name="proveedor_actual" type="radio" id="proveedor_actual_{{$aux}}" {{$checked}} value="{{$aux}}" disabled/>
                                <label for="proveedor_actual_{{$aux}}">Proveedor actual</label>
                            </p>
                            <div class="input-field col s12 l3">
                                @foreach(\App\Models\Proveedor::permitidos()->get() as $pr)
                                    @if($obj->proveedor_id == $pr->id)
                                        <p>{{$pr->nombre}}</p>
                                    @endif
                                @endforeach
                                <label class="active">Proveedores</label>
                            </div>
                            <div class="input-field col s12 l2">
                                {!!Form::label("precio_costo_".$aux,"Precio costo",["class"=>"active"])!!}
                                <p>$ {{number_format($obj->precio_costo_nuevo,2,',','.')}}</p>
                            </div>

                            @if($admin->regimen == "común")
                                <div class="input-field col s12 l2">
                                    {!!Form::label("iva_".$aux,"Iva % en compra",["class"=>"active"])!!}
                                    <p>{{number_format($obj->iva_nuevo,2,',','.')}}%</p>
                                </div>
                            @endif
                            <div class="input-field col s12 l{{$lastColumns}}">
                                {!!Form::label("utilidad_".$aux,"Utilidad %",["class"=>"active"])!!}
                                <p>{{number_format($obj->utilidad_nueva,2,',','.')}}%</p>
                            </div>
                            <div class="input-field col s12 l{{$lastColumns}}">
                                {!!Form::label("precio_venta_".$aux,"Precio venta",["class"=>"active"])!!}
                                <?php
                                $precio_venta = $obj->precio_costo_nuevo+($obj->precio_costo_nuevo*$obj->utilidad_nueva)/100;
                                $precio_venta += ($precio_venta * $obj->iva_nuevo)/100;
                                ?>
                                <p>$ {{number_format($precio_venta,2,',','.')}}</p>
                            </div>
                        </div>
                        <?php $aux++; ?>
                    @endforeach
                    <p class="col s12 titulo-modal margin-top-40" style="border: none;"><strong>Promedio ponderado: </strong>$ {{number_format($producto->promedio_ponderado,2,',','.')}}</p>
</div>