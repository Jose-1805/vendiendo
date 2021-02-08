<?php
    if(!isset($hide))$hide = "hide";
?>
@if(count($modulos_configuracion))
    <div class="col s12 menu-hamburguesa {{$hide}}" id="menu-configuracion" >
        <?php
        $aux = 0;
        $categoria = "";
        $nueva = false;
        ?>
        @forelse($modulos_configuracion as $m)
            <?php
            if($categoria != $m->categoria){
                $categoria = $m->categoria;
                $nueva = true;
                $aux = 0;
            }else{
                $nueva = false;
            }
            ?>
            @if($nueva)
                <p class="col s12 titulo-modal center-align">{{$categoria}}</p>
            @endif

            @include('templates.secciones.item-menu',["data"=>["btn-menu-toggle"=>true,"responsive_class"=>"s4 m4 l4","href"=>$m->url,"src"=>$m->image_url,"fa_class"=>$m->icon_class,"color_class"=>"red darken-4","label"=>$m->label,"tooltip"=>$m->tooltip]])
            <?php $aux++; ?>
            @if($aux % 3 == 0)
                <div class="row hide-on-small-only" style="margin: 0px;"></div>
            @endif
        @empty
            <h4 class="center-align">No se han asignado módulos en esta sección.</h4>
        @endforelse

        @if(\Illuminate\Support\Facades\Auth::user()->perfil->nombre != "superadministrador")
            <!-- OPCION PARA OBJETIVOS DE VENTAS -->
            @include('templates.secciones.item-menu',["data"=>["btn-menu-toggle"=>true,"responsive_class"=>"s4 m4 l4","href"=>url("/objetivos-ventas"),"src"=>"","fa_class"=>"fa-eercast","color_class"=>"red darken-4","label"=>"Objetivos de ventas","tooltip"=>""]])
            <?php $aux++; ?>
            @if($aux % 3 == 0)
                <div class="row hide-on-small-only" style="margin: 0px;"></div>
            @endif
        @endif
    </div>
@endif