@if(count($notificaciones))
    <ul class="collection">
        @foreach($notificaciones as $notificacion)
                <?php
                    $classBtn= "btn-notificacion-leida";
                    $title = "Marcar como leído";
                    $cursor = "pointer";
                    if($notificacion->estado == "leído"){
                        $classBtn= "grey-text";
                        $title = "";
                        $cursor = "default";
                    }
                ?>
                <li class="collection-item">
                    <a href="#!" class="secondary-content {{$classBtn}}" style="cursor: {{$cursor}};" data-tipo="{{$tipo}}" data-notificacion="{{$notificacion->id}}" title="{{$title}}"><i class="fa fa-check-circle"></i></a>
                    <!--<a class="link-notificacion" href="#">-->
                    <div class="mensaje">
                        {!! $notificacion->mensaje !!}
                    </div>
                    <!--</a>-->
                    <p class="fecha-notificacion right-align"><i class="fa fa-clock-o"></i> <span>{{date("Y-m-d",strtotime($notificacion->created_at))}}</span></p>
                </li>
        @endforeach
    </ul>
@else
    <p class="center-align">No existen notificaciones para mostrar.</p>
    <input type="checkbox" id="sin-notificaciones" checked>
@endif