<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        <p class="titulo">Crear remisión de ingreso</p>
        @include("templates.mensajes",["id_contenedor"=>"crear-remision-ingreso"])

        <div class="col s12 " id="contenedor-detalles-remision-ingreso" style="margin-top: 0px;">
            <p class="titulo-modal col s12 margin-top-10">Detalles de la remisión</p>
            <div class="content-table-slide col s12">
                @include("templates.mensajes",["id_contenedor"=>"detalles-remision-ingreso"])
                <table class="table highlight centered" style="min-width: 800px;" id="tabla-detalle-producto-remision">
                    <thead style="background-color: #78909c">
                    <th>Tipo elemento</th>
                    <th>Código<br>de barrras</th>
                    <th>Nombre<br>elemento</th>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                    <th>Valor unitario [$]</th>
                    <!--<th>Subtotal [$]</th>-->
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
                                <label for="tipo_1_mp" style="height: 4px !important;font-size: 0.85rem !important;">Materia P</label>
                            </p>
                        </td>
                        <td>
                            <input class="barCodeRemisionesIngreso" id="barCodeRemision_0" type="text" class="barcodeRemisionesIngreso" onblur='seleccionElemento(this.id,"barCodeProductosRemisiones")' placeholder="Código de barras">
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

                        <!--<td>
                            <p class="vlr-subtotal" style="white-space: nowrap;">$ 0</p>
                        </td>-->

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
                    <a class="waves-effect waves-light blue-grey darken-2 btn" onclick="agregarElementoRemision(true)" >Agregar Elemento</a>
                </div>
            </div>
            <div class="divider col s12 grey lighten-1" style="margin-top: -30px !important;"></div>


            @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","Remision ingreso","inicio"))
                <div class="col s12 center margin-top-40" id="contenedor-boton-realizar-remision-ingreso">
                    <a class="btn waves-effect waves-light blue-grey darken-2" onclick="realizarRemision()" >Registrar remisión</a>
                </div>
            @endif
            <div class="progress hide" id="progress-remision-ingreso">
                <div class="indeterminate"></div>
            </div>
        </div>
    </div>
@endsection

<div id="modal-elementos-remision-ingreso" class="modal modal-fixed-footer ">
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
        </button><span class="" id="titulo-modal-remisiones-ingreso">Productos</span></p>
        @include('templates.mensajes',["id_contenedor"=>"elementos-remision-ingreso"])
        <div id="contenedor-elementos"></div>
        <div class="col s12 progress hide" id="progress-contenedor-elementos"><div class="indeterminate"></div></div>
    </div>

    <div class="modal-footer" id="div-footer-elementos-remision-ingreso">
        <div class="col s12" id="contenedor-botones-elementos-remision-ingreso-modal">
            <a class="green-text btn-flat" onclick="javascript: seleccionElemento('', 'modal_productos_remision_ingreso')">Aceptar</a>
            <a class="modal-close cyan-text btn-flat">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-elementos-remision-ingreso-modal">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>

<div id="modal-edit-precio" class="modal modal-fixed-footer modal-small">
    <div class="modal-content">
        <p class="titulo-modal" id="titulo-modal-remisiones-ingreso">Editar valor del producto</p>
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
        <p class="titulo-modal" id="titulo-modal-remisiones-ingreso">Editar valor de la materia prima</p>
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

@section('js')
    @parent
    <script src="{{asset('js/productos/funciones.js')}}"></script>
    <script src="{{asset('js/remisiones_ingreso/index.js')}}"></script>
@stop
