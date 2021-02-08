@section('header')
@show
<!DOCTYPE html>
<!--<html manifest="cache.manifest">-->
<html>
    <head>
        <title>
            @section("titulo")
                Vendiendo.co - Facturación en Línea
            @show
        </title>

        <meta name="viewport" content="width=device-width, initial-scale=1">

        @section('css')
            <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
             <!-- Compiled and minified CSS -->
            <link rel="stylesheet" href="{{ asset('materialize/css/materialize.min.css') }}">

            <!--Import Google Icon Font-->
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
            <link rel="stylesheet" href="{{ asset('css/global.css') }}">
            <link rel="stylesheet" href="{{ asset('css/impresion.css') }}">
            <link rel="stylesheet" href="{{ asset('css/menus.css') }}">
            <link rel="stylesheet" href="{{ asset('css/propiedades.css') }}">
            <link rel="stylesheet" href="{{ asset('css/flatpickr.min.css') }}">
        @show

        <style>
            .breadcrumb:before {
                font-size: 18px !important;
                line-height: 41px !important;
                margin-left: 5px !important;
                margin-right: 5px !important;
            }
            #nav-breadcrump{
                /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#aaaaaa+28,a8a8a8+100&0.74+0,0.45+100 */
                background: -moz-linear-gradient(-45deg,  rgba(170,170,170,0.74) 0%, rgba(170,170,170,0.66) 28%, rgba(168,168,168,0.45) 100%); /* FF3.6-15 */
                background: -webkit-linear-gradient(-45deg,  rgba(170,170,170,0.74) 0%,rgba(170,170,170,0.66) 28%,rgba(168,168,168,0.45) 100%); /* Chrome10-25,Safari5.1-6 */
                background: linear-gradient(135deg,  rgba(170,170,170,0.74) 0%,rgba(170,170,170,0.66) 28%,rgba(168,168,168,0.45) 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
                filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#bdaaaaaa', endColorstr='#73a8a8a8',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */

            }

            .progress{
                height: 2px !important;
            }
        </style>
        @if(\Illuminate\Support\Facades\Auth::check())
        <script>
            mRoomid = '{{\Illuminate\Support\Facades\Auth::user()->userAdminId()}}';
            mUsername = '{{ \Illuminate\Support\Facades\Auth::user()->nombres  }}';
            if(localStorage.getItem("state") === null){
                localStorage.setItem("state","1");
                window.location.reload();
            }else{
                if(localStorage.state != "1"){
                    localStorage.setItem("state","1");
                    window.location.reload();
                }
            }

        </script>
        @endif

        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript" src="{{ asset('js/jquery-2.2.3.min.js') }}"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script type="text/javascript" src="{{ asset('js/jquery.ui.touch-punch.min.js') }}"></script>
        <!-- Compiled and minified JavaScript -->
        <script src="{{ asset('materialize/js/materialize.min.js') }}"></script>
        <script src="{{ asset('js/global.js') }}"></script>
        <script>
            $(function () {
                setLengthRowsTables({{Auth::user()->length_rows_tables}});
            })
        </script>

    {!! Html::script('librerias/datatable/js/jquery.dataTables.min.js') !!}
    {!! Html::style('librerias/datatable/css/jquery.dataTables.css') !!}
    </head>
    <body onload="cargarPedido()">
    <div id="background"></div>

        @if(\Illuminate\Support\Facades\Session::has("mensaje_validacion"))
            <div class="z-depth-2 validacion-ruta">
                <i class="fa fa-times-circle red-text text-darken-2 right close-validacion-ruta"></i>
                {!!\Illuminate\Support\Facades\Session::get("mensaje_validacion")!!}
            </div>
        @endif
        <?php
            if(!isset($render_cuadre_caja)) $render_cuadre_caja = true;
        ?>
        @if(\Illuminate\Support\Facades\Session::has("mensaje_validacion_caja") && $render_cuadre_caja)
            <div id="contenedor-validacion-caja">
                <div class="z-depth-2 validacion-ruta">
                    <i class="fa fa-times-circle red-text text-darken-2 right close-validacion-ruta-caja"></i>
                    <p style="padding: 0px;font-weight: 500;">{!!\Illuminate\Support\Facades\Session::get("mensaje_validacion_caja")!!}</p>
                </div>
            </div>
        @endif
        @if(\Illuminate\Support\Facades\Session::has("mensaje-factura-abierta"))
            <div class="z-depth-2 validacion-ruta" style="z-index: 10000;">
                <i class="fa fa-times-circle red-text text-darken-2 right close-validacion-ruta"></i>
                {!!\Illuminate\Support\Facades\Session::get("mensaje-factura-abierta")!!}
            </div>
        @endif

        <?php
            $uni=$cat=$pro=$res=1;
                if (isset($unidades) && isset($categorias) && isset($proveedores) && isset($resoluciones)){
                    $uni = $unidades;
                    $cat = $categorias;
                    $pro = $proveedores;
                    $res = $resoluciones;
                }

                if(!isset($display_factura_abierta))$display_factura_abierta = true;
                if(!isset($display_compra_js))$display_compra_js = true;

            $caja_asignada = null;
            if(Auth::check())
            $caja_asignada = \Illuminate\Support\Facades\Auth::user()->cajaAsignada();
        ?>

        @section('encabezado')
            @if(\Illuminate\Support\Facades\Auth::check())
                @if(!isset($noPrOpc))
                    <div class="row" style="position: fixed;z-index: 100 !important;">
                        <div id="contenedor-botones-encabezado"style="pointer-events: none;">
                            <?php

                                $perfil = \Illuminate\Support\Facades\Auth::user()->perfil;
                                $terminos_condiciones = true;
                                if ($perfil->nombre != "superadministrador" ){
                                    $admin = \App\User::find(Auth::user()->userAdminId());
                                    if ($admin->terminos_condiciones != "si"){
                                        $terminos_condiciones = false;
                                    }
                                }
                            ?>
                            @if($terminos_condiciones)

                                <a style="pointer-events: auto;" class='focus-tecla btn waves-effect waves-light blue-grey darken-2 btn-home' href='{{url("/")}}'><i class="fa fa-home"></i></a>

                                @if ($uni > 0 && $cat > 0 && $pro > 0 && $res > 0)
                                    @if(Auth::user()->configuracionCambioAB())
                                        <a style="pointer-events: auto;" class='focus-tecla btn waves-effect waves-light blue-grey darken-2 btn-menu-app hide-on-med-and-down' href='#' id="menu-desplegable"><i class="fa fa-bars"></i></a>
                                    @endif
                                @endif
                                <a style="pointer-events: auto;" class='focus-tecla dropdown-button btn waves-effect waves-light blue-grey darken-2 btn-menu-usuario' href='#!' data-activates='dropdown1'>{{\Illuminate\Support\Facades\Auth::user()->alias}}</a>

                                @if(Auth::user()->configuracionCambioAB())
                                    <a id="btn-notificaciones" style="pointer-events: auto;" class="focus-tecla btn btn-floating blue-grey darken-2 lighten-2 waves-effect waves-light blue-grey darken-2 btn-notificaciones" href="#!"><i id="icono-campana" class="fa fa-bell"></i><i id="numero-notificaciones" class="grey-text darken-text-4"></i></a>
                                @endif

                                @if(\Illuminate\Support\Facades\Auth::user()->plan()->factura_abierta == "si" && \App\Models\Caja::cajasPermitidas()->where("fecha",date("Y-m-d"))->get()->count() && $display_factura_abierta && \Illuminate\Support\Facades\Auth::user()->adminFacturaAbierta() == "si")
                                    @if(Auth::user()->configuracionCambioAB())
                                        <a id="btn-factura-abierta" style="pointer-events: auto;" class="focus-tecla btn btn-floating blue-grey darken-2 lighten-2 waves-effect waves-light blue-grey darken-2 btn-factura-abierta modal-trigger" href="#modal-factura-abierta"><i id="" class="fa fa-wpforms" style="font-size: 18px;"></i></a>
                                    @endif
                                @endif
                                @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Pedido","Búsqueda en proveedor","inicio"))
                                    @if(Auth::user()->configuracionCambioAB())
                                        <a id="btn-pedidos-proveedor" style="pointer-events: auto;" class="hide btn btn-floating blue-grey darken-2 lighten-2 waves-effect waves-light blue-grey darken-2" href="#!"><i id="" class="fa fa-shopping-cart" style="font-size: 18px;"></i></a>
                                    @endif
                                @endif
                                @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","facturas","inicio") && \Illuminate\Support\Facades\Auth::user()->cajaAsignada())
                                    @if(Auth::user()->configuracionCambioAB())
                                        <a style="pointer-events: auto;" class="focus-tecla btn btn-floating blue-grey darken-2 lighten-2 waves-effect waves-light blue-grey darken-2" href="{{url('/factura/create')}}" tit><i id="" class="fa fa-plus" style="font-size: 18px;"></i></a>
                                    @endif
                                @endif
                            @else
                                <a style="pointer-events: auto;" class='focus-tecla dropdown-button btn waves-effect waves-light blue-grey darken-2 btn-menu-usuario' href='#!' data-activates='dropdown1'>{{\Illuminate\Support\Facades\Auth::user()->alias}}</a>
                            @endif
                        </div>
                         @include('menu.menus')
                    </div>
                @endif
            @endif
        @show
        @include('templates.notificaciones.index')
        <div class="row padding-10" id="contenedor-principal">
            @if(!isset($noPrOpc))
                <?php
                    if(!isset($size_medium))$size_medium = "m10 offset-m1";
                ?>
                @if(Auth::user()->configuracionCambioAB())
                    <nav class="col s12 {{$size_medium}} no-padding hide" id="nav-breadcrump" style="margin-top: 50px !important;box-shadow: 0px 0px 0px 0px;margin-bottom: -100px !important;height: 35px; line-height: 38px;">
                            <div class="nav-wrapper">
                                @if(\Illuminate\Support\Facades\Session::has("mensaje_validacion_session_demo"))
                                    <div class="col s6 offset-s6" style="position: absolute;color: red">
                                        {!!\Illuminate\Support\Facades\Session::get("mensaje_validacion_session_demo")!!}
                                    </div>
                                @endif

                                <div class="col s12" id="content-breadcrumb">

                                </div>
                            </div>
                    </nav>
                @endif
            @endif
            <div class="row">
                @section('contenido')
                @show
            </div>
            <div class="toast-vendiendo z-depth-3">
                <a href="#!" id="btn-cerrar-toast" class="right red-text"><i class="fa fa-times-circle"></i></a>
                <p id="titulo" class="truncate">Confirmación</p>
                <p id="mensaje">Mesaje de confirmación vendiendo.co </p>
            </div>
        </div>

        <div class="toast-numericos hide">
            <p id="valor">$ 0</p>
        </div>
        {!!Form::hidden(null,csrf_token(),["id"=>"general-token"])!!}
        {!!Form::hidden(null,url("/"),["id"=>"base_url"])!!}

        <div class="footer-fixed">
            @if($caja_asignada)
                <h5 class="left z-depth-1 padding-10 grey-text" style="margin-top: 0px; background-color: rgba(255,255,255,0.9)">
                    @if(Auth::user()->bodegas == 'si')
                        @if(Auth::user()->almacenActual())
                            <p class="center" style="font-size: small;padding: 0px;margin: 0px;"><strong>Almacén:</strong> {{Auth::user()->almacenActual()->nombre}}</p>
                        @endif
                    @endif
                    {{$caja_asignada->nombre." - ".$caja_asignada->prefijo}}
                    <p class="center" style="font-size: small;padding: 0px;margin: 0px;">{{" (".$caja_asignada->estado.")"}}</p>
                </h5>
            @endif
        </div>
        @section('pie-pagina')
        @show

        @if(Auth::check())
            @if(\Illuminate\Support\Facades\Auth::user()->plan()->factura_abierta == "si" && \App\Models\Caja::cajasPermitidas()->where("fecha",date("Y-m-d"))->get()->count() && $display_factura_abierta)
                <div id="modal-factura-abierta" class="modal modal-fixed-footer s12" style="width: 80% !important;min-height: 85% !important;">
                    <div class="modal-content">

                        <p class="titulo-modal">Venta rápida
                            <a href="#modal-eliminar-productos" class="modal-trigger cyan-text" title="Elimina todos los productos en la factura actual" onclick="id_select = 1"><i class="fa fa-trash fa-1x" style="cursor: pointer;"></i></a>
                            <a target="_blank" href="{{url('/factura/detalle-factura-abierta')}}" class="fa fa-list-alt cyan-text right tooltipped" data-position="bottom" data-delay="50" data-tooltip="Detalle factura abierta" style="line-height: 35px;font-size: 20px;"></a>
                        </p>
                        <div id="contenido-factura-abierta" style="width: 100%" style="min-height: 80% !important;">
                            <div class="content-table-slide col s12">
                                @include('factura.detalle_factura')
                                {{-- <div class="divider col s12 grey lighten-1" style="margin-top: -30px !important;"></div>       --}}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="height: 80px !important;">

                        <div class="row">
                            <div class="col s6 m4" style="font-size: 12px !important;">
                                <div class="col s12 left-align hide">
                                    <strong class="no-margin" style="display: inline-block !important;">Subtotal:</strong>
                                    <p class="no-margin" style="display: inline-block !important; width: 150px;" id="txt-subtotal">$ 0,00</p>
                                </div>
                                <div class="col s12 left-align hide">
                                    <strong class="no-margin" style="display: inline-block !important;">IVA:</strong>
                                    <p class="no-margin" style="display: inline-block !important; width: 150px;" id="txt-iva">$ 0,00</p>
                                </div>
                                <div class="col s12 left-align">
                                    <strong class="no-margin" style="padding-top: 10px; font-size: 20px; display: inline-block !important;">Total:</strong>
                                    <p class="total no-margin" style="padding-top: 10px; font-size: 20px; display: inline-block !important; width: 150px;" id="txt-total-pagar">$ 0,00</p>
                                </div>
                            </div>
                            <div class="col s6 m8 right-align" id="contenedor-botones-factura-abiera" >

                                <div class="col s12 m12" id="contenedor-botones-factura-abiera">
                                    <a onclick="generarFacturaAbierta()" href="#!" class="cyan-text btn-flat">Generar factura</a>
                                    <a onclick="venderFacturaAbierta()" href="#!" class="cyan-text btn-flat">Vender</a>
                                    <a href="#!" id="btnCerrarFacturaAbierta" class="modal-close cyan-text btn-flat">Cerrar</a>
                                    <a class="cyan-text btn-flat tooltipped" data-position="top" data-delay="50" data-tooltip="Abre una nueva pestaña de vendiendo.com para facturar, dejando la actual en espera." id="btnNuevaFactura"  onclick="nuevaFacturaAbierta()" >APLAZAR ESTA VENTA</a>
                                </div>
                                <div class="col s12 progress hide"  id="progress-detalle-factura-abierta">
                                    <div class="indeterminate cyan"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 progress hide" id="progress-detalle-factura-abierta">
                            <div class="indeterminate cyan"></div>
                        </div>
                    </div>
                </div>

                <div id="modal-pagar" class="modal modal-fixed-footer modal-sm modal-small" style="height: 400px !important;min-height: 400px;">
                    <div class="modal-content">
                        <p class="titulo-modal">Pagar</p>
                        <div class="col s12">
                            <strong>Total a pagar </strong>
                            <p id="total-pagar-modal">$ 0</p>
                        </div>
                        <div class="col s12">
                            <strong>Efectivo </strong>
                            {!!Form::text("efectivo-modal",null,["id"=>"efectivo-modal","maxlength"=>"10","class"=>"num-entero"])!!}
                        </div>
                        <div class="col s12">
                            <strong>Regreso</strong>
                            <p id="regreso-modal">$ 0</p>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <div class="col s12" id="contenedor-botones-pagar-modal">
                            <a class="green-text btn-flat" onclick="javascript: validPagarFacturaAbierta()">Realizar pago</a>
                            <a class="green-text btn-flat" onclick="javascript: facturarFacturaAbierta(true)">Omitir calculo</a>
                            <a class="modal-close cyan-text btn-flat">Cancelar</a>
                        </div>

                        <div class="progress hide" id="progress-pagar-modal">
                            <div class="indeterminate cyan"></div>
                        </div>
                    </div>
                </div>

                <div id="modal-detalles-factura" class="modal modal-fixed-footer " style="width: 75% !important; min-height: 75% !important; font-size: 12px;">
                    <div class="modal-content">
                        <p class="titulo-modal" style="margin:0px !important;">Productos</p>
                        {{-- <i class="fa fa-filter right waves-effect waves-light" title="Filtros de busqueda" style="cursor: pointer;margin-top: -40px;" onclick="javascript: $('#contenedor-filtros-detalles-factura').slideToggle(500)"></i> --}}

                        <div id="contenedor-productos-modal" class="col s12"></div>
                    </div>

                    <div class="modal-footer" id="footer-modal-detalles-factura">
                        <div class="col s12" id="contenedor-botones-detalles-factura-modal">
                            <a class="green-text btn-flat" id="btnSeleccionProducto">Aceptar</a>
                            <a class="modal-close cyan-text btn-flat">Cancelar</a>
                        </div>

                        <div class="progress hide" id="progress-detalles-factura-modal">
                            <div class="indeterminate cyan"></div>
                        </div>
                    </div>
                </div>

                <div id="modal-eliminar-productos" class="modal modal-fixed-footer modal-small" style="min-height: 50% !important;">
                    <div class="modal-content">
                        <p class="titulo-modal">Eliminar Productos</p>
                        <b>Estas a punto de eliminar todos los productos que contiene esta factura!!!</b>
                        <p>¿Seguro deseas hacerlo?</p>
                    </div>

                    <div class="modal-footer">
                        <div class="col s12" id="contenedor-botones-eliminar-productos">
                            <a href="#!" class="red-text btn-flat" onclick="limpiarFactura()">Aceptar</a>
                            <a href="#!" id="btnCancelarEliminacionProductos" class="modal-close cyan-text btn-flat">Cancelar</a>
                        </div>

                        <div class="progress hide" id="progress-eliminar-productos">
                            <div class="indeterminate cyan"></div>
                        </div>
                    </div>
                </div>
            @endif

            <div id="modal-detalle-carrito" class="modal modal-fixed-footer s12 m6">
                <div class="modal-content">
                    <p class="titulo-modal">Detalle del pedido</p>
                    <div id="contenido-detalle-carrito" style="width: 100%">

                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col s12 m6" id="contenedor-botones-detalle-pedido">
                        <!--<a href="#!" class="red-text btn-flat" onclick="javascript: detalleProducto(id_select)">Aceptar</a>-->
                        <a onclick="javascript: localStorage.setItem('loadPedido',true);" href="{{url('/factura/create')}}" class="cyan-text btn-flat">Generar factura</a>
                        <a href="#!" class="modal-close cyan-text btn-flat">Cerrar</a>
                        <a href="#!" class="modal-close cyan-text btn-flat" onclick="descartarPedido()">Vaciar carrito</a>
                    </div>
                </div>
            </div>

            @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Pedido","Búsqueda en proveedor","inicio"))
                <div id="modal-detalle-pedido-proveedor" class="modal modal-fixed-footer s12 m6">
                    <div class="modal-content">
                        <p class="titulo-modal">Detalle del pedido</p>
                        @include('templates.mensajes',["id_contenedor"=>"detalle-pedido"])
                        <div id="contenido-detalle-pedido-proveedor">

                        </div>
                    </div>
                    <div class="modal-footer">
                            <!--<a href="#!" class="red-text btn-flat" onclick="javascript: detalleProducto(id_select)">Aceptar</a>-->
                        <a onclick="enviarPedidoProveedor()" href="#!" class="cyan-text btn-flat">Enviar pedido</a>
                        <a href="#!" class="modal-close cyan-text btn-flat" onclick="quitarProductosPedidoProveedor()">Quitar productos</a>
                        <a href="#!" class="modal-close cyan-text btn-flat">Cerrar</a>
                    </div>
                </div>
            @endif
        @endif

        <div id="modal-mensaje-demo" class="modal">
            <div class="modal-content">
                <h4 style="color: red"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Mensaje de advertencia </h4>
                <p id="mensaje-cuerpo">

                </p>
            </div>
            <div class="modal-footer">
                <a href="#!" class=" modal-action modal-close waves-effect waves-green btn-flat">Aceptar</a>
            </div>
        </div>

        @section('js')
            <script src="{{ asset('js/pedidos/funciones.js') }}"></script>
            <script src="https://use.fontawesome.com/825447006e.js"></script>
            <script src="{{ asset('js/validacion_numeric.js') }}"></script>
            <script src="{{ asset('js/jquery.numeric.js') }}"></script>
            <script src="{{ asset('js/Numericos.js') }}"></script>
            <script src="{{ asset('js/notificaciones.js') }}"></script>
            <script src="{{ asset('js/jquery.blockUI.js') }}"></script>
            <script src="{{asset('js/sms/flatpickr.js')}}"></script>
            <script src="{{asset('js/sms/idioma/flatpickr.l10n.es.js')}}"></script>
            @if($display_compra_js)
                <script src="{{asset('js/comprasAction.js')}}"></script>
            @endif
            <script>

                function limpiarFactura(){
                    $('#tabla-detalle-producto tbody tr').each(function () {
                        $(this).closest('tr').remove();
                    });
                    agregarElementoFactura()
                    $("#btnCancelarEliminacionProductos").click()
                    $("#txt-total-pagar").html("$ 0,00");
                }

                $(function(){

                    var urlCrearFactura = '{{url('factura/create')}}';
                    var URLactual = window.location;

                    if(URLactual != urlCrearFactura) {
                        if(sessionStorage.lista_factura_abierta) {
                           sessionStorage.clear();
                        }
                        $("#btn-limpiar-lista-productos").addClass('hide');
                    }

                    $("#btn-factura-abierta").click(function () {
                        $("#btn-limpiar-lista-productos").remove();
                    })
                    $('body').on('update', function(){
                        //alert('Table updated, launching kill space goats sequence now.')
                        $(".text-center").css("font-size", tamano_fuente+"px");
                    });
                    /*<a href="#!" class="breadcrumb font-small">First</a>
                     <a href="#!" class="breadcrumb font-small">Second</a>
                     <a href="#!" class="breadcrumb font-small">Third</a>*/
                    //var url = window.location.pathname;
                    var home = "<a href='"+window.location.origin+"' class='breadcrumb font-small'>Inicio</a>";

                    //var home = "<a href='"+window.location.origin+"/instalaciones_claro/' class='breadcrumb font-small'>Inicio</a>";

                    var path = window.location.pathname;
                    path = path.substr(1,path.length);
                    var links = "";
                    var lastUrl = window.location.origin;

                    //var lastUrl = window.location.origin+"/instalaciones_claro";

                   // path = path.replace("instalaciones_claro/","");

                    if(path.length > 0) {
                        for (var i = 0; i < path.split("/").length; i++) {
                            if(path.split('/')[(i+1)] && $.isNumeric(path.split('/')[(i+1)])){
                                links += "<a href='"+lastUrl+"/"+path.split('/')[i]+"/"+path.split('/')[(i+1)]+"' class='breadcrumb font-small'>"+path.split('/')[i].replace("-"," ")+"</a>";
                                break;
                            }else {
                                if(path.split('/')[i] != "home") {
                                    var get="";
                                    if((i+1) == path.split("/").length)
                                        get = window.location.search;
                                    links += "<a href='" + lastUrl + "/" + path.split('/')[i] +  get +  "' class='breadcrumb font-small'>" + path.split('/')[i].replace("-"," ") + "</a>";
                                    lastUrl = lastUrl + "/" + path.split('/')[i];
                                }
                            }
                        }
                    }

                    if(links.length) {
                        $("#content-breadcrumb").html(home + links+"<div id='Prueba' style='position: relative; color:black; float: right;'><input id='ColorBtn' value='#455a64' title='Personalice el color de fondo de los botones' style='border-radius: 2px; border:none; margin:5px; height: 22px; padding:0px; width: 20px;' type='color'/><input type='button' value='A' value='A' class='darken-2' id='AumentaFuente'  style='font-size: 16px; background-color:rgba(0,0,0,0.1); border-radius: 2px; border:none; padding:7px;'  /> <input type='button' value='A'  id='DisminuyeFuente' class=' darken-2' style='font-size: 12px; background-color:rgba(0,0,0,0.1); border-radius: 2px; border:none; padding:7px;' /></div>");

                        $( "#AumentaFuente" ).click(function() {
                            tamano_fuente+=1;
                            if(tamano_fuente>35)
                                tamano_fuente=35;
                            ActualizarIDB();
                            console.log('Tamano:'+tamano_fuente);

                        });
                        $( "#DisminuyeFuente" ).click(function() {
                            tamano_fuente-=1;
                            if(tamano_fuente<10)
                                tamano_fuente=10;
                            ActualizarIDB();
                            console.log('Tamano:'+tamano_fuente);
                        });
                        $("#ColorBtn").change(function (){
                            color_botones = $("#ColorBtn").val();
                            $('.blue-grey.darken-2').attr('style', function(i,s) {
                                if(s === undefined || s === null)
                                    s='';
                                return s + 'background-color:'+ color_botones +' !important;';
                            });
                            ActualizarIDB();
                            console.log("Color:"+color_botones);
                        });

                        $("#nav-breadcrump").removeClass("hide");
                    }else{
                        $("#nav-breadcrump").addClass("hide");
                    }
                    if(localStorage.loadPedido && path != "factura/create" ){
                        localStorage.removeItem("loadPedido");
                    }

                    setInterval(checkSession, 10000);
                    //valida si e demo o produccion

                    if ('{{ config('options.version_proyecto') }}' == 'DEMO'){
                        var rango_visualizacion_mensaje_demo = '{{ config('options.rango_visualizacion_mensaje_demo') }}' *  60000;
                        setInterval(validarFechaDemo, rango_visualizacion_mensaje_demo);
                    }
                    $(document).on('click',"body", function(event){
                        // alert("Entra");
                        if($("#base_url").val()!= undefined) {
                            //console.log("Click");
                            $.post($("#base_url").val() + "/session/resetConteo", {"_token": $("#general-token").val()}, function (data) {
                              //  console.log("Enviado...");
                            });
                        }
                        //checkSession();
                    });

                    @if(\Illuminate\Support\Facades\Session::has("mensaje-factura-abierta"))
                        $("#modal-factura-abierta").openModal();
                    @endif
                })

                function checkSession() {

                    $.post('{{ route('session.ajax.check') }}', { '_token' : '{!! csrf_token() !!}' }, function(data) {
                        if (data == 'Retirado') {
                            // User was logged out. Redirect to login page
                            lanzarNotificacion(data);
                            document.location.href = '{{  url("/") }}';
                        }else if (data == 'Duplicado') {
                            // User was logged out. Redirect to login page
                            lanzarNotificacion("Se intentó iniciar sesión desde otro dispositivo, por seguridad se cerró la sesión.");
                            document.location.href = '{{  url("/") }}';
                        }
                        else if (data != '') {
                            // User will be logged out soon.
                            // TODO display proper modal, instead of console.log()
                            //console.log(data);
                            lanzarNotificacion(data);
                            //alert(data);
                        }
                    });
                }
                function validarFechaDemo() {
                    //console.log("Verificando estado del demo "+ '{{ route('session.estado.demo') }}');
                    $.post('{{ route('session.estado.demo') }}', { '_token' : '{!! csrf_token() !!}' }, function(data) {
                        console.log(data);
                        var vinculo = "<a href='http://vendiendo.co' target='_blank'>Vendiendo.co</a>"
                        if (data <= 15){
                            $("#modal-mensaje-demo").openModal();
                            $("#mensaje-cuerpo").html("<h5>Tu periodo de prueba terminará en "+ data+ " minutos</h5>Si quieres seguir disfrutando de <b>Vendiendo.co</b>, es necesario" +
                                    "que adquieras uno de nuestros planes que ofrece <br>Puedes visitarnos en "+vinculo);
                        }
                        if (data == 0){
                            $("#modal-mensaje-demo").closeModal();
                            window.location.reload(true);
                        }
                    })

                }
            </script>
            @if(Auth::check() && \Illuminate\Support\Facades\Auth::user()->plan()->factura_abierta == "si" && \App\Models\Caja::cajasPermitidas()->where("fecha",date("Y-m-d"))->get()->count() && $display_factura_abierta)
            <script src="{{asset('js/facturaAction.js')}}"></script>
            @endif
        @show
        <script>
            @if(\Illuminate\Support\Facades\Session::has("mensaje_toast"))
                <?php
                    $datos = \Illuminate\Support\Facades\Session::get("mensaje_toast");
                ?>
                lanzarToast('{{$datos["mensaje"]}}','{{$datos["titulo"]}}','{{$datos["duracion"]}}','{{$datos["color_titulo"]}}');
            @endif
        </script>
    </body>
</html>
