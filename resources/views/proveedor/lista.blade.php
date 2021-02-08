
@include("templates.mensajes",["id_contenedor"=>"lista-proveedores"])
<?php
    $numColumns = 6;
?>

<table class="bordered highlight centered" id="tabla_proveedores" cellspacing="0" width="100%" style="word-break: break-all;">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>NIT</th>
            <th>Contacto</th>
            <th>Dirección</th>
            <th>Teléfono</th>
            <th>Correo</th>
            @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","proveedores","configuracion"))
                <th>Editar</th>
            @else
                <th class="hide"></th>
            @endif
            @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","proveedores","configuracion"))
                <th>Eliminar</th>
            @else
                <th class="hide"></th>
            @endif
        </tr>
    </thead>
    <tbody></tbody>
</table>

@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","proveedores","configuracion"))
<div id="modal-eliminar-proveedor" class="modal modal-fixed-footer modal-small">
    <div class="modal-content">
        <p class="titulo-modal">Eliminar</p>
        <p>¿Está seguro de eliminar este proveedor?</p>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-eliminar-proveedor">
            <a href="#!" class="red-text btn-flat" onclick="javascript: eliminar(null,false)">Aceptar</a>
            <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-eliminar-proveedor">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>
@endif
@section('js')
@parent
    <script src="{{asset('js/proveedorAction.js')}}"></script>
    <script type="text/javascript">
     @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","proveedores","configuracion"))
            setPermisoEditarProveedor(true);
        @else
            setPermisoEditarProveedor(false);
        @endif


     @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","proveedores","configuracion"))
        setPermisoEliminarProveedor(true);
    @endif
</script>
@stop

