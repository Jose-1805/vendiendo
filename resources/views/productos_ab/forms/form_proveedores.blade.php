<?php
$admin = \App\User::find(\Illuminate\Support\Facades\Auth::user()->userAdminId());
$lastColumns = 3;
$r = "s";
if($admin->regimen == "común"){
    $r = "c";
    $lastColumns = 2;
}
if(!isset($producto)){
    if(\Illuminate\Support\Facades\Auth::user()->bodegas == 'si')
        $producto = new \App\Models\ABProducto();
    else
        $producto = new \App\Models\Producto();
}
?>
<div class="input-field col s12 m12" id="proveedor-div" >
    @if(\App\Models\Proveedor::permitidos()->get()->count() > 0)
        <p class="titulo col s12">Proveedores</p>
        <div id="contenedor-proveedores-productos">
            @if($accion == "create")
                <div id="proveedor-1" class="proveedores col s12">
                    @if(!isset($noPrOpc))
                        <p class="titulo-modal">Proveedor #1</p>
                        <p class="right" style="margin-top: -50px;">
                            <input name="proveedor_actual" type="radio" id="proveedor_actual_1" checked="checked" value="1" />
                            <label for="proveedor_actual_1">Proveedor actual</label>
                        </p>
                        <a href="#!" class="right" style="margin-bottom: -22px;"><i class="fa fa-close"></i></a>
                    @else
                        <input name="proveedor_actual" type="hidden" value="1" />
                        <p class="titulo-modal">Proveedor</p>
                    @endif
                    <div class="input-field col s12 l3">
                        @if(!isset($noPrOpc))
                            <select id="select-proveedor-1" name="select-proveedor-1">
                                <option disabled selected>Seleccione un proveedor</option>
                                @foreach(\App\Models\Proveedor::permitidos()->get() as $pr)
                                    <?php
                                    if(isset($pr_) && $pr_ == $pr->id)$selected = "selected";
                                    else $selected = "";
                                    ?>
                                    <option value="{{$pr->id}}" {{$selected}}>{{$pr->nombre}}</option>
                                @endforeach
                            </select>
                            <label>Proveedores</label>
                        @else
                            @foreach(\App\Models\Proveedor::permitidos()->get() as $pr)
                                @if($pr_ == $pr->id)
                                    {!! Form::hidden("select-proveedor-1",$pr->id) !!}
                                    {!! Form::text("",$pr->nombre,["disabled"=>"disabled"]) !!}
                                @endif
                            @endforeach
                            <label>Proveedor</label>
                        @endif
                    </div>

                    <div class="input-field col s12 l3">
                        {!!Form::label("precio_costo_1","Precio costo",["class"=>"active"])!!}
                        {!!Form::text("precio_costo_1",null,["id"=>"precio_costo_1","class"=>"num-real precio_costo","placeholder"=>"Ingrese precio costo"])!!}
                    </div>

                    @if($admin->regimen == "común")
                        <div class="input-field col s12 l3">
                            {!!Form::label("iva_1","Iva % en compra",["class"=>"active"])!!}
                            {!!Form::text("iva_1",null,["id"=>"iva_1","class"=>"num-real iva","placeholder"=>"Ingrese iva"])!!}
                        </div>
                    @endif
                    <div class="input-field col s12 l3">
                        {!!Form::label("cantidad_1","Cantidad",["class"=>"active"])!!}
                        {!!Form::text("cantidad_1",0,["id"=>"cantidad_1","class"=>"num-entero cantidad","placeholder"=>"Ingrese la cantidad"])!!}
                    </div>
                </div>
            @elseif($accion == 'edit')
                @if(count($detalle_producto)>0)
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
                                <input name="proveedor_actual" type="radio" id="proveedor_actual_{{$aux}}" {{$checked}} value="{{$aux}}" />
                                <label for="proveedor_actual_{{$aux}}">Proveedor actual</label>
                            </p>
                            <div class="input-field col s12 l3">
                                <select id="select-proveedor-{{$aux}}" name="select-proveedor-{{$aux}}">
                                    <option disabled selected>Seleccione un proveedor</option>
                                    @foreach(\App\Models\Proveedor::permitidos()->get() as $pr)
                                        @if($obj->proveedor_id == $pr->id)
                                            <option value="{{$pr->id}}" selected>{{$pr->nombre}}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <label>Proveedores</label>
                            </div>
                            <div class="input-field col s12 l3">
                                {!!Form::label("precio_costo_".$aux,"Precio costo",["class"=>"active"])!!}
                                {!!Form::text("precio_costo_".$aux,$obj->precio_costo_nuevo,["id"=>"precio_costo_".$aux,"class"=>"num-real precio_costo"])!!}
                            </div>

                            @if($admin->regimen == "común")
                                <div class="input-field col s12 l3">
                                    {!!Form::label("iva_".$aux,"Iva % en compra",["class"=>"active"])!!}
                                    {!!Form::text("iva_".$aux,$obj->iva_nuevo,["id"=>"iva_".$aux,"class"=>"num-real iva","placeholder"=>"Ingrese iva"])!!}
                                </div>
                            @endif

                            @if(!$producto->aparicionesVentasCompras())
                                <div class="input-field col s12 l3">
                                    {!!Form::label("cantidad_".$aux,"Cantidad",["class"=>"active"])!!}
                                    {!!Form::text("cantidad_".$aux,$obj->stock,["id"=>"cantidad_".$aux,"class"=>"num-entero cantidad","placeholder"=>"Ingrese la cantidad"])!!}
                                </div>
                            @endif
                        </div>
                        <?php $aux++; ?>
                    @endforeach
                @else
                    <p class="col s12 center">No tiene proveedores inscritos</p>
                @endif
            @endif

        </div>
        @if(!isset($noPrOpc))
            @if($accion == "create")
                <div class="col s12 margin-top-10"><p class="titulo-modal" style="border: none;"><strong>Promedio ponderado: </strong><span id="promedio-ponderado">$ {{number_format($producto->promedio_ponderado,2,',','.')}}</span></p></div>
            @endif
            <div class="col s12 right-align hide-on-small-only">
                <a href="#!" class="btn blue-grey darken-2 waves-effect waves-light" id="add-proveedor">Crear proveedor</a>
                <a href="#!" class="btn blue-grey darken-2 waves-effect waves-light" onclick="agregarProveedorProducto('{{$r}}',this,'{{$accion}}')">Otro proveedor</a>
            </div>

            <div class="col s12 hide-on-med-and-up">
                <a href="#!" class="btn blue-grey darken-2 waves-effect waves-light col s12" onclick="agregarProveedorProducto('{{$r}}',this,'{{$accion}}')">Otro proveedor</a>
                <a href="#!" class="btn blue-grey darken-2 waves-effect waves-light col s12" id="add-proveedor">Crear proveedor</a>
            </div>

            @if($accion == "edit")
                @if($producto->aparicionesVentasCompras())
                    <div class="col s12 margin-top-10"><p class="titulo-modal" style="border: none;"><strong>Promedio ponderado: </strong><span id="">$ {{number_format($producto->promedio_ponderado,2,',','.')}}</span></p></div>
                @else
                    <div class="col s12 margin-top-10"><p class="titulo-modal" style="border: none;"><strong>Promedio ponderado: </strong><span id="promedio-ponderado">$ {{number_format($producto->promedio_ponderado,2,',','.')}}</span></p></div>
                @endif
            @endif
        @endif
</div>

@else
    <p class="col s12 center">No es posible registrar materias primas si no existen proveedores relacionados con el administrador</p>
@endif
@section('js')
   @parent
    @if($producto->exists && $producto->aparicionesVentasCompras())
    <script>
        $(function () {
            poner_cantidad = false;
        })

    </script>
    @endif
@endsection