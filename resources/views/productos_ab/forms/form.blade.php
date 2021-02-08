<?php

$url = url("/productos/store");
$accion= "create";
$terminado='checked="checked"';
$unitario='checked="checked"';
$fraccional="";
$compuesto=$preparado="";
$disable ="";
$admin = \App\User::find(\Illuminate\Support\Facades\Auth::user()->userAdminId());
$hidePrecios = "hide";
$precioV = "";
if($producto->exists){
    $accion ="edit";
    $disable="disabled";
    $url = url("/productos/update/".$producto->id);
    $terminado=$compuesto=$preparado="";
    if($producto->getAttribute('tipo_producto')=="Terminado")$terminado='checked="checked"';
    if($producto->getAttribute('tipo_producto')=="Compuesto")$compuesto='checked="checked"';
    if($producto->getAttribute('tipo_producto')=="Preparado")$preparado='checked="checked"';

    if($producto->getAttribute('medida_venta')=="Fraccional"){$fraccional='checked="checked"';$unitario="";}

    if($producto->tipo_producto != "Terminado"){
        $hidePrecios = "";
        $precioV = $producto->precio_costo+($producto->precio_costo * $producto->utilidad)/100;
        $precioV += ($precioV * $producto->iva)/100;
    }

    if(!isset($barCodeProducto_))
        $barCodeProducto_ = $producto->barcode;
}
$valor = 0.0;
?>
@include("templates.mensajes",["id_contenedor"=>"producto"])

{!!Form::model($producto,["id"=>"form-producto","url"=>$url,"enctype"=>"multipart/form-data"])!!}
{!! Form::hidden("producto",$producto->id) !!}
<div class="col s12" style="padding: 20px;">
    <div class="input-field col s12 m6">
        <div class="input-field col s12 m12">
            @if(isset($noPrOpc))<?php $disable = "disabled"; ?>@endif
            <input name="tipo_producto" type="radio" value="Terminado" id="Terminado" {{$disable}} {{$terminado}} onclick="viewMateriasPrimas(this.id,'{{$accion}}')"/>
            <label for="Terminado">Terminado</label>
            @if(!isset($noPrOpc))
                <input name="tipo_producto" type="radio" value="Compuesto" id="Compuesto" {{$disable}} {{$compuesto}}onclick="viewMateriasPrimas(this.id, '{{$accion}}')"/>
                <label for="Compuesto">Compuesto</label>
            @endif
        </div>
        @if($funcion == 'editar')
            <input type="hidden" name="tipo_producto" value="{{$producto->getAttribute('tipo_producto')}}"/>
        @endif
    </div>
    <div class="input-field col s12 m6">
        {!!Form::label("nombre","Nombre")!!}
        {!!Form::text("nombre",null,["id"=>"nombre","placeholder"=>"Ingrese el nombre del producto","maxlength"=>"100"])!!}
    </div>
    @if($funcion == "crear")
        <div class="input-field col s12 m6">
            <select id="select-categoria" name="select-categoria" class="col s8 m10 l11">
                <option disabled selected>Seleccione una categoria</option>
                @foreach(\App\Models\Categoria::permitidos()->get() as $categoria)
                    <option value="{{$categoria->id}}">{{$categoria->nombre}}</option>
                @endforeach
            </select>
            <label>Categoria</label>
            <a class="s4 m2 l1 tooltipped modal-trigger" href="#modal-accion-categoria" data-position="bottom" data-delay="50" data-tooltip="Crear categoria"><i class="fa fa-plus" style="margin:10px;margin-top: 25px;"></i></a>
        </div>
    @elseif($funcion == "editar")
        <div class="input-field col s12 m6">
            <select id="select-categoria" name="select-categoria">
                <option disabled selected>Seleccione una categoria</option>
                @foreach(\App\Models\Categoria::permitidos()->get() as $categoria)
                    @if($producto->getAttribute('categoria_id') == $categoria->id)
                        <option value="{{$categoria->id}}" selected>{{$categoria->nombre}}</option>
                    @else
                        <option value="{{$categoria->id}}">{{$categoria->nombre}}</option>
                    @endif
                @endforeach
            </select>
            <label>Categoria</label>
        </div>
    @endif

    @if($accion == 'edit')
        @if(count($detalle_producto)>0)
            @foreach($detalle_producto as $obj)
                @foreach(\App\Models\MateriaPrima::materiasPrimasPermitidas()->get() as $pr)
                    @if($obj->id == $pr->id)
                      <?php
                            $cantidad_aux = 1;
                            if (isset($obj->cantidad))
                                $cantidad_aux = $obj->cantidad;
                        $valor += ($pr->precio_costo * $cantidad_aux);
                    ?>
                    @endif
                @endforeach
            @endforeach
        @else
            <?php $valor = 0.0;?>
        @endif
    @endif
    <div id="precios" class="{{$hidePrecios}}">
        <?php
            $valor = number_format($valor,2,',','.');
        ?>
    <div class="input-field col s12 m6">
        {!!Form::label("precio_costo","Precio costo (Costo materias primas: $ $valor)",["id"=>"costo_materias_primas_hidden"])!!}
        {!!Form::text("precio_costo",null,["id"=>"precio_costo","class"=>"num-entero","placeholder"=>"Ingrese precio costo","maxlength"=>"100"])!!}
    </div>
    @if($admin->regimen == "común")
        <div class="input-field col s12 m6">
            {!!Form::label("iva","Iva %")!!}
            {!!Form::text("iva",null,["id"=>"iva","class"=>"num-real","placeholder"=>"Ingrese iva","maxlength"=>"100"])!!}
        </div>
    @endif

    @if(isset($noPrOpc))
        {!!Form::hidden("noPrOpc","no-print")!!}
    @endif
    </div>
    <div class="input-field col s12 m6 hide" id="contenedor-stock">
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
    @if($funcion == "crear")
        <div class="input-field col s12 m6">
            <select id="select-unidad" name="select-unidad" class="col s8 m10 l11">
                <option disabled selected>Seleccione una unidad</option>
                @foreach(\App\Models\Unidad::unidadesPermitidas()->get() as $unidad)
                    <option value="{{$unidad->id}}">{{$unidad->nombre}}</option>
                @endforeach
            </select>
            <label>Unidad</label>
            <a class="s4 m2 l1 tooltipped" id="add-unidad" href="#" data-position="bottom" data-delay="50" data-tooltip="Crear unidad"><i class="fa fa-plus" style="margin:10px;margin-top: 25px;"></i></a>
        </div>
    @elseif($funcion == "editar")
        <div class="input-field col s12 m6">
            <select id="select-unidad" name="select-unidad" class="col s8 m10 l11">
                <option disabled selected>Seleccione una unidad</option>
                @foreach(\App\Models\Unidad::unidadesPermitidas()->get() as $unidad)
                    @if($producto->getAttribute('unidad_id') == $unidad->id)
                        <option value="{{$unidad->id}}" selected>{{$unidad->nombre}}</option>
                    @else
                        <option value="{{$unidad->id}}">{{$unidad->nombre}}</option>
                    @endif
                @endforeach
            </select>
            <label>Unidad</label>
            <a class="s4 m2 l1 tooltipped" id="add-unidad" href="#" data-position="bottom" data-delay="50" data-tooltip="Crear unidad"><i class="fa fa-plus" style="margin:10px;margin-top: 25px;"></i></a>
        </div>
    @endif
    <div class="input-field col s12 m6">
        {{--aqui es campo para barcode--}}
        {!!Form::label("barcode","Código de barras")!!}
        {!!Form::text("barcode",$barCodeProducto_,["id"=>"barcode","placeholder"=>"Ingrese el código de barras del producto","maxlength"=>"100"])!!}
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

    @if(\Illuminate\Support\Facades\Auth::user()->permitirTarea("uploads","Crear","Productos","inicio"))
        @if($producto->imagen !='')
            <div class="col s12 m6 center" id="imagen_producto">
                {!! Html::image(url("/app/public/img/productos/".$producto->id."/".$producto->imagen), $alt="", $attributes = array('style'=>'max-height: 200px;max-width: 100%;')) !!}
            </div>
            <div class="col s12 m6 white-text">.</div>
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
    @if($accion =='create')
        <div id="form-contenido">
            @include('productos_ab.forms.form_proveedores',['accion'=>'create'])
        </div>
    @elseif($accion =='edit')
        @if($terminado !="")
            <div id="form-contenido">
                @include('productos_ab.forms.form_proveedores',['accion'=>'edit'])
            </div>
        @else
            <div id="form-contenido">
                @include('productos_ab.forms.form_materias_primas',['accion'=>'edit'])
            </div>
        @endif
    @endif
    @if($accion == "edit")
        <div class="col s12"><p class="col s12 titulo-modal"><strong>Stock: </strong>{{$producto->stock}}</p></div>
    @endif

    <div class="col s12 center" id="contenedor-action-form-producto" style="margin-top: 30px;">
        <a class="btn blue-grey darken-2 waves-effect waves-light" id="btn-action-form-producto">Guardar</a>
    </div>

    <div class="progress hide" id="progress-action-form-producto" style="top: 30px;margin-bottom: 30px;">
        <div class="indeterminate cyan"></div>
    </div>

</div>

<div id="modal-almacenes-stock" class="modal modal-fixed-footer" >
    <div class="modal-content">
        <p class="titulo-modal">Almacenes</p>
        <p class="text-info"><strong>NOTA: </strong> Los almacenes donde no se ingrese el campo cantidad o el campo precio venta no serán tenidos en cuenta.</p>
        <div id="contenido-almacenes-stock">
            @forelse(\App\Models\Almacen::permitidos()->get() as $al)
                <div class="col s12">
                    <div class="col s4">
                        <p>{!! $al->nombre !!}</p>
                    </div>

                    <div class="col s4">
                        {!! Form::label('cantidad_al_'.$al->id,'Cantidad') !!}
                        {!! Form::text('cantidad_al_'.$al->id,null,['id'=>'cantidad_al_'.$al->id,'class'=>'num-entero cantidad_almacen']) !!}
                    </div>

                    <div class="col s4">
                        {!! Form::label('precio_venta_al_'.$al->id,'Precio venta') !!}
                        {!! Form::text('precio_venta_al_'.$al->id,null,['id'=>'precio_venta_al_'.$al->id,'class'=>'num-entero']) !!}
                    </div>
                </div>
            @empty
                <p>No se han registrado almacenes, todas las unidades de este producto se almacenarán en bodega</p>
            @endforelse
            <p class="col s12"><strong>Cantidad de unidades en bodega: </strong><span id="cantidad_bodega">0</span></p>
        </div>
    </div>

    <div class="modal-footer">
        <a href="#!" class="btn-flat waves-effect waves-block" id="btn-almacenes-stock">Guardar</a>
        <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cerrar</a>
    </div>
</div>
{!!Form::close()!!}

<div id="modal-accion-categoria" class="modal modal-fixed-footer ">
    <div class="modal-content">
        <div id="contenido-accion-categoria">
            @include('categoria.form',["categoria"=> new \App\Models\Categoria(),"accion"=>"Agregar"])
        </div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-accion-categoria">
            <a href="#!" class="btn-flat waves-effect waves-block" id="btn-accion-categoria">Guardar</a>
            <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cerrar</a>
        </div>
        <div class="progress hide" id="progres-accion-categoria">
            <div class="indeterminate"></div>
        </div>
    </div>
</div>



@section('js')
    @parent
    <script src="{{asset('js/categoriaAction.js')}}"></script>
    <script src="{{asset('js/productos_ab/funciones.js')}}"></script>
    <script src="{{asset('js/productos_ab/productosAction.js')}}"></script>

    <script>
        var windowProveedor = null;
        $(document).ready(function (){
            /*$('.cantidad-numerico').keyup(function (){
                this.value = (this.value + '').replace(/[^0-9]/g, '');
            });*/
            $('.cantidad-numerico').numeric();
            $('.cantidad-decimal').numeric(".");

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
                                if(valueSelectOld) {
                                    return $(this).val() == valueSelectOld;
                                }else{
                                    if(localStorage.proveedor)
                                        if($(this).val() == localStorage.proveedor){
                                            localStorage.removeItem("proveedor");
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    else
                                        return false
                                }
                            }).attr('selected', true);
                        });
                        inicializarMaterialize();
                        //$("#select-categoria").parent().html(data+"<label>Categoria</label>");
                    })
                }, true);
            })

            $("#add-unidad").click(function(){
                windowProveedor = window.open('/unidades/create?noPrOpc=1','Crear unidad','width=600,resizable=0,toolbar=0,menubar=0');
                windowProveedor.addEventListener('beforeunload', function(){
                    var data = JSON.parse(localStorage.getItem("strDataUnidades"));

                    $('#select-unidad option[value!="0"]').remove();//Elimino los valores que tenga el select
                    opciones='<option value="" >Seleccione una unidad</option>';
                    $.each(data.valores, function(i, item) {
                        seleccionado="";
                        //id del objeto creado
                        if(data.id_anterior == data.valores[i].id) seleccionado=' selected="true" '; //Si coincide el id con el recien ingresado se selecciona
                        opciones+='<option value="' +data.valores[i].id +'" '+ seleccionado  + ' >' + data.valores[i].nombre + '</option>';
                    })
                    $('#select-unidad').append(opciones);//Actualizo los valores del select
                    localStorage.removeItem("strDataUnidades");
                    inicializarMaterialize();
                    windowProveedor.close();

                }, true);
            })
        });
    </script>
@stop