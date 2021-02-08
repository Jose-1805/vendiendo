@include("templates.mensajes",["id_contenedor"=>"lista-cajas"])

<table class="bordered highlight centered" id="tabla_cajas" style="width: 100%;">
    <thead>
    <tr>
        <th>Nombre</th>
        <th>Prefijo</th>
        <th>Estado</th>
        <th>Cajero actual</th>
        <th>Valor inicial</th>
        <th>Valor actual</th>
        <th>Opciones</th>
    </tr>
    </thead>
</table>

@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","caja","configuracion"))
    <div id="modal-eliminar-caja" class="modal modal-fixed-footer modal-small">
        <div class="modal-content">
            <p class="titulo-modal">Eliminar</p>
            @include('templates.mensajes',["id_contenedor"=>"eliminar-caja"])
            <p>¿Está seguro de eliminar esta caja?</p>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-eliminar-caja">
                <a href="#!" class="red-text btn-flat" onclick="javascript: eliminar(null,false)">Aceptar</a>
                <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-eliminar-caja">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>
@endif
