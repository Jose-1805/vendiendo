<?php
    $usuario = \Illuminate\Support\Facades\Auth::user();
    $admin = \App\User::find($usuario->userAdminId());
?>
<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        <p class="titulo">Configuración</p>
        @include('templates.mensajes',["id_contenedor"=>"configuracion"])
        @if($usuario->perfil->nombre != "superadministrador")
            <div class="col s12 m3 l2 center-align margin-bottom-40">
                <a class="modal-trigger waves-effect waves-light white-text" href="#modal-cerrar-aplicacion">
                    <i style="font-size: 25px;" class="blue-grey darken-2 item-configuracion fa fa-mobile"></i>
                    <p class="blue-grey-text text-darken-2 truncate" style="line-height: 15px;">Cerrar aplicación móvil</p>
                </a>
            </div>
        @endif

        @if($usuario->plan()->factura_abierta == "si" && $usuario->bodegas == 'no')
            <div class="col s12 m3 l2 center-align margin-bottom-40">
                <a class="modal-trigger waves-effect waves-light white-text" href="#modal-factura-abierta-estado">
                    <i style="font-size: 25px;" class="blue-grey darken-2 item-configuracion fa fa-wpforms"></i>
                    <p class="blue-grey-text text-darken-2 truncate" style="line-height: 15px;">Venta rápida</p>
                </a>
            </div>
        @endif

        <div class="col s12 m3 l2 center-align margin-bottom-40">
            <a class="modal-trigger waves-effect waves-light white-text" href="#modal-cambiar-contrasena">
                <i style="font-size: 25px;" class="blue-grey darken-2 item-configuracion fa fa-ellipsis-h"></i>
                <p class="blue-grey-text text-darken-2 truncate" style="line-height: 15px;">Cambiar contraseña</p>
            </a>
        </div>

        @if(
            $usuario->plan()->puntos == "si" &&
            (
                ($usuario->perfil->nombre == "administrador" && $usuario->bodegas == 'no')
                || ($usuario->perfil->nombre == "administrador" && $usuario->bodegas == 'si' && $usuario->admin_bodegas == 'si')
            )
        )
            <div class="col s12 m3 l2 center-align margin-bottom-40">
                <a class="modal-trigger waves-effect waves-light white-text" href="#modal-sistema-puntos">
                    <i style="font-size: 25px;" class="blue-grey darken-2 item-configuracion fa fa-credit-card"></i>
                    <p class="blue-grey-text text-darken-2 truncate" style="line-height: 15px;">Sistema de puntos</p>
                </a>
            </div>
        @endif

        @if(
            ($usuario->perfil->nombre == "administrador" && $usuario->bodegas == 'no')
            || ($usuario->perfil->nombre == "administrador" && $usuario->bodegas == 'si' && $usuario->admin_bodegas == 'si')
        )
            <div class="col s12 m3 l2 center-align margin-bottom-40">
                <a class="modal-trigger waves-effect waves-light white-text" href="#modal-panel-graficas">
                    <i style="font-size: 25px;" class="blue-grey darken-2 item-configuracion fa fa-pie-chart"></i>
                    <p class="blue-grey-text text-darken-2 truncate" style="line-height: 15px;">Panel de gráficas</p>
                </a>
            </div>
        @endif

        @if(
            ($usuario->perfil->nombre == "administrador" && $usuario->bodegas == 'no')
            || ($usuario->perfil->nombre == "administrador" && $usuario->bodegas == 'si' && $usuario->admin_bodegas == 'si')
        )
            <div class="col s12 m3 l2 center-align margin-bottom-40">
                <a class="modal-trigger waves-effect waves-light white-text" href="#modal-informacion-negocio">
                    <i style="font-size: 25px;" class="blue-grey darken-2 item-configuracion fa fa-home"></i>
                    <p class="blue-grey-text text-darken-2 truncate" style="line-height: 15px;">Información de negocio</p>
                </a>
            </div>
        @endif

        @if(
            ($usuario->perfil->nombre == "administrador" && $usuario->bodegas == 'no')
            ||($usuario->perfil->nombre == "administrador" && $usuario->bodegas == 'si' && $usuario->admin_bodegas == 'si')
        )
            <div class="col s12 m3 l2 center-align margin-bottom-40">
                <a class="modal-trigger waves-effect waves-light white-text" href="#modal-formas-pago">
                    <i style="font-size: 25px;" class="blue-grey darken-2 item-configuracion fa fa-dollar"></i>
                    <p class="blue-grey-text text-darken-2 truncate" style="line-height: 15px;">Formas de pago</p>
                </a>
            </div>
        @endif
        <div class="col s12 divider margin-bottom-30"></div>
    </div>


    @if($usuario->perfil->nombre != "superadministrador")
        <div id="modal-cerrar-aplicacion" class="modal modal-fixed-footer modal-small">
            <div class="modal-content">
                <p class="titulo-modal">¿Cerrar aplicación móvil?</p>
                <p>Se cerrará la sesión de <strong>vendiendo.co</strong> en el dispositivo móvil donde ha sido instalada la aplicación.</p>
            </div>

            <div class="modal-footer">
                <div class="col s12" id="contenedor-botones-cerrar-aplicacion">
                    <a href="#!" class="red-text btn-flat" onclick="javascript: cerrarApliaccion()">Aceptar</a>
                    <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
                </div>

                <div class="progress hide" id="progress-cerrar-aplicacion">
                    <div class="indeterminate cyan"></div>
                </div>
            </div>
        </div>
    @endif

    @if($usuario->plan()->factura_abierta == "si")
        <div id="modal-factura-abierta-estado" class="modal modal-fixed-footer modal-small">
            <div class="modal-content">
                @if($usuario->adminFacturaAbierta() == "si")
                    <p class="titulo-modal">Desactivar</p>
                    <p>¿Está seguro de desactivar la funcionalidad de venta rápida?</p>
                @else
                    <p class="titulo-modal">Activar</p>
                    <p>Seleccione la opción aceptar para activar la funcionalidad de venta rápida.</p>
                @endif
            </div>

            <div class="modal-footer">
                <div class="col s12" id="contenedor-botones-factura-abierta-estado">
                    <a href="#!" class="red-text btn-flat" onclick="javascript: updatedFacturaAbierta()">Aceptar</a>
                    <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
                </div>

                <div class="progress hide" id="progress-factura-abierta-estado">
                    <div class="indeterminate cyan"></div>
                </div>
            </div>
        </div>
    @endif


    <div id="modal-cambiar-contrasena" class="modal modal-fixed-footer modal-small" style="min-height: 70%;">
        <div class="modal-content">
            <p class="titulo-modal">Cambiar contraseña</p>
            @include("templates.mensajes",["id_contenedor"=>"cambiar-contrasena"])
            {!! Form::open(["id"=>"form-cambiar-contrasena"]) !!}
            <div class="input-field col s12">
                <input type="password" id="password-old" name="password-old" autocomplete="off">
                <label for="password-old">Contraseña anterior</label>
            </div>
            <div class="input-field col s12">
                <input type="password" id="password-new" name="password-new" autocomplete="off">
                <label for="password-new">Contraseña nueva</label>
            </div>
            <div class="input-field col s12">
                <input type="password" id="password-check" name="password-check" autocomplete="off">
                <label for="password-check">Confirme su nueva contraseña</label>
            </div>
            {!! Form::close() !!}
        </div>
        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-cambiar-contrasena">
                <a href="#!" class="green-text btn-flat" onclick="cambiarContrasena()">Aceptar</a>
                <a href="#!" class="modal-close btn-flat ">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-cambiar-contrasena">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>

    @if(
        $usuario->plan()->puntos == "si" &&
        (
            ($usuario->perfil->nombre == "administrador" && $usuario->bodegas == 'no')
            || ($usuario->perfil->nombre == "administrador" && $usuario->bodegas == 'si' && $usuario->admin_bodegas == 'si')
        )
    )
        <div id="modal-sistema-puntos" class="modal modal-fixed-footer modal-small" style="min-height: 80%;">
            <div class="modal-content">
                <p class="titulo-modal">Sistema de puntos</p>
                @include("templates.mensajes",["id_contenedor"=>"sistema-puntos"])
                {!! Form::model($usuario,["id"=>"form-sistema-puntos"]) !!}
                <p class="col s12">Caducidad de los puntos (Meses)</p>
                {!! Form::text("caducidad_puntos",null,["id"=>"caducidad_puntos","class"=>"col s12 num-entero"]) !!}
                <p class="col s12">En ventas</p>
                {!! Form::text("pesos_venta",null,["id"=>"pesos_venta","class"=>"col s4 num-entero"]) !!}
                {!! Form::text("puntos_venta",null,["id"=>"puntos_venta","class"=>"col s4 offset-s4 num-entero"]) !!}
                <p class="col s4 center-align font-small" style="margin-top: -10px;">Peso(s)</p>
                <p class="col s4 center-align font-small" style="margin-top: -10px;">Equivalen a:</p>
                <p class="col s4 center-align font-small" style="margin-top: -10px;">Punto(s)</p>

                <p class="col s12 margin-top-30">En pagos</p>
                {!! Form::text("puntos_pago",null,["id"=>"puntos_pago","class"=>"col s4 num-entero"]) !!}
                {!! Form::text("pesos_pago",null,["id"=>"pesos_pago","class"=>"col s4 offset-s4 num-entero"]) !!}
                <p class="col s4 center-align font-small" style="margin-top: -10px;">Punto(s)</p>
                <p class="col s4 center-align font-small" style="margin-top: -10px;">Equivalen a:</p>
                <p class="col s4 center-align font-small" style="margin-top: -10px;">Peso(s)</p>
                {!! Form::close() !!}
            </div>
            <div class="modal-footer">
                <div class="col s12" id="contenedor-botones-sistema-puntos">
                    <a href="#!" class="green-text btn-flat" onclick="establecerSistemaPuntos()">Aceptar</a>
                    <a href="#!" class="modal-close btn-flat ">Cancelar</a>
                </div>
            </div>
        </div>
    @endif

    @if(
            ($usuario->perfil->nombre == "administrador" && $usuario->bodegas == 'no')
            || ($usuario->perfil->nombre == "administrador" && $usuario->bodegas == 'si' && $usuario->admin_bodegas == 'si')
        )
        <div id="modal-informacion-negocio" class="modal modal-fixed-footer modal-small" style="min-height: 70%;">
            <div class="modal-content">
                <p class="titulo-modal">Información de negocio</p>
                @include("templates.mensajes",["id_contenedor"=>"informacion-negocio"])
                {!!Form::model($usuario,["id"=>"form-informacion-negocio"])!!}
                <div class="col s12" style="padding: 20px;">
                    <div class="input-field col s12">
                        {!!Form::label("nombre_negocio","Nombre")!!}
                        {!!Form::text("nombre_negocio",null,["id"=>"nombre_negocio","placeholder"=>"Ingrese el nombre del negocio","maxlength"=>"200"])!!}
                    </div>

                    <div class="input-field col s12" id="contenedor-nit">
                        {!!Form::label("nit","NIT")!!}
                        {!!Form::text("nit",null,["id"=>"nit","placeholder"=>"Ingrese el nit del administrador","maxlength"=>"30"])!!}
                    </div>

                    <div class="input-field col s12">
                        {!!Form::label("telefono","Teléfono")!!}
                        {!!Form::text("telefono",null,["id"=>"telefono","placeholder"=>"Ingrese el teléfono del usuario","maxlength"=>"10","class"=>"num-tel"])!!}
                    </div>
                </div>
                {!! Form::hidden("id",$usuario->id) !!}
                {!!Form::close()!!}
            </div>

            <div class="modal-footer">
                <div class="col s12" id="contenedor-botones-informacion-negocio">
                    <a href="#!" class="green-text btn-flat" onclick="cambiarInformacionNegocio()">Aceptar</a>
                    <a href="#!" class="modal-close btn-flat">Cancelar</a>
                </div>

                <div class="progress hide" id="progress-informacion-negocio">
                    <div class="indeterminate cyan"></div>
                </div>
            </div>
        </div>
    @endif

    @if(
            ($usuario->perfil->nombre == "administrador" && $usuario->bodegas == 'no')
            || ($usuario->perfil->nombre == "administrador" && $usuario->bodegas == 'si' && $usuario->admin_bodegas == 'si')
        )
        <div id="modal-panel-graficas" class="modal modal-fixed-footer modal-small" style="min-height: 70%;">
            <div class="modal-content">
                <p class="titulo-modal">Administrar panel de gráficas</p>
                @include("templates.mensajes",["id_contenedor"=>"panel-graficas"])
                {!!Form::open(["id"=>"form-panel-graficas"])!!}
                    <div class="col s12">
                        <?php
                            $checked = "";
                            if($usuario->panel_graficas == "si")$checked = "checked='checked'"
                        ?>
                        <p class="no-margin">
                            <input type="checkbox" name="usuarios[]" id="user-{{$usuario->id}}" value="{{$usuario->id}}" {{$checked}}/>
                            <label for="user-{{$usuario->id}}">{{$usuario->nombres." ".$usuario->apellidos}}</label>
                        </p>
                        @foreach(\App\User::permitidos()->get() as $u)
                            <?php
                                $checked = "";
                                if($u->panel_graficas == "si")$checked = "checked='checked'"
                            ?>
                            <p class="no-margin">
                                <input type="checkbox" name="usuarios[]" id="user-{{$u->id}}" value="{{$u->id}}" {{$checked}}/>
                                <label for="user-{{$u->id}}">{{$u->nombres." ".$u->apellidos}}</label>
                            </p>
                        @endforeach
                    </div>
                {!!Form::close()!!}
            </div>

            <div class="modal-footer">
                <div class="col s12" id="contenedor-botones-panel-graficas">
                    <a href="#!" class="green-text btn-flat" onclick="administrarPanelGraficas()">Aceptar</a>
                    <a href="#!" class="modal-close btn-flat">Cancelar</a>
                </div>

                <div class="progress hide" id="progress-panel-graficas">
                    <div class="indeterminate cyan"></div>
                </div>
            </div>
        </div>
    @endif

    @if(
        ($usuario->perfil->nombre == "administrador" && $usuario->bodegas == 'no')
        ||($usuario->perfil->nombre == "administrador" && $usuario->bodegas == 'si' && $usuario->admin_bodegas == 'si')
    )
        <div id="modal-formas-pago" class="modal modal-fixed-footer modal-small" style="min-height: 70%;">
            <div class="modal-content">
                <p class="titulo-modal">Formas de pago</p>
                @include("templates.mensajes",["id_contenedor"=>"formas-pago"])
                {!!Form::open(["id"=>"form-formas-pago"])!!}
                    <div class="col s12">
                        <?php
                            $tipos_pago = \App\Models\TipoPago::all();
                            $bancos = \App\Models\CuentaBancaria::permitidos()->get()->count()>0?true:false;

                            $pagos_sin_consignacion = \App\Models\Factura::permitidos()->select('facturas_tipos_pago.*','facturas.id as id_factura')
                                ->join('facturas_tipos_pago','facturas.id','=','facturas_tipos_pago.factura_id')
                                ->join('tipos_pago','facturas_tipos_pago.tipo_pago_id','=','tipos_pago.id')
                                ->leftJoin('consignaciones','facturas_tipos_pago.id','=','consignaciones.factura_tipo_pago_id')
                                ->whereNull('consignaciones.id')
                                ->where('tipos_pago.valor_a_caja','no')
                                ->get();
                        ?>

                        @if(count($pagos_sin_consignacion) && $bancos && $admin->cuenta_bancaria_forma_pago)
                            <p class="text-info font-small orange lighten-3 padding-10 row" id="text-consignaciones-pago">
                                Su cuenta de usuario contiene ({{count($pagos_sin_consignacion->groupBy('id_factura'))}}) facturas
                                con ({{count($pagos_sin_consignacion)}}) pagos que debieron ser dirigidos a la cuenta bancaria seleccionada en esta configuración. <strong>Las consignaciones correspondientes para estos pagos no han sido realizadas.</strong>
                                <br>
                                <br>
                                <span><strong>Valor: </strong>$ {{number_format($pagos_sin_consignacion->sum('valor'),2,',','.')}}</span>
                                <br>
                                <br>
                                Para que el sistema realice las consignaciones de los pagos, haga click en el siguiente botón.

                                <a id="btn-realizar-consignaciones-pagos" class="margin-top-20 btn col s12 blue-grey darken-2 waves-effect waves-light">Realizar Consignaciones</a>
                            </p>
                        @endif

                        @if(!$bancos)
                            <p class="text-info font-small">Para poder seleccionar más formas de pago debe crear sus <a href="{{url('/cuenta-bancaria')}}">cuentas bancarias</a> en el sistema.</p>
                        @else
                            <p class="text-info font-small">Seleccione el banco al cual serán dirigidos los pagos que deben enviarse a un banco</p>
                            {!! Form::select('banco',\App\Models\CuentaBancaria::lista(),$admin->cuenta_bancaria_forma_pago,['class'=>'margin-bottom-20']) !!}
                        @endif
                        @foreach($tipos_pago as $tp)
                            @if($tp->valor_a_caja == 'si' || $bancos)
                                <?php
                                    $checked = "";
                                    if(Auth::user()->hasTipoPago($tp->id))$checked = "checked='checked'"
                                ?>
                                <p class="no-margin">
                                    <input type="checkbox" name="tipos_pago[]" id="tipo-pago-{{$tp->id}}" value="{{$tp->id}}" {{$checked}}/>
                                    <label for="tipo-pago-{{$tp->id}}">{{$tp->nombre}} (@if($tp->valor_a_caja == 'si') Caja @else Banco @endif)</label>
                                </p>
                            @endif
                        @endforeach
                    </div>
                {!!Form::close()!!}
            </div>

            <div class="modal-footer">
                <div class="col s12" id="contenedor-botones-formas-pago">
                    <a href="#!" class="green-text btn-flat" onclick="administrarFormasPago()">Aceptar</a>
                    <a href="#!" class="modal-close btn-flat">Cancelar</a>
                </div>

                <div class="progress hide" id="progress-formas-pago">
                    <div class="indeterminate cyan"></div>
                </div>
            </div>
        </div>
    @endif

@endsection

@section('js')
    @parent
    <script src="{{asset("js/configuracion.js")}}"></script>
@endsection