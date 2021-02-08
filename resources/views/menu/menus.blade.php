<?php
$secciones = [];

$modulos_inicio = [];
$modulos_configuracion = [];

$margenes = [0,0.53,0.96,1.38,1.8,2.22,2.64,3,3,3,3,1.85];
$clases_columns = ["one-wide","one-wide","two-wide","three-wide","four-wide","five-wide","six-wide","seven-wide","eight-wide","nine-wide","ten-wide"];

$obj_mod_inicio = [];
$obj_mod_configuracion = [];

//dd(\Illuminate\Support\Facades\Auth::user()->modulosActivos());
$modulos = \Illuminate\Support\Facades\Auth::user()->modulosActivos();

for($i = 0;$i < count($modulos);$i++){
    $modulo = $modulos[$i];
    if(!in_array($modulo->seccion,$secciones)){
        $secciones[] = $modulo->seccion;
    }

    switch($modulo->seccion){
        case "inicio": $modulos_inicio[] = $modulo;
            $obj_mod_inicio[] = $modulo;
            break;
        case "configuracion": $modulos_configuracion[] = $modulo;
            $obj_mod_configuracion[] = $modulo;
            break;
    }
}
?>
        <!-- MENU DE LA APLICACIÓN -->

@if(Auth::user()->configuracionCambioAB())
    <div id="contenedor-menu-app" class="z-depth-3" style="border: 0px !important;" >
        <ul >
            <li >
                <p class=" boton-menu-hamburguesa pestana-no-seleccionada" id="configuracion_btn"  data-id="configuracion"  >
                    Configuración
                </p>
            </li>
            <li >
                <p class="boton-menu-hamburguesa pestana-seleccionada" id="inicio_btn"  data-id = 'inicio'  >
                    Inicio
                </p>
            </li>
            @if(Auth::user()->permiso_reportes == 'si')
                <li >
                    <p class=" boton-menu-hamburguesa pestana-no-seleccionada" id="reportes_btn"   data-id="reportes"  >
                        Reportes
                    </p>
                </li>
            @endif
        </ul>
        @if(count($secciones))
            @if(count($modulos_inicio))
                @include("menu.inicio")
            @endif

            @if(count($modulos_configuracion))
                @include("menu.configuracion")
            @endif

                @if(\Illuminate\Support\Facades\Auth::user()->perfil->nombre != "superadministrador" && \Illuminate\Support\Facades\Auth::user()->perfil->nombre != "proveedor" && Auth::user()->permiso_reportes == 'si')
                @include('menu.reportes')
            @endif
        @else
            <p class="center-align">No existen modulos relacionados con su cuenta de usuario.</p>
        @endif
    </div>
@endif

<!-- MUNU DE USUARIO -->
<ul id='dropdown1' class='dropdown-content' style="pointer-events: auto;">
    <?php
        $user = \Illuminate\Support\Facades\Auth::user();
        $perfil = $user->perfil;
        $terminos_condiciones = true;
        if ($perfil->nombre != "superadministrador" ){
            $admin = \App\User::find($user->userAdminId());
            if ($admin->terminos_condiciones != "si"){
                $terminos_condiciones = false;
            }
        }
        $idRuta = "";
    ?>

    <li class="text-center" style="padding: 20px; width: 180px; ">

        @if($user->perfil->nombre == "superadministrador")
            <i class="fa fa-user" style="font-size: 60px;"></i>
        @else
            @if($user->perfil->nombre == "administrador" && $user->logo != "")
                <?php $logo = $user->logo;$idRuta = $user->id; ?>
            @elseif($user->perfil->nombre == "usuario")
                <?php $creador = \App\User::find($user->usuario_creador_id);?>
                @if($creador->logo != "")
                    <?php $logo = $creador->logo; $idRuta = $creador->id; ?>
                @endif
            @endif

            @if(isset($logo) && $idRuta != "")
                <img src='{{url("/app/public/img/users/logo/".$idRuta."/".$logo)}}' width="100%">
            @else
                <i class="fa fa-user" style="font-size: 60px;"></i>
            @endif
        @endif
        <p style="font-size: small;">{{\Illuminate\Support\Facades\Auth::user()->nombres." ".\Illuminate\Support\Facades\Auth::user()->apellidos}}</p>
        <p style="font-size: x-small;line-height: 0px;">({{str_replace("_"," ",\Illuminate\Support\Facades\Auth::user()->perfil->nombre)}})</p>
    </li>
    @if($terminos_condiciones)
        @if(Auth::user()->configuracionCambioAB())
            <li class="text-center" style="min-height: 30px !important;"><a href="{{url("/configuracion")}}" style="line-height: 30px !important; padding: 0px !important;">Configuración</a></li>
        @endif
    @endif
    <li class="text-center" style="min-height: 30px !important;"><a href="{{url('/auth/logout')}}" style="line-height: 30px !important; padding: 0px !important;" onclick="descartarPedido()">Salir</a></li>
</ul>