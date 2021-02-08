<table class="table highlight centered">
    <thead>
        <th>Materia prima</th>
        <th>Cant.</th>
        <th>Stock</th>
        <th>Vlr. actual</th>
        <th>Vlr. anterior</th>
    </thead>
    <tbody>
        @forelse($componentes as $m)
            <tr>
                <td>{{$m->nombre}}</td>
                <td>{{$m->cantidad}}</td>
                <td>{{$m->stock}}</td>
                <td>$ {{number_format($m->ultimoHistorial()->precio_costo_nuevo,2,',','.')}}</td>
                <td>$ {{number_format($m->ultimoHistorial()->precio_costo_anterior,2,',','.')}}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="center-align">Sin resultados.</td>
            </tr>
        @endforelse
    </tbody>
</table>