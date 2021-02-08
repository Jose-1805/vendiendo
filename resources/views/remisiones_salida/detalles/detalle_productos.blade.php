<div class="content-table-slide col s12">
    <table class="table highlight centered margin-bottom-40" style="min-width: 500px;">
        <thead>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Stock actual</th>
            <th>Unidad</th>
        </thead>

        <tbody>
        @foreach($remision_productos as $producto)
            <tr>
                <td>{{$producto->nombre}}</td>
                <td>{{$producto->cantidad}}</td>
                <td>{{$producto->stock}}</td>
                <td>{{$producto->unidad->nombre}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>