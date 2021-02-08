
@include("templates.mensajes",["id_contenedor"=>"lista-materias-primas"])
<?php
    $numColumns = 5;
?>

<table id="tabla_materias_primas" class="bordered highlight centered" style="width: 100%">
    <thead>
    <tr>
        <th >Nombre</th>
        <th >Código</th>
        <th style="max-width: 300px !important;">Descripción</th>
        <th >Unidad</th>
        <th >Stock</th>
        <th >Umbral</th>
        <th >Detalle</th>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","materias primas","inicio"))
            <th >Editar</th>
        @else
            <th class="hide"></th>
        @endif
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","materias primas","inicio"))
            <th >Eliminar</th>
        @else
            <th class="hide"></th>
        @endif

    </tr>
    </thead>
</table>

@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","materias primas","inicio"))
<div id="modal-eliminar-materia" class="modal modal-fixed-footer modal-small">
    <div class="modal-content">
        <p class="titulo-modal">Eliminar</p>
        <p>¿Está seguro de eliminar esta materia prima?</p>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-eliminar-materia">
            <a href="#!" class="red-text btn-flat" onclick="javascript: eliminar(null,false)">Aceptar</a>
            <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-eliminar-materia">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>
@endif

