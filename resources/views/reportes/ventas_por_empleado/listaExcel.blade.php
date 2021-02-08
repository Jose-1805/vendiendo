<table class="table centered highlight">
    <thead>
        <th>Nombre</th>
        <th>Alias</th>
        <th>Perfil</th>
        <th>Total vendido</th>
    </thead>
    <tbody>
    @if(isset($reporte))
        <tr><td></td><td></td><td></td><td>
    @endif
    @forelse($empleados as $e)
        <tr>
            <td >{!! \App\TildeHtml::TildesToHtml($e->nombres." ".$e->apellidos) !!}</td>
            <td >{!! \App\TildeHtml::TildesToHtml($e->alias) !!}</td>
            <td class="truncate" style="max-width: 300px !important;">{!! $e->perfil !!}</td>
            <td>{{number_format($e->total,2,',','')}}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="center-align">No se encontraron resultados</td>
        </tr>
    @endforelse
    </tbody>
</table>
@if(!isset($reporte))
    {!! $empleados->render() !!}
@endif
