<table class="table centered highlight">
    <thead>
    <th>C&oacute;digo</th>
    <th>Nombre materia prima</th>
    <th>Descripci&oacute;n</th>
    <th>Umbral</th>
    <th>Stock</th>
    <th>Costo actual</th>
    <th>Unidad</th>
    <th>Proveedor actual</th>
    </thead>
    <tbody>
    @if(isset($reporte))
        <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
    @endif
    @foreach($materias as $materia)
        <tr>
            <td >{{$materia->codigo}}</td>
            <td >{{\App\TildeHtml::TildesToHtml($materia->nombre)}}</td>
            <td >{{\App\TildeHtml::TildesToHtml($materia->descripcion)}}</td>
            <td>{{$materia->umbral}}</td>
            <td>{{$materia->stock}}</td>
            <?php
                $precio = 0;
                if ($materia->precio_costo != '')
                    $precio = $materia->precio_costo;
            ?>
            <td> {{number_format($precio, 0, "," , "" )}}</td>
            <td>{{$materia->unidad->sigla}}</td>
            <td>{{$materia->proveedorActual->nombre}}</td>

        </tr>
    @endforeach
    </tbody>
</table>
@if(!isset($reporte))
    {!! $materias->render() !!}
@endif