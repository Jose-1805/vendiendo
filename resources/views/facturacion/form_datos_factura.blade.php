{!! Form::model($usuario,["id"=>"form-datos-facturacion"]) !!}
<div class="col s12 margin-top-40">
    @include("templates.mensajes",["id_contenedor"=>"datos-facturacion"])
    <p class="font-large margin-bottom-20">Encabezado factura</p>
    <p>
        @if($usuario->datos_cliente_vendedor == "si")
            <input type="checkbox" name="datos_cliente_vendedor" id="datos_cliente_vendededor" value="si" checked/>
        @else
            <input type="checkbox" name="datos_cliente_vendedor" id="datos_cliente_vendededor" value="si" />
        @endif
        <label for="datos_cliente_vendededor">Imprimir datos de cliente y vendedor en la factura</label>
    </p>
    <ul class="col s12 padding-left-30 grey-text text-darken-1">
        <li style="list-style-type: disc;">Se recomienda ingresar régimen al que pertenece, dirección física del negocio, teléfonos, correos, página web, país y ciudad.</li>
        <li style="list-style-type: disc;">Se recomienda ingresar máximo 5 líneas.</li>
    </ul>
    {!! Form::textarea("encabezado_factura",null,["id"=>"encabezado_factura","class"=>"materialize-textarea"]) !!}
</div>

<div class="col s12 margin-top-30">
    <p class="font-large margin-bottom-20">Pie de página</p>

    <p>
        @if($usuario->observaciones_bn == "si")
            <input type="checkbox" name="observaciones_bn" id="observaciones_bn" value="si" checked/>
        @else
            <input type="checkbox" name="observaciones_bn" id="observaciones_bn" value="si" />
        @endif
        <label for="observaciones_bn">Imprimir observaciones en factura a blanco y negro</label>
    </p>

    <p>
        @if($usuario->observaciones_color == "si")
            <input type="checkbox" name="observaciones_color" id="observaciones_color" value="si" checked/>
        @else
            <input type="checkbox" name="observaciones_color" id="observaciones_color" value="si" />
        @endif
        <label for="observaciones_color">Imprimir observaciones en factura a color</label>
    </p>

    <ul class="col s12 padding-left-30 grey-text text-darken-1">
        <li style="list-style-type: disc;">Se recomienda agregar condiciones de garantía. Ejemplo: "No se realiza devoluciones en efectivo". </li>
        <li style="list-style-type: disc;">Se recomienda ingresar máximo 5 líneas.</li>
    </ul>
    {!! Form::textarea("pie_factura",null,["id"=>"pie_factura","class"=>"materialize-textarea"]) !!}
</div>

<div class="col s12 center-align margin-top-20" id="contenedor-botones-encabezado-factura">
    <a class="btn blue-grey darken-2 waves-effect waves-light" onclick="guardarDatosFacturacion();">Guardar</a>
</div>

<div class="col s12 progress margin-bottom-10 hide" id="progress-encabezado-factura">
    <div class="indeterminate"></div>
</div>
{!! Form::close() !!}