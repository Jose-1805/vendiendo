<div class="col s12 m6">
    <strong>Nombre</strong>
    <p style="margin-top: 0px;">{{$producto->nombre}}</p>
</div>

<div class="col s12 m6">
    <strong>Descripción</strong>
    <p style="margin-top: 0px;">{{$producto->descripcion}}</p>
</div>

<div class="col s12"></div>

<div class="col s12 m6">
    <strong>Barcode</strong>
    <p style="margin-top: 0px;">{{$producto->barcode}}</p>
</div>

<div class="col s12 m6">
    <strong>Precio</strong>
    <p style="margin-top: 0px;">$ {{number_format($producto->precio_costo,2,',','.')}}</p>
</div>

<div class="col s12"></div>

<div class="col s12 m6">
    <strong>Categoría</strong>
    <p style="margin-top: 0px;">{{$producto->categoria->nombre}}</p>
</div>

<div class="col s12 m6">
    <strong>Unidad</strong>
    <p style="margin-top: 0px;">{{$producto->unidad->nombre}}</p>
</div>

<div class="col s12"></div>

<div class="col s12 m6">
    <strong>Medida venta</strong>
    <p style="margin-top: 0px;">{{$producto->medida_venta}}</p>
</div>

@if($producto->imagen !='')
    <div class="col s12 center">
        {{-- Html::image(url("/app/public/img/productos/".$producto->id."/".$producto->imagen), $alt="", $attributes = array('style'=>'max-height: 200px;')) --}}
        {!! Html::image(url("/img/productos/".$producto->id."/".$producto->imagen), $alt="", $attributes = array('style'=>'max-height: 200px;')) !!}
    </div>
@endif
