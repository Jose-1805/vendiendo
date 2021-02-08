@include("templates.mensajes",["id_contenedor"=>"lista-productos"])
<?php
    $numColumns = 2;
?>

<table class="bordered highlight centered" id="tabla_categorias" style="width: 100%;">
    <thead>
    <tr>
        <th>Nombre</th>
        <th>Descripción</th>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","Categorias","configuracion"))
            <th >Editar</th>
        @else
            <th class="hide"></th>
        @endif

        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","Categorias","configuracion"))
            <th >Eliminar</th>
        @else
            <th class="hide"></th>
        @endif
    </tr>
    </thead>
</table>

@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","Categorias","configuracion"))
    <div id="modal-eliminar-categoria" class="modal modal-fixed-footer modal-small">
        <div class="modal-content">
            <p class="titulo-modal">Eliminar</p>
            <p>¿Está seguro de eliminar esta categoría?</p>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-eliminar-categoria">
                <a href="#!" class="red-text btn-flat" onclick="javascript: eliminar(id_select)">Aceptar</a>
                <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-eliminar-categoria">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>
@endif

