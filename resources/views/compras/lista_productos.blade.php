<input type="hidden" id="tipo_elemento" value="producto">

<div class="content-table-slide">
{!! Form::open(["id"=>"form-select-producto"]) !!}
    @if($tipo == "productos")
        <table id="ProductosProveedorActualTabla" class="bordered highlight centered" style="min-width: 100%;">
            <thead>
                <tr>
                    <th></th>
                    <th>Nombre</th>
                    <th>Valor</th>
                    <th>Iva</th>
                    <th>Stock</th>
                    <th>Umbral</th>
                    <th>Unidad</th>
                    <th>Categoria</th>
                </tr>
            </thead>
        </table>
    @elseif($tipo == "productos_otros_proveedores")
        <table id="ProductosOtrosProveedoresTabla" class="bordered highlight centered" style="min-width: 100%; width: 100%">
            <thead>
            <tr>
                <th>Nombre</th>
                <th>Valor actual</th>
                <th>Iva actual</th>
                <th>Stock</th>
                <th>Unidad</th>
                <th>Categoria</th>
                <th>Proveedor actual</th>
                <th>Rel</th>
            </tr>
            </thead>
        </table>

    @endif
{!! Form::close() !!}

</div>