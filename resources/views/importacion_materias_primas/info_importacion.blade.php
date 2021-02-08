<div class="col s12">
    <p style="margin-top: -5px !important;"><strong>Nombre</strong></p>
    <p style="margin-top: -18px !important;">{{$importacion->nombre}}</p>
</div>
<div class="col s12">
    <p style="margin-top: -5px !important;"><strong>Descripción</strong></p>
    <p style="margin-top: -18px !important;">{{$importacion->descripcion}}</p>
</div>
<div class="col s12 m6">
    <p style="margin-top: -5px !important;"><strong>Barcode</strong></p>
    <p style="margin-top: -18px !important;">{{$importacion->barcode}}</p>
</div>
<div class="col s12 m6">
    <p style="margin-top: -5px !important;"><strong>Precio costo</strong></p>
    <p style="margin-top: -18px !important;">$ {{number_format($importacion->precio_costo,2,',','.')}}</p>
</div>
<div class="col s12 m6">
    <p style="margin-top: -5px !important;"><strong>Iva</strong></p>
    <p style="margin-top: -18px !important;">{{$importacion->iva}}%</p>
</div>
<div class="col s12 m6">
    <p style="margin-top: -5px !important;"><strong>Utilidad</strong></p>
    <p style="margin-top: -18px !important;">{{$importacion->utilidad}}%</p>
</div>
<div class="col s12 m6">
    <p style="margin-top: -5px !important;"><strong>Stock</strong></p>
    <p style="margin-top: -18px !important;">{{$importacion->stock}}</p>
</div>
<div class="col s12 m6">
    <p style="margin-top: -5px !important;"><strong>Umbral</strong></p>
    <p style="margin-top: -18px !important;">{{$importacion->umbral}}</p>
</div>
<div class="col s12 m6">
    <p style="margin-top: -5px !important;"><strong>Medida venta</strong></p>
    <p style="margin-top: -18px !important;">{{$importacion->medida_venta}}</p>
</div>
<div class="col s12 m6">
    <p style="margin-top: -5px !important;"><strong>Fecha</strong></p>
    <p style="margin-top: -18px !important;">{{date("Y-m-d",strtotime($importacion->created_at))}}</p>
</div>