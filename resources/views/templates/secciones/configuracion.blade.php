@if(count($modulos_configuracion))
    <?php
        $class_color = "red darken-4";
    ?>
        <div class="row contenedor-seccion" id="configuracion" style="display: none;">

        <div class="col s12 encabezado-session">
            <div class="background-titulo-seccion col s12">
                <img class="" src="{{ asset('img/sistema/BarraRojo.png') }}" alt="1" />
            </div>
            <p class="col s12 titulo-seccion">Configuración</p>
        </div>
        <?php
            $aux = 0;
            $categoria = "";
            $nueva = false;
            $pintar = true;
        ?>

        <div class="contenedor-items col s12">
            <div class="col s12 borde-contenedor-items">

                @forelse($modulos_configuracion as $m)
                    <?php
                    if($categoria != $m->categoria){
                        $categoria = $m->categoria;
                        $nueva = true;
                        $aux = 0;
                    }else{
                        $nueva = false;
                    }

                    if(Auth::user()->bodegas == "si" && Auth::user()->admin_bodegas == "si" && $m->privilegio_administrador_bodegas == "no")
                        $pintar = false;
                    ?>
                    @if($pintar)
                        @if($nueva)
                            <p class="col s12 grey-text darken-2 titulo-modal center-align margin-top-50 padding-top-20">{{$categoria}}</p>
                        @endif

                        @include('templates.secciones.item-menu',["data"=>["responsive_class"=>"s4 m3 l2","href"=>$m->url,"color_class"=>$class_color,/*"color_style"=>"",*/"src"=>$m->image_url,"fa_class"=>$m->icon_class,"label"=>$m->label,"tooltip"=>$m->tooltip]])
                        <?php $aux++; ?>
                        @if($aux % 4 == 0)
                            <div class="row hide-on-large-only hide-on-small-only"></div>
                        @endif
                        @if($aux % 3 == 0)
                            <div class="row hide-on-med-and-up"></div>
                        @endif
                        @if($aux % 6 == 0)
                            <div class="row hide-on-med-and-down"></div>
                        @endif
                    @endif
                    <?php
                        $pintar = true;
                    ?>
                @empty
                    <!--<h4 class="center-align">No se han asignado módulos en esta sección.</h4>-->
                @endforelse

                @if(
                    (Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'no')
                    || (Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
                    && Auth::user()->plan()->objetivos_ventas == "si")
                    <!-- OPCION PARA OBJETIVOS DE VENTAS -->

                    @include('templates.secciones.item-menu',["data"=>["responsive_class"=>"s4 m3 l2","href"=>url("/objetivos-ventas"),"color_class"=>$class_color,/*"color_style"=>"",*/"src"=>"","fa_class"=>"fa-eercast","label"=>"Objetivos de ventas","tooltip"=>""]])
                    <?php $aux++; ?>
                    @if($aux % 4 == 0)
                        <div class="row hide-on-large-only hide-on-small-only"></div>
                    @endif
                    @if($aux % 3 == 0)
                        <div class="row hide-on-med-and-up"></div>
                    @endif
                    @if($aux % 6 == 0)
                        <div class="row hide-on-med-and-down"></div>
                    @endif
                @endif

            </div>
        </div>
            <div class="footer-items col s12"></div>
    </div>
@endif