<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    @if(!isset($filtro))
        <?php $filtro=""; ?>
    @endif
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        <p class="titulo">Revisión producto</p>
        @include("templates.mensajes",["id_contenedor"=>"inventario-productos"])
        {!!Form::model($producto,["id"=>"form-producto-app","enctype"=>"multipart/form-data"])!!}
        {!! Form::hidden("producto_app",$producto->id) !!}
        <div class="col s12" style="padding: 20px;">
            <div class="input-field col s12 m6">
                {!!Form::label("nombre","Nombre")!!}
                {!!Form::text("nombre",null,["id"=>"nombre","placeholder"=>"Ingrese el nombre del producto","maxlength"=>"100"])!!}
            </div>
                <div class="input-field col s12 m6">
                    <select id="select-categoria" name="select-categoria">
                        <option disabled selected>Seleccione una categoria</option>
                        @foreach(\App\Models\Categoria::permitidos()->get() as $categoria)
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
                {!!Form::label("stock","Stock (cantidad de existencias en inventario)")!!}
                {!!Form::text("stock",null,["id"=>"stock","class"=>"num-entero","placeholder"=>"Ingrese el stock del producto","maxlength"=>"100"])!!}
            </div>
            <div class="input-field col s12 m6">
                {!!Form::label("umbral","Umbral (cantidad mínima de existencias permitido)")!!}
                {!!Form::text("umbral",null,["id"=>"umbral","class"=>"num-entero","placeholder"=>"Ingrese el umbral del producto","maxlength"=>"100"])!!}
            </div>
            <div class="input-field col s12 m6">
                {!!Form::label("descripcion","Descripción")!!}
                {!!Form::text("descripcion",null,["id"=>"descripcion","placeholder"=>"Ingrese la descripción del producto","maxlength"=>"160"])!!}
            </div>
                <div class="input-field col s12 m6">
                    <select id="select-unidad" name="select-unidad">
                        <option disabled selected>Seleccione una unidad</option>
                        @foreach(\App\Models\Unidad::unidadesPermitidas()->get() as $unidad)
                            @if($producto->unidad_id == $unidad->id)
                                <option value="{{$unidad->id}}" selected>{{$unidad->nombre}}</option>
                            @else
                                <option value="{{$unidad->id}}">{{$unidad->nombre}}</option>
                            @endif
                        @endforeach
                    </select>
                    <label>Unidad</label>
                </div>
            <div class="input-field col s12 m6">
                {{--aqui es campo para barcode--}}
                {!!Form::label("barcode","BarCode")!!}
                {!!Form::text("barcode",null,["id"=>"barcode","placeholder"=>"Ingrese el código de barras del producto","maxlength"=>"100"])!!}
            </div>
            <div class="input-field col s12 m6">
                <p style="margin-top: 30px">
                    @if($producto->medida_venta == "Unitaria")
                        <input name="medida_venta" type="radio" id="Unitaria" value="Unitaria" checked/>
                    @else
                        <input name="medida_venta" type="radio" id="Unitaria" value="Unitaria"/>
                    @endif
                    <label for="Unitaria">Unitaria</label>

                    @if($producto->medida_venta == "Fraccional")
                        <input name="medida_venta" type="radio" id="Fraccional" value="Fraccional" checked/>
                    @else
                        <input name="medida_venta" type="radio" id="Fraccional" value="Fraccional" />
                    @endif
                    <label for="Fraccional">Fraccional</label>
                </p>
                <label>Medida de venta</label>
            </div>

            @if(\Illuminate\Support\Facades\Auth::user()->permitirTarea("uploads","Crear","Productos","inicio"))
                @if($producto->imagen !='')
                    <div class="col s12 m6 center">
                        {!! Html::image(url("/app/public/img/productos/".$producto->id."/".$producto->imagen), $alt="", $attributes = array('style'=>'max-height: 200px;')) !!}
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
            @endif
            <div class="input-field col s12 m12"></div>
                <div id="form-contenido">
                    @include('productos.forms.form_proveedores',['accion'=>'create'])
                </div>
            <div class="col s12 center" id="contenedor-action-form-producto-app" style="margin-top: 30px;">
                <a class="btn blue-grey darken-2 waves-effect waves-light" id="btn-action-form-producto-app">Guardar</a>
            </div>

            <div class="progress hide" id="progress-action-form-producto-app" style="top: 30px;margin-bottom: 30px;">
                <div class="indeterminate cyan"></div>
            </div>

        </div>
        {!!Form::close()!!}

    </div>
@endsection

@section('js')
    @parent
    <script>
        $(function () {
            $("#umbral").focus();
            $("#add-proveedor").click(function(){
                windowProveedor = window.open('/proveedor/create?noPrOpc=1','Crear proveedor','width=600,resizable=0,toolbar=0,menubar=0');
                windowProveedor.addEventListener('beforeunload', function(){
                    $.post("/proveedor/select",{id:"select-proveedor",_token:$("#general-token").val()},function(data){
                        var htmlSelect = data+"<label>Proveedores</label>";
                        $(".proveedores").each(function(i,el){
                            var selectOld = $(el).children(".input-field").children(".select-wrapper").children("select").eq(0);
                            var idSelectOld = $(selectOld).attr("id");
                            var valueSelectOld = $(selectOld).val();
                            var input_field = $(selectOld).parent().parent();
                            $(input_field).html(htmlSelect);
                            var selectNew = $(input_field).children("#select-proveedor");
                            $(selectNew).attr("id",idSelectOld);
                            $(selectNew).attr("name",idSelectOld);
                            $("#"+idSelectOld+" option").filter(function() {
                                //may want to use $.trim in here
                                return $(this).val() == valueSelectOld;
                            }).attr('selected', true);
                        });
                        inicializarMaterialize();
                        //$("#select-categoria").parent().html(data+"<label>Categoria</label>");
                    })
                }, true);
            })
        })
    </script>
    <script src="{{asset('js/productos/productosAction.js')}}"></script>
    <script src="{{asset('js/productos/funciones.js')}}"></script>
@stop