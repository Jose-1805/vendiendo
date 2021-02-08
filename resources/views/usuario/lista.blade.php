<table class="bordered highlight centered" id="tabla_usuarios">
    <thead>
    <tr>
        <th >Nombre</th>
        <th >Perfil</th>
        <th >Seudónimo</th>
        <th >Correo</th>
        <th >Teléfono</th>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","usuarios","configuracion"))
            <th >Editar</th>
        @else
            <th class="hide"></th>
        @endif

        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","usuarios","configuracion"))
            <th >Eliminar</th>
        @else
            <th class="hide"></th>
        @endif
    </tr>
    </thead>
</table>

@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","usuarios","configuracion"))
<div id="modal-eliminar-usuario" class="modal modal-fixed-footer modal-small">
    <div class="modal-content">
        <p class="titulo-modal">Eliminar</p>
        <p>¿Está seguro de eliminar este usuario?</p>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-eliminar-usuario">
            <a href="#!" class="red-text btn-flat" onclick="javascript: eliminar(id_select)">Aceptar</a>
            <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-eliminar-usuario">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>
@endif