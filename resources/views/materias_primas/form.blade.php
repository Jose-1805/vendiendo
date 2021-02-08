<?php
    $url = url("/materia-prima/store");
    if($materia_prima->exists){
        $url = url("/materia-prima/update/".$materia_prima->id);
    }
    if(!(isset($barCodeProducto_))){
        $barCodeProducto_ = $materia_prima->codigo;
    }
?>
@include("templates.mensajes",["id_contenedor"=>"materia-prima"])
@section('css')
@parent
    <style>
        .proveedores{
            padding: 20px!important;
        }

        .proveedores a,.proveedores a i{
            line-height: 0px !important;
            cursor: pointer;
        }
        .proveedores:hover{
            background: rgba(130,130,130,.1);
        }
    </style>
@stop
{!!Form::model($materia_prima,["id"=>"form-materia-prima","url"=>$url,"enctype"=>"multipart/form-data"])!!}
    @if(isset($noPrOpc))
        {!!Form::hidden("noPrOpc","no-print")!!}
    @endif
    @if(isset($pr_))
        {!!Form::hidden("pr_",$pr_)!!}
    @endif

    <div class="col s12" style="padding: 20px;">
        <div class="input-field col s12 m6">
          {!!Form::label("nombre","Nombre")!!}
          {!!Form::text("nombre",null,["id"=>"nombre","placeholder"=>"Ingrese el nombre de la materia prima","maxlength"=>"100"])!!}
        </div>

        <div class="input-field col s12 m6">
          {!!Form::label("codigo","Código")!!}
          {!!Form::text("codigo",$barCodeProducto_,["id"=>"codigo","placeholder"=>"Ingrese el código de la materia prima","maxlength"=>"100"])!!}
        </div>
        <!--<div class="input-field col s12 m6">
            {!!Form::label("stock","Stock")!!}
            {!!Form::text("stock",null,["id"=>"stock","class"=>"num-entero","placeholder"=>"Ingrese el stock de la materia prima","maxlength"=>"100"])!!}
        </div>-->

        <div class="input-field col s12 m6">
            {!!Form::label("umbral","Umbral")!!}
            {!!Form::text("umbral",null,["id"=>"umbral","class"=>"num-entero","placeholder"=>"Ingrese el umbral de la materia prima","maxlength"=>"100"])!!}
        </div>

        <div class="input-field col s12 m6">
          {!!Form::label("descripcion","Descripción")!!}
          {!!Form::textarea("descripcion",null,["id"=>"descripcion","placeholder"=>"Ingrese la descripción de la materia prima","maxlength"=>"500", "class"=>"materialize-textarea","style"=>"padding: 0px; padding-top: 10px;"])!!}
        </div>

        @if(\Illuminate\Support\Facades\Auth::user()->permitirTarea("uploads","Crear","materias primas","inicio"))
            @if($materia_prima->imagen != "")
                <div class="col s12 m6 center">
                {!! Html::image(url("/app/public/img/materias_primas/".$materia_prima->imagen), $alt="", $attributes = array('style'=>'max-height: 200px;')) !!}
                </div>
                <div class="col s12 m6">.</div>
            @endif
            <div class="file-field input-field col s12 m6" style="padding-top: 10px;">
                <div class="col s12 center-align">
                    <img id="preview" src="#" alt="Materia prima" class="hide col s12">
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
        
        @if($funcion == "crear")
            <div class="input-field col s12 m8">
                <select id="select-unidad" name="select-unidad" class="col s8 m10 l11">
                    <option disabled selected>Seleccione un unidad</option>
                    @foreach(\App\Models\Unidad::unidadesPermitidas()->get() as $unidad)
                        <option value="{{$unidad->id}}">{{$unidad->nombre}}</option>
                    @endforeach
                </select>
                <label>Unidades</label>
                <a class="s4 m2 l1 tooltipped" id="add-unidad" href="#" data-position="bottom" data-delay="50" data-tooltip="Crear unidad"><i class="fa fa-plus" style="margin:10px;margin-top: 25px;"></i></a>
            </div>
        @elseif($funcion == "editar")
            <div class="input-field col s12 m8">
                <select id="select-unidad" name="select-unidad">
                    <option disabled selected>Seleccione una unidad</option>
                    @foreach(\App\Models\Unidad::unidadesPermitidas()->get() as $unidad)
                        @if($materia_prima->unidad->id == $unidad->id)
                            <option value="{{$unidad->id}}" selected>{{$unidad->nombre}}</option>
                        @else
                            <option value="{{$unidad->id}}">{{$unidad->nombre}}</option>
                        @endif
                    @endforeach
                </select>
                <label>Unidades</label>
            </div>
        @endif

        @if(\App\Models\Proveedor::permitidos()->get()->count() > 0)
        <p class="titulo col s12">Proveedores</p>
        <div id="contenedor-proveedores">
            @if($funcion == "crear")
                @if(!isset($noPrOpc))
                    <div id="proveedor-1" class="proveedores col s12">
                        <p class="titulo-modal">Proveedor #1</p>
                        <p class="right" style="margin-top: -50px;">
                            <input name="proveedor_actual" type="radio" id="proveedor_actual_1" checked="checked" value="1" />
                            <label for="proveedor_actual_1">Proveedor actual</label>
                        </p>
                        <a class="right"><i class="fa fa-close"></i></a>

                        <div class="input-field col s12 m6">
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
                        </div>

                        <div class="input-field col s12 m3">
                          {!!Form::label(null,"Valor")!!}
                          {!!Form::text("valor-1",null,["id"=>"valor-1","maxlength"=>"10","class"=>"num-real _valor"])!!}
                        </div>
                        <div class="input-field col s12 m3">
                          {!!Form::label(null,"Cantidad")!!}
                          {!!Form::text("cantidad-1",null,["id"=>"cantidad-1","maxlength"=>"10","class"=>"num-real _cantidad"])!!}
                        </div>
                    </div>
                @else
                    <input name="proveedor_actual" type="hidden" value="1" />
                    <!--<p class="titulo-modal">Proveedor</p>-->

                    @foreach(\App\Models\Proveedor::permitidos()->get() as $pr)
                        @if($pr_ == $pr->id)
                            <div class="input-field col s12 m6">
                                <label>Proveedor</label>
                                {!! Form::hidden("select-proveedor-1",$pr->id) !!}
                                {!! Form::text("",$pr->nombre,["disabled"=>"disabled"]) !!}
                            </div>

                            <div class="input-field col s12 m3">
                                {!!Form::label(null,"Valor")!!}
                                {!!Form::text("valor-1",null,["id"=>"valor-1","maxlength"=>"10","class"=>"num-real _valor"])!!}
                            </div>

                            <div class="input-field col s12 m3">
                                {!!Form::label(null,"Cantidad")!!}
                                {!!Form::text("cantidad-1",null,["id"=>"cantidad-1","maxlength"=>"10","class"=>"num-real _cantidad"])!!}
                            </div>
                        @endif
                    @endforeach
                @endif
            @elseif($funcion == "editar")
                <?php
                    $proveedores = $materia_prima->proveedores()->select("materias_primas_historial.*","materias_primas_historial.precio_costo_nuevo as valor")->get();
                ?>
                @if(count($proveedores))
                    <?php $aux = 1; ?>
                    @foreach($proveedores as $obj)
                        <div id="proveedor-{{$aux}}" class="proveedores col s12">
                            <p class="titulo-modal">Proveedor #{{$aux}}</p>
                            <?php
                            if($obj->proveedor_id == $materia_prima->proveedor_actual)
                                $checked = "checked='checked'";
                            else
                                $checked = "";
                            ?>
                            <a class="right"><i class="fa fa-close"></i></a>
                            <p class="right" style="margin-top: -50px;">
                                <input name="proveedor_actual" type="radio" id="proveedor_actual_{{$aux}}" {{$checked}} value="{{$aux}}" />
                                <label for="proveedor_actual_{{$aux}}">Proveedor actual</label>
                            </p>

                            <div class="input-field col s12 m6">
                                <select id="select-proveedor-{{$aux}}" name="select-proveedor-{{$aux}}">
                                    <option disabled selected>Seleccione un proveedor</option>
                                    @foreach(\App\Models\Proveedor::permitidos()->get() as $pr)
                                        @if($obj->proveedor_id == $pr->id)
                                            <option value="{{$pr->id}}" selected>{{$pr->nombre}}</option>
                                        @else
                                            <option value="{{$pr->id}}">{{$pr->nombre}}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <label>Proveedores</label>
                            </div>

                            <div class="input-field col s12 m3">
                                {!!Form::label(null,"Valor")!!}
                                {!!Form::text("valor-".$aux,$obj->valor,["id"=>"valor-".$aux,"maxlength"=>"10","class"=>"num-real _valor"])!!}
                            </div>

                            @if(!$materia_prima->exists || ($materia_prima->exists && !$materia_prima->aparicionesProductosCompras()))
                                <div class="input-field col s12 m3">
                                    {!!Form::label(null,"Cantidad")!!}
                                    {!!Form::text("cantidad-".$aux,$obj->stock,["id"=>"cantidad-".$aux,"maxlength"=>"10","class"=>"num-real _cantidad"])!!}
                                </div>
                            @endif

                        </div>
                        <?php $aux++; ?>
                    @endforeach
                @else
                    <p class="col s12 center">No se encontraron relaciones para mostrar</p>
                @endif

            @endif

        </div>

        @if($funcion == "editar")
            <div class="col s12 margin-top-10">
                <p class="titulo-modal" style="border: none;">
                    <strong>Promedio ponderado: </strong><span id="promedio-ponderado">$ {{number_format($materia_prima->promedio_ponderado,2,',','.')}}</span>
                </p>
                <p class="titulo-modal" style="border: none;">
                    <strong>Stock: </strong><span >{{$materia_prima->stock}}</span>
                </p>
            </div>
        @else

            <div class="col s12 margin-top-10">
                <p class="titulo-modal" style="border: none;">
                    <strong>Promedio ponderado: </strong><span id="promedio-ponderado">$ 0,00</span>
                </p>
            </div>
        @endif

        @if(!isset($noPrOpc))
            <div class="col s12 right-align hide-on-small-only">
                <a href="#!" class="btn blue-grey darken-2 waves-effect waves-light" id="add-proveedor">Crear proveedor</a>
                <a href="#!" class="btn blue-grey darken-2 waves-effect waves-light" onclick="agregarProveedor(this)">Asignar proveedor</a>
            </div>

            <div class="col s12 hide-on-med-and-up">
                <a href="#!" class="btn blue-grey darken-2 waves-effect waves-light col s12" onclick="agregarProveedor(this)">Asignar proveedor</a>
                <a href="#!" class="btn blue-grey darken-2 waves-effect waves-light col s12" id="add-proveedor">Crear proveedor</a>
            </div>
        @endif
        <div class="col s12 center" id="contenedor-action-form-materia-prima" style="margin-top: 30px;">
            <a class="btn blue-grey darken-2 waves-effect waves-light" id="btn-action-form-materia-prima">Guardar</a>
        </div>

        <div class="progress hide" id="progress-action-form-materia-prima" style="top: 30px;margin-bottom: 30px;">
            <div class="indeterminate cyan"></div>
        </div>
        @else
            <p class="col s12 center">No es posible registrar materias primas si no existen proveedores relacionados con el administrador</p>
        @endif
    </div>
{!!Form::close()!!}
@section('js')
@parent
    <script src="{{asset('js/materiasPrimasAction.js')}}"></script>
    @if($materia_prima->exists)
        <script>editar = true;</script>
    @endif
    @if($materia_prima->exists && $materia_prima->aparicionesProductosCompras())
        <script>poner_cantidad = false;</script>
    @endif
    <script>
        $(function(){
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

                }, true);
            })
        })
    </script>
@stop