@if(count($modulos_inicio))
    <div class="col s12 menu-hamburguesa" id="menu-inicio" >
        <?php
            $aux = 0;
            $categoria = "";
            $nueva = false;
        ?>
        @forelse($modulos_inicio as $m)
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

            @include('templates.secciones.item-menu',["data"=>["btn-menu-toggle"=>true,"responsive_class"=>"s4 m4 l4","href"=>$m->url,"src"=>$m->image_url,"fa_class"=>$m->icon_class,"color_class"=>"orange accent-2","label"=>$m->label,"tooltip"=>$m->tooltip]])
            <?php $aux++; ?>
            @if($aux % 3 == 0)
                <div class="row hide-on-small-only" style="margin: 0px;"></div>
            @endif
        @empty
            <h4 class="center-align">No se han asignado módulos en esta sección.</h4>
        @endforelse


    </div>
@endif