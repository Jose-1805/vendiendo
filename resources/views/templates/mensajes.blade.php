<?php
    $confirmacion_clase = "hide";
    $error_clase = "hide";
    if(\Illuminate\Support\Facades\Session::has("mensaje")){
        $confirmacion_clase = "";
    }
    if(\Illuminate\Support\Facades\Session::has("mensaje_error")){
        $error_clase = "";
    }
?>
<div class="contenedor-errores {{$error_clase}} col s12" id="contenedor-errores-{{$id_contenedor}}">
    @if(\Illuminate\Support\Facades\Session::has("mensaje_error"))
        <i class='fa fa-close btn-cerrar-errores'></i>
        <ul>
            <li>{!! \Illuminate\Support\Facades\Session::get("mensaje_error") !!}</li>
            <?php \Illuminate\Support\Facades\Session::forget("mensaje_error"); ?>
        </ul>
    @endif
</div>
<div class="col s12 contenedor-confirmacion {{$confirmacion_clase}}" id="contenedor-confirmacion-{{$id_contenedor}}">
    @if(\Illuminate\Support\Facades\Session::has("mensaje"))
        <i class='fa fa-close btn-cerrar-confirmacion'></i>
        <ul>
            <li>{!! \Illuminate\Support\Facades\Session::get("mensaje") !!}</li>
            <?php \Illuminate\Support\Facades\Session::forget("mensaje"); ?>
        </ul>
    @endif
</div>