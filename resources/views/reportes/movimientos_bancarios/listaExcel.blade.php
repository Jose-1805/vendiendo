<table class="table centered highlight">
    <thead>
        <th>Fecha</th>
        <th>Cuenta</th>
        <th>Tipo de movimiento</th>
        <th>Desde</th>
        <th>Valor</th>
        <th>Saldo</th>
    </thead>
    <tbody>
    @if(isset($reporte))
        <tr><td></td><td></td><td></td><td></td><td>
    @endif
    @forelse($elementos as $el)
        <tr>
            <td >{!! $el->created_at !!}</td>
            <td class="truncate" style="max-width: 300px !important;">{!! \App\TildeHtml::TildesToHtml($el->cuenta) !!}</td>
            <td>{!! \App\TildeHtml::TildesToHtml($el->tipo) !!}</td>
            <td >{{$el->desde}}</td>
            <td>{{$el->valor}}</td>
            <td>{{$el->saldo}}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="center-align">No se encontraron productos</td>
        </tr>
    @endforelse
    </tbody>
</table>
@if(!isset($reporte))
    {!! $elementos->render() !!}
@endif
