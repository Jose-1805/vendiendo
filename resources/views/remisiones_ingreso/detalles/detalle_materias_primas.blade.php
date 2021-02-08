<div class="content-table-slide col s12">
    <table class="table highlight centered margin-bottom-40" style="min-width: 500px;">
        <thead>
        <th>Materia prima</th>
        <th>Cantidad</th>
        <th>Stock actual</th>
        <th>Unidad</th>
        </thead>

        <tbody>
        @foreach($remision_materias_p as $materia)
            <tr>
                <td>{{$materia->nombre}}</td>
                <td>{{$materia->cantidad}}</td>
                <td>{{$materia->stock}}</td>
                <td>{{$materia->unidad->nombre}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>