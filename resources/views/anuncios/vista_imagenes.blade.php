<div class="row">
    @for($i = 1;$i < 4;$i++)
        <?php
            $name = "imagen_".$i;
        ?>
        @if($anuncio->$name)
            <div class="col s12 m4">
                <div class="col s12 center-align" style="height: 200px">
                    <img id="" src="{{url('/app/public/img/anuncios/'.$anuncio->id."/".$anuncio->$name)}}" alt="Producto" class="" style="max-width: 100%;max-height: 100%;">
                </div>
            </div>
        @endif
    @endfor
</div>