<table class="table centered highlight">
    <thead>
        <th>N&uacute;mero</th>
        <th>Valor</th>
        <th>Fecha</th>
        <th>Proveedor</th>
        <th>Usuario</th>
        <th>Estado compra</th>
        <th>Estado pago</th>
        <th>Devoluciones</th>
    </thead>
    <tbody>
    @if(isset($reporte))
        <tr><td></td><td></td><td></td></tr>
    @endif
    @forelse($compras as $compra)
        <tr>
            <td >{{$compra->numero}}</td>
            <td >{{$compra->valor}}</td>
            <td >{{$compra->fecha}}</td>
            <td >{{$compra->proveedor}}</td>
            <td >{{$compra->usuario}}</td>
            <td >{{$compra->estado}}</td>
            <td >{{$compra->estado_pago}}</td>
            <td>{{$compra->devoluciones}}</td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="center-align">No se encontraron registros</td>
        </tr>
    @endforelse
    </tbody>
</table>
@if(!isset($reporte))
    {!! $compras->render() !!}
@endif
