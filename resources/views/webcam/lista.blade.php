@include("templates.mensajes",["id_contenedor"=>"lista-productos"])
<?php
    $numColumns = 3;
?>
<table id="WebcamTabla" class="bordered highlight centered">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Alias</th>
            <th>Ubicación</th>
            @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","Web Cam","inicio"))
                <th >Editar</th>
            @endif
            @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Ver","Web Cam","inicio"))
                <th >VER Camara</th>
            @endif
            @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","Web Cam","inicio"))
                <th >Eliminar</th>
            @endif
        </tr>
    </thead>
</table>

@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","Web Cam","inicio"))
    <div id="modal-eliminar-webcam" class="modal modal-fixed-footer modal-small">
        <div class="modal-content">
            <p class="titulo-modal">Eliminar</p>
            <p>¿Está seguro de eliminar esta categoría?</p>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-eliminar-webcam">
                <a href="#!" class="red-text btn-flat" onclick="javascript: eliminar(id_select)">Aceptar</a>
                <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-eliminar-webcam">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>
@endif

