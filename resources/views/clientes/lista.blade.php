@include("templates.mensajes",["id_contenedor"=>"lista-clientes"])


<table class="bordered highlight centered" id="tabla_clientes" style="width: 100%;">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Identificación</th>
            <th>Teléfono</th>
            <th>Correo</th>
            <th>Dirección</th>
            @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","clientes","inicio"))
                <th >Editar</th>
            @else
                <th class="hide"></th>
            @endif
            @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","clientes","inicio"))
                <th >Eliminar</th>
            @else
                <th class="hide"></th>
            @endif
        </tr>
    </thead>
</table>

@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","clientes","inicio"))
    <div id="modal-eliminar-cliente" class="modal modal-fixed-footer modal-small">
        <div class="modal-content">
            <p class="titulo-modal">Eliminar</p>
            <p>¿Está seguro de eliminar este cliente?</p>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-eliminar-cliente">
                <a href="#!" class="red-text btn-flat" onclick="javascript: eliminar(null,false)">Aceptar</a>
                <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-eliminar-cliente">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>
@endif