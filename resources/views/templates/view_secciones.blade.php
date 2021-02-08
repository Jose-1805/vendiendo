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
?>

@for($i = 0;$i < count($modulos);$i++)
    <?php
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
     ?>
@endfor
    @if(count($secciones))
        <div class="" id="contenedor-secciones" style="">
            @if(count($modulos_configuracion))
                @include("templates.secciones.configuracion",["class_color"=>"blue2","class_color_title"=>"blue2"])
            @endif

            @if(count($modulos_inicio))
                @include("templates.secciones.inicio")
            @endif

            @if(Auth::user()->permiso_reportes == 'si')
                @include("templates.secciones.reportes",["class_color"=>"blue3","class_color_title"=>"blue3"])
            @endif

        </div>
        <div id="contenedor-botones-fijos-menu" class="">
            @if(count($modulos_configuracion))
                @include('templates.secciones.item-menu',["data"=>["btn-footer"=>true,"color_class"=>"red darken-4","href"=>"#configuracion",/*"color_style"=>"",*/"src"=>asset('img/sistema/SetupBtn.png'),/*"fa_class"=>"",*/]])
            @endif
            @if(count($modulos_inicio))
                @include('templates.secciones.item-menu',["data"=>["btn-footer"=>true,"color_class"=>"orange orange accent-2","href"=>"#inicio",/*"color_style"=>"",*/"src"=>asset('img/sistema/HomeBtn.png'),/*"fa_class"=>"",*/]])
            @endif
            @if(\Illuminate\Support\Facades\Auth::user()->perfil->nombre != "superadministrador" && \Illuminate\Support\Facades\Auth::user()->perfil->nombre != "proveedor" && Auth::user()->permiso_reportes == 'si')
                @include('templates.secciones.item-menu',["data"=>["btn-footer"=>true,"color_class"=>"green lighten-1","href"=>"#reportes",/*"color_style"=>"",*/"src"=>asset('img/sistema/ReportBtn.png'),/*"fa_class"=>"",*/]])
            @endif
        </div>
    @else
        <div class="col s10 offset-s1 m8 offset-m2 l6 offset-l3" style="background-color: rgba(255,255,255,.85);margin-top: 100px;border-radius: 3px;">
            <div class="col s12 center"><img src="{{ asset('img/sistema/LogoCompletoMini.png') }}" class="logo-fireos" alt="Fireos SAS"></div>
            <p style="font-size: large;text-align: justify;"><strong>{{\Illuminate\Support\Facades\Auth::user()->nombres}},</strong> aún no se han registrado planes o modulos
            que se relacionen con su cuenta de usuario. Para más información dirijase a <a href="#">preguntas frecuentes.</a></p>
        </div>
    @endif

@section('js')
    @parent
@stop