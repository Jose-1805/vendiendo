<?php
$url = url("/productos-proveedor/store");
$accion= "create";
$unitario='checked="checked"';
$fraccional="";
$disable ="";
if($producto->exists){
    $accion ="edit";
    $disable="disabled";
    $url = url("/productos-proveedor/update/".$producto->id);

    if($producto->getAttribute('medida_venta')=="Fraccional"){$fraccional='checked="checked"';$unitario="";}
}
?>
@include("templates.mensajes",["id_contenedor"=>"producto"])

{!!Form::model($producto,["id"=>"form-producto","url"=>$url,"enctype"=>"multipart/form-data"])!!}
{!! Form::hidden("id",$producto->id) !!}
<div class="col s12" style="padding: 20px;">
    <div class="input-field col s12 m6">
        {!!Form::label("nombre","Nombre")!!}
        {!!Form::text("nombre",null,["id"=>"nombre","placeholder"=>"Ingrese el nombre del producto","maxlength"=>"100"])!!}
    </div>

    <div class="input-field col s12 m6">
        {{--aqui es campo para barcode--}}
        {!!Form::label("barcode","Código de barras")!!}
        {!!Form::text("barcode",null,["id"=>"barcode","placeholder"=>"Ingrese el código de barras del producto","maxlength"=>"100"])!!}
    </div>

    <div class="input-field col s12 m6">
        {!!Form::label("descripcion","Descripción")!!}
        {!!Form::text("descripcion",null,["id"=>"descripcion","placeholder"=>"Ingrese la descripción del producto","maxlength"=>"160"])!!}
    </div>

    <div class="input-field col s12 m6">
        <select id="select-categoria" name="select-categoria">
            <option disabled selected>Seleccione una categoria</option>
            @foreach(\App\Models\Categoria::listaNegociosObj() as $categoria)
                @if($producto->categoria_id == $categoria->id)
                    <option value="{{$categoria->id}}" selected>{{$categoria->nombre}}</option>
                @else
                    <option value="{{$categoria->id}}">{{$categoria->nombre}}</option>
                @endif
            @endforeach
        </select>
        <label>Categoria</label>
    </div>

    <div class="input-field col s12 m6">
        {!!Form::label("precio_costo","Precio")!!}
        {!!Form::text("precio_costo",null,["id"=>"precio_costo","class"=>"num-entero","placeholder"=>"Ingrese precio del producto","maxlength"=>"100"])!!}
    </div>

    <div class="input-field col s12 m6">
        <select id="select-unidad" name="select-unidad">
            <option disabled selected>Seleccione una unidad</option>
            @foreach(\App\Models\Unidad::where("superadministrador","si")->get() as $unidad)
                @if($producto->getAttribute('unidad_id') == $unidad->id)
                    <option value="{{$unidad->id}}" selected>{{$unidad->nombre}}</option>
                @else
                    <option value="{{$unidad->id}}">{{$unidad->nombre}}</option>
                @endif
            @endforeach
        </select>
        <label>Unidad</label>
    </div>

    <div class="input-field col s12 m6">
        <p style="margin-top: 30px">
            <input name="medida_venta" type="radio" id="Unitaria" value="Unitaria" {{$unitario}}/>
            <label for="Unitaria">Unitaria</label>

            <input name="medida_venta" type="radio" id="Fraccional" value="Fraccional" {{$fraccional}}/>
            <label for="Fraccional">Fraccional</label>
        </p>
        <label>Medida de venta</label>
    </div>

    @if($producto->imagen !='')
        <div class="col s12 m6 center">
            {{-- Html::image(url("/app/public/img/productos/".$producto->id."/".$producto->imagen), $alt="", $attributes = array('style'=>'max-height: 200px;')) --}}
            {!! Html::image(url("/img/productos/".$producto->id."/".$producto->imagen), $alt="", $attributes = array('style'=>'max-height: 200px;')) !!}
        </div>
        <div class="col s12 m6">.</div>
    @endif

    <div class="file-field input-field col s12 m6" style="padding-top: 10px;">
        <div class="col s12 center-align">
            <img id="preview" src="#" alt="Producto" class="hide col s12">
        </div>
        <div class="file-path-wrapper col s12 m8">
            <input class="file-path validate" type="text">
        </div>
        <div class="btn blue-grey darken-2 col s12 m4">
            <span>Imagen</span>
            <input type="file" name="imagen" id="imagen">
        </div>
    </div>

    <div class="input-field col s12 m12">
        {!! Form::label("tags","Tags",["class"=>"active"]) !!}
        {!! Form::textarea("tags",null,["id"=>"tags","class"=>"materialize-textarea","placeholder"=>"Ingrese aquì palabras relacionadas con su producto (separadas por comas ',')"]) !!}
    </div>

    <div class="input-field col s12 m12"></div>

    <div class="col s12 center" id="contenedor-action-form-producto" style="margin-top: 30px;">
        <a class="btn blue-grey darken-2 waves-effect waves-light" id="btn-action-form-producto">Guardar</a>
    </div>


</div>
{!!Form::close()!!}


@section('js')
    @parent
    <script src="{{asset('js/productos/productosProveedorAction.js')}}"></script>
@stop