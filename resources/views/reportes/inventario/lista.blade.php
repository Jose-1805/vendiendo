<table class="table centered highlight">
    <thead>
        <th>{{\App\TildeHtml::TildesToHtml('Código de barras')}}</th>
        <th>Nombre producto</th>
        <th>Umbral</th>
        <th>Stock</th>
        @if(
            (Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            ||(Auth::user()->bodegas == 'no')
        )
            <th>Costo unidad</th>
            <th>Costo total</th>
        @endif
        <th>Unidad</th>
        <th>Categor&iacute;a</th>
    </thead>
    <tbody>
    @if(isset($reporte))
        <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
    @endif
    @foreach($productos as $producto)
        <tr>
            <td >{{$producto->barcode}}</td>
            <td >{{\App\TildeHtml::TildesToHtml($producto->nombre)}}</td>
            <td>{{$producto->umbral}}</td>
            <td>{{$producto->stock}}</td>
            @if(
                (Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
                ||(Auth::user()->bodegas == 'no')
            )
                <td>{{number_format($producto->promedio_ponderado, 0, "," , "" )}}</td>
                <td>{{number_format($producto->promedio_ponderado*$producto->stock, 0, "," , "" )}}</td>
            @endif
            <td>{{$producto->unidad->sigla}}</td>
            <td>{{$producto->categoria->nombre}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
@if(!isset($reporte))
    {!! $productos->render() !!}
@endif