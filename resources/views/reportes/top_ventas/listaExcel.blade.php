<table class="table centered highlight">
    <thead>
        <th>Nombre</th>
        <th>{!! \App\TildeHtml::TildesToHtml('Descripción') !!}</th>
        <th>{!! \App\TildeHtml::TildesToHtml('Categoría') !!}</th>
        <th>Cantidad vendida</th>
        <th>Stock</th>
    </thead>
    <tbody>
    @if(isset($reporte))
        <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
    @endif
    @forelse($productos as $producto)
        <tr>
            <td >{!! \App\TildeHtml::TildesToHtml($producto->nombre) !!}</td>
            <td class="truncate" style="max-width: 300px !important;">{!! \App\TildeHtml::TildesToHtml($producto->descripcion) !!}</td>
            <td>{!! \App\TildeHtml::TildesToHtml($producto->categoria->nombre) !!}</td>
            <td >{{$producto->cantidad_vendida}}</td>
            <td>{{$producto->stock}}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="center-align">No se encontraron productos</td>
        </tr>
    @endforelse
    </tbody>
</table>
@if(!isset($reporte))
    {!! $productos->render() !!}
@endif
