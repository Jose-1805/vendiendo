<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        <p class="titulo">Crear compra</p>
        @include("templates.mensajes",["id_contenedor"=>"crear-compra"])
        <div class="col s12 m10 offset-m1" id="datos-proveedor">
            <p class="titulo-modal"><i class="fa fa-angle-double-up cyan white-text waves-effect waves-light btn-toggle-datos-proveedor" style="cursor: pointer;border-radius: 80px !important;width: 17px;text-align: center;"></i> Datos del proveedor</p>

            <div class="input-field col m6 l4 right hide-on-small-only buscar-proveedor" style="margin-top: -64px;">
                <input type="text" class="hide" name="busqueda" id="busqueda-proveedor" placeholder="Buscar" value="" style="border: none !important;">
                <button class="btn blue-grey darken-2 waves-effect waves-light btn-buscar-proveedor" style="float: right;"><i class="fa fa-search icono-buscar" style=""></i><i class="fa fa-spin fa-spinner hide icono-load-buscar" style=""></i></button>
            </div>

            <div class="input-field col s12 hide-on-med-and-up buscar-proveedor" >
                <input type="text" name="busqueda2" id="busqueda-proveedor-2" placeholder="Buscar" value="">
                <button class="btn blue-grey darken-2 waves-effect waves-light btn-buscar-proveedor" style="float: right;margin-top: -55px;padding: 0px 5px !important;"><i class="fa fa-search icono-buscar" style=""></i><i class="fa fa-spin fa-spinner hide icono-load-buscar" style=""></i></button>
            </div>

            <div class="col s12 no-padding">
                <p class="" style="width: 150px;display: inline-block;"><strong>Nombre: </strong></p>
                <p class="dato-proveedor grey-text text-darken-1" style="display: inline-block;" ><span id="txt-nombre">No establecido</span>
                    <span id="contenedor-botones-cliente-up" class="hide"><i class="cyan-text modal-trigger btn-crear-proveedor fa fa-plus-square-o fa-1x" title="Crear un nuevo proveedor" style="cursor: pointer;" href="#modal-crear-proveedor"></i></span>
                </p>
            </div>

            <div id="info-proveedor">
                <div class="col s12 m6 no-padding">
                    <p class="" style="width: 150px;display: inline-block;"><strong>NIT: </strong></p>
                    <p class="dato-proveedor grey-text text-darken-1" style="display: inline-block;" id="txt-nit">No establecido</p>
                </div>

                <div class="col s12 m6 no-padding">
                    <p class="" style="width: 150px;display: inline-block;"><strong>Contacto: </strong></p>
                    <p class="dato-proveedor grey-text text-darken-1" style="display: inline-block;" id="txt-contacto">No establecido</p>
                </div>

                <div class="col s12 m6 no-padding">
                    <p class="" style="width: 150px;display: inline-block;"><strong>Dirección: </strong></p>
                    <p class="dato-proveedor grey-text text-darken-1" style="display: inline-block;" id="txt-direccion">No establecido</p>
                </div>

                <div class="col s12 m6 no-padding">
                    <p class="" style="width: 150px;display: inline-block;"><strong>Teléfono: </strong></p>
                    <p class="dato-proveedor grey-text text-darken-1" style="display: inline-block;" id="txt-telefono">No establecido</p>
                </div>

                <div class="col s12 m6 no-padding">
                    <p class="" style="width: 150px;display: inline-block;"><strong>Correo: </strong></p>
                    <p class="dato-proveedor grey-text text-darken-1" style="display: inline-block;" id="txt-correo">No establecido</p>
                </div>

                <div class="col s12 right-align margin-top-30" id="contenedor-botones-cliente">
                    <a class="btn blue-grey darken-2 waves-effect waves-light modal-trigger btn-crear-proveedor" href="#modal-crear-proveedor">Crear</a>
                </div>
            </div>
        </div>
        <div class="col s12 m10 offset-m1 hide" id="contenedor-estados-compra">
            <p class="titulo-modal margin-top-40"><i class="fa fa-angle-double-down cyan white-text waves-effect waves-light btn-toggle-estados-compra" style="cursor: pointer; border-radius: 80px !important;width: 17px;text-align: center;"></i>Estados de la compra</p>
            <div class="col s12" id="estados-compra" style="display: none;">
                <div class="input-field col s12 m12 l6" style="padding: 0px;margin-bottom: 1pt">
                    <i class="material-icons prefix red-text">check_box</i>
                    {!! Form::select("estado",['Recibida'=>'Recibida','Pendiente por recibir'=>'Pendiente por recibir'],'Recibida',["id"=>"estado","class"=>"active"]) !!}
                    {!! Form::label("estado","Seleccionar estado de la compra") !!}
                </div>
                <div class="input-field col s12 m12 l6" style="padding: 0px;margin-bottom: 1pt">
                    <i class="material-icons prefix red-text">check_box</i>
                    {!! Form::select("estado_pago",['Pagada'=>'Pagada','Pendiente por pagar'=>'Pendiente por pagar'],'Pagada',["id"=>"estado_pago","class"=>"active"]) !!}
                    {!! Form::label("estado_pago","Seleccionar estado de pago") !!}
                </div>
            </div>
        </div>

        <div class="col s12 m10 offset-m1 hide" id="contenedor-detalles-compra" style="margin-top: 0px;">
            <p class="col s12">
                <input type="checkbox" id="predeterminado" name="predeterminado" />
                <label for="predeterminado" class="col s12" style="padding-left: 30px;">Predeterminado <i style="font-size: small;font-style: normal;">(Marque para utilizar los precios de este proveedor en los items asociados a esta compra)</i></label>
            </p>
            <p class="titulo-modal col s12 margin-top-10">Detalles de la compra</p>
            <div class="content-table-slide col s12">
                @include("templates.mensajes",["id_contenedor"=>"detalles-compra"])
                <table class="table highlight centered" style="min-width: 800px;" id="tabla-detalle-producto">
                    <thead style="background-color: #78909c">
                    <th>Tipo elemento</th>
                    <th>Código<br>de barrras</th>
                    <th>Nombre<br>elemento</th>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                    <th>Valor unitario [$]</th>
                    <th>Subtotal [$]</th>
                    <!--<th>IVA [%]</th>-->
                    <th></th>
                    </thead>

                    <tbody>
                    <tr>
                        <td class="radio-tipo-elemento">
                            <p style="margin-top: -25px !important;" class="left-align">
                                <input name="tipo_1" type="radio" id="tipo_1_pr" value="producto" />
                                <label for="tipo_1_pr" style="height: 4px !important;font-size: 0.85rem !important;">Producto</label>
                            </p>
                            <p class="left-align">
                                <input name="tipo_1" type="radio" id="tipo_1_mp" value="materia prima" />
                                <label for="tipo_1_mp" style="height: 4px !important;font-size: 0.85rem !important;">Materia Prima</label>
                            </p>
                        </td>
                        <td>
                            <input class="barCodeCompras" id="barCodeCompra_0" type="text" class="barcodeCompras" onchange='seleccionElemento(this.id)' onblur='seleccionElemento(this.id)' placeholder="Código de barras">
                        </td>
                        <td>
                            <input type="text" class="nombre" placeholder="Click aquí">
                            <i class="fa fa-spin fa-spinner hide" style="margin-top: -40px;"></i>
                            <input type="hidden" class="id-pr">
                        </td>

                        <td>
                            <input type="text" value="1" min="1" class="num-real center-align cantidad">
                        </td>

                        <td>
                            <p class="unidad"></p>
                        </td>

                        <td>
                            <p class="vlr-unitario" style="white-space: nowrap;">$ 0</p>
                        </td>

                        <td>
                            <p class="vlr-subtotal" style="white-space: nowrap;">$ 0</p>
                        </td>

                        <!--<td>
                            <p class="iva">0%</p>
                        </td>-->

                        <td>
                            <i class="fa fa-trash red-text text-darken-1 waves-effect waves-light" title="Eliminar elemento" style="cursor: pointer;"></i>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div class="contenedor-btn-add-elemento margin-bottom-50 right-align margin-top-20">
                    <a class="waves-effect waves-light blue-grey darken-2 btn" onclick="agregarElementoCompra(true)" >Agregar Elemento</a>
                </div>
                {!! Form::hidden("proveedor",null,["id"=>"proveedor"]) !!}
            </div>
            <div class="divider col s12 grey lighten-1" style="margin-top: -30px !important;"></div>

            <!--<div class="col s12 right-align">
                <strong class="no-margin" style="display: inline-block !important;">Subtotal:</strong>
                <p class="no-margin" style="display: inline-block !important; width: 100px;" id="txt-subtotal">$ 0,00</p>
            </div>
            <div class="col s12 right-align">
                <strong class="no-margin" style="display: inline-block !important;">Iva:</strong>
                <p class="no-margin" style="display: inline-block !important; width: 100px;" id="txt-iva">$ 0,00</p>
            </div>-->
            <div class="col s12 right-align">
                <strong class="no-margin" style="display: inline-block !important;">Total a pagar:</strong>
                <p class="no-margin" style="display: inline-block !important; width: 100px;" id="txt-total-pagar">$ 0,00</p>
            </div>
            <input type="hidden" id="efectivo_caja" value="{{ $efectivo_caja->efectivo_final }}">

            @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","compras","inicio"))
                <div class="col s12 center margin-top-40" id="contenedor-boton-realizar-compra">
                    <a class="btn waves-effect waves-light blue-grey darken-2" onclick="comprar()" >Realizar compra</a>
                </div>
            @endif
            <div class="progress hide" id="progress-compra">
                <div class="indeterminate"></div>
            </div>
        </div>
    </div>
@endsection

<div id="modal-elementos-compra" class="modal modal-fixed-footer ">
    <div class="modal-content">
        <!--

    margin-right: 20px;
    padding: 5px !Important;
    line-height: 0px;
    height: 25px;
        -->
        <!--
        <i class="fa fa-exchange right waves-effect waves-light" onclick="cambiarListaElementos()" title="Proveedor seleccionado/Otros proveedores" style="margin-right: 25px;margin-top: 5px;cursor: pointer;"></i>
        -->
        <p class="titulo-modal">
            <span title="Proveedor seleccionado/Otros proveedores" id="btn-cambiar-lista-elementos">
            <svg version="1.1" class="right hide" onclick="cambiarListaElementos"  style="margin-right: 20px;margin-top:5px;cursor: pointer;" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 width="24.063px" height="17px" viewBox="0 0 24.063 17" style="enable-background:new 0 0 24.063 17;" xml:space="preserve">
<circle style="fill:none;stroke:#000000;stroke-width:2;stroke-miterlimit:10;" cx="7.715" cy="8.532" r="5.907"/>
                <circle style="fill:none;stroke:#000000;stroke-width:2;stroke-miterlimit:10;" cx="15.883" cy="8.532" r="5.907"/>
                <g>
                    <path d="M15.883,2.625c-1.585,0-3.023,0.626-4.083,1.642c1.123,1.075,1.823,2.588,1.823,4.265c0,1.678-0.7,3.191-1.823,4.266
        c1.061,1.016,2.499,1.641,4.083,1.641c3.263,0,5.907-2.645,5.907-5.907C21.79,5.27,19.146,2.625,15.883,2.625z"/>
                </g>
</svg>

            <svg version="1.1" class="right" id="svg-cambiarListaElementos" onclick="cambiarListaElementos()" title="Proveedor seleccionado/Otros proveedores" style="margin-right: 20px;margin-top:5px;cursor: pointer;" id="Capa_2" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 width="24.063px" height="17px" viewBox="0 0 24.063 17" style="enable-background:new 0 0 24.063 17;" xml:space="preserve">
<circle style="fill:none;stroke:#000000;stroke-width:2;stroke-miterlimit:10;" cx="16.411" cy="8.31" r="6.129"/>
                <circle style="fill:none;stroke:#000000;stroke-width:2;stroke-miterlimit:10;" cx="7.937" cy="8.31" r="6.129"/>
                <g>
                    <path d="M7.937,14.439c1.645,0,3.136-0.649,4.237-1.703c-1.165-1.116-1.891-2.685-1.891-4.425c0-1.741,0.727-3.311,1.891-4.426
        c-1.101-1.054-2.592-1.703-4.237-1.703c-3.385,0-6.129,2.744-6.129,6.129C1.808,11.695,4.552,14.439,7.937,14.439z"/>
                </g>
</svg>
        </span>

            </button><span class="" id="titulo-modal-compras">Productos</span></p>
        @include('templates.mensajes',["id_contenedor"=>"elementos-compra"])
        <div id="contenedor-elementos"></div>
        <div class="col s12 progress hide" id="progress-contenedor-elementos"><div class="indeterminate"></div></div>
    </div>

    <div class="modal-footer" id="div-footer-elementos-compra">
        <div class="col s12" id="contenedor-botones-elementos-compra-modal">
            <a class="green-text btn-flat" onclick="javascript: seleccionElemento('', 'modal_productos_compra')">Aceptar</a>
            <a data-href="{{url('/productos/create?noPrOpc=1')}}" id="link-crear-producto" data-tipo-elemento="producto" class="modal-close link-crear-elemento cyan-text btn-flat">Crear producto</a>
            <a data-href="{{url('/materia-prima/create?noPrOpc=1')}}" id="link-crear-materia-prima" data-tipo-elemento="materia prima" class="modal-close link-crear-elemento cyan-text btn-flat">Crear Materia prima</a>
            <a class="modal-close cyan-text btn-flat">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-elementos-compra-modal">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>

<div id="modal-proveedores" class="modal modal-fixed-footer ">
    <div class="modal-content">
        <p class="titulo-modal" id="titulo-modal-compras">Proveedores</p>
        <div id="contenedor-proveedores" class="content-table-slide">
            <table id="ProveedoresTabla" style="width:100%;" class="bordered highlight centered">
                <thead>
                    <tr>
                        <th></th>
                        <th>NIT</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-proveedores">
            {{--<a class="green-text btn-flat" onclick="javascript: seleccionProveedor()">Aceptar</a>--}}
            <a class="modal-close cyan-text btn-flat">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-proveedores">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>

<div id="modal-crear-proveedor" class="modal modal-fixed-footer ">
    <div class="modal-content">
        <p class="titulo-modal" id="titulo-modal-compras">Crear proveedor</p>
        <div id="contenedor-crear-proveedor" class="row">
            @include("templates.mensajes",["id_contenedor"=>"crear-proveedor"])
            @include('proveedor.form',["modal"=>true])
        </div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-crear-proveedor">
            <a class="green-text btn-flat" onclick="javascript: crearProveedor()">Aceptar</a>
            <a class="modal-close cyan-text btn-flat">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-crear-proveedor">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>

<div id="modal-edit-precio" class="modal modal-fixed-footer modal-small">
    <div class="modal-content">
        <p class="titulo-modal" id="titulo-modal-compras">Editar valor del producto</p>
        @include('templates.mensajes',["id_contenedor"=>"editar-valor-producto"])
        <div id="contenedor-form-edit-precio"></div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-edit-precio-modal">
            <a class="green-text btn-flat" onclick="cambiarPrecioProducto()">Aceptar</a>
            <a class="modal-close cyan-text btn-flat">Cerrar</a>
        </div>

        <div class="progress hide" id="progress-edit-precio-modal">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>

<div id="modal-edit-precio-materia-prima" class="modal modal-fixed-footer">
    <div class="modal-content">
        <p class="titulo-modal" id="titulo-modal-compras">Editar valor de la materia prima</p>
        @include('templates.mensajes',["id_contenedor"=>"editar-valor-producto"])
        <div id="contenedor-form-edit-precio-materia-prima"></div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-edit-precio-materia-prima-modal">
            <a class="green-text btn-flat" onclick="cambiarPrecioMateriaPrima()">Aceptar</a>
            <a class="modal-close cyan-text btn-flat">Cerrar</a>
        </div>

        <div class="progress hide" id="progress-edit-precio-materia-prima-modal">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>
@include('compras.abonos.forma_pago')



<div id="modal-cuadre-caja" class="modal modal-fixed-footer" >
    <div class="modal-content">
        <p class="titulo-modal">Operaciones de caja</p>
        <div id='mensaje-confirmacion-cuadre-caja'></div>
        {!! Form::open([ 'url' => 'caja/operacion-caja' ,'data-toggle'=>'validator', 'class' => 'form-inline','role'=> 'form','method' => 'POST', 'novalidate', 'id' => 'form-caja',  'autocomplete' =>'off'] ) !!}
        @include('compras.abonos.form_entrar_dinero_caja')
        {!! Form::close() !!}
    </div>
    <div class="modal-footer">
        <div class="progress hide" id="progress-action-form-cuadre-caja" style="top: 30px;margin-bottom: 30px;">
            <div class="indeterminate cyan"></div>
        </div>
        <div class="col s12" id="contenedor-botones-cuadre-caja">
            <a href="#!" class="modal-close cyan-text btn-flat" onclick="cerrarModalCuadreCaja()">Cerrar</a>
        </div>
    </div>
</div>


<div id="modal-relacion-proveedor" class="modal modal-fixed-footer modal-small" style="height: 370px;min-height: 370px;">
    <div class="modal-content">
        <p class="" id="titulo-modal-compras">Ingrese los precios del proveedor seleccionado</p>
        @include('templates.mensajes',["id_contenedor"=>"relacion-proveedor"])
        <?php
        $admin = \App\User::find(\Illuminate\Support\Facades\Auth::user()->userAdminId());
        ?>
        {!!Form::open(["id"=>"form-relacion-proveedor"])!!}
        <div class="row" style="padding: 20px;">

            <div class="input-field col s12 m6">
                {!!Form::label("precio_costo_proveedor","Precio costo",["class"=>"active"])!!}
                {!!Form::text("precio_costo_proveedor",null,["id"=>"precio_costo","class"=>"num-entero","placeholder"=>"Ingrese precio costo","maxlength"=>"100"])!!}
            </div>
            @if($admin->regimen == "común")
                <div class="input-field col s12 m6">
                    {!!Form::label("iva_proveedor","Iva % en compra",["class"=>"active"])!!}
                    {!!Form::text("iva_proveedor",null,["id"=>"iva","class"=>"num-real","placeholder"=>"Ingrese iva","maxlength"=>"100"])!!}
                </div>
            @endif

            @if(\Illuminate\Support\Facades\Auth::user()->bodegas == 'no')
                <div class="input-field col s12 m6">
                    {!!Form::label("utilidad_proveedor","Utilidad %",["class"=>"active"])!!}
                    {!!Form::text("utilidad_proveedor",null,["id"=>"utilidad","class"=>"num-real","placeholder"=>"Ingrese la utilidad","maxlength"=>"5"])!!}
                </div>
                <div class="input-field col s12 m6">
                    {!!Form::label("precio_venta_proveedor","Precio venta al público",["class"=>"active"])!!}
                    {!!Form::text("precio_venta_proveedor",null,["id"=>"precio_venta","class"=>"num-entero","placeholder"=>"Ingrese el precio de venta al público","maxlength"=>"100"])!!}
                </div>
            @endif
        </div>
        {!! Form::close() !!}
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-relacion-proveedor-modal">
            <a class="green-text btn-flat" onclick="relacionarProductoProveedor()">Aceptar</a>
            <a class="modal-close cyan-text btn-flat">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-relacion-proveedor-modal">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>

<div id="modal-relacion-proveedor-mp" class="modal modal-fixed-footer modal-small" style="height: 370px;min-height: 370px;">
    <div class="modal-content">
        <p class="" id="titulo-modal-compras">Ingrese el valor del proveedor seleccionado</p>
        {!!Form::open(["id"=>"form-relacion-materia-prima"])!!}
        <div class="row" style="padding: 20px;">

            <div class="input-field col s12 m12">
                {!!Form::label("valor","Valor",["class"=>"active"])!!}
                {!!Form::text("valor",null,["id"=>"valor","class"=>"num-real","placeholder"=>"Valor","maxlength"=>"10"])!!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-relacion-proveedor-modal">
            <a class="green-text btn-flat" onclick="relacionarProveedoresMateriaPrima()">Aceptar</a>
            <a class="modal-close cyan-text btn-flat">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-relacion-proveedor-modal">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>
@section('js')
    @parent

    <script src="{{asset('js/productos/funciones.js')}}"></script>

    <script>
        $(function(){
            if(localStorage.loadPedido){
                localStorage.setItem("deletePedido",true);
                loadPedido();
            }else{
                if(localStorage.deletePedido){
                    localStorage.removeItem("deletePedido");
                }
            }
        })
    </script>
@stop
