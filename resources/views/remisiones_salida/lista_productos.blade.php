<input type="hidden" id="tipo_elemento" value="producto">

<div class="content-table-slide">
{!! Form::open(["id"=>"form-select-producto"]) !!}
    <table id="tabla-productos" class="bordered highlight centered" style="min-width: 100%;">
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
{!! Form::close() !!}

</div>