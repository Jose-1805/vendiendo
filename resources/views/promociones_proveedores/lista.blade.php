<table class="bordered highlight centered">
    <thead>
        <th>Descripción</th>
        <th>Producto</th>
        <th>Fecha inicio</th>
        <th>Fecha fin</th>
        <th>Estado activo/inactivo</th>
    </thead>

    <tbody>
        @foreach($promociones as $p)
            <tr data-promocion="{{$p->id}}">
                <td>{{$p->descripcion}}</td>
                <td>{{$p->producto->nombre." - ".$p->producto->categoria->nombre}}</td>
                <td>{{$p->fecha_inicio}}</td>
                <td>{{$p->fecha_fin}}</td>
                <td>
                    <div class="switch">
                        <label>
                            @if($p->estado == "activo")
                                <input class="check-estado-promocion" type="checkbox" checked>
                            @else
                                <input class="check-estado-promocion" type="checkbox">
                            @endif
                            <span class="lever"></span>
                        </label>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>