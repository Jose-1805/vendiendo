@if(count($historiales))<ul class="collection">@endif
@forelse($historiales as $historial)
    <li class="collection-item">
        <p>{{$historial->observacion}}</p>
        <p class="font-small grey-text text-darken-1 right-align" style="margin: 0px !important;">{{$historial->usuario->nombres." ".$historial->usuario->apellidos}} - <i>{{$historial->created_at}}</i></p>
    </li>
@empty
    <p class="center-align">No se ha registrado ningún historial.</p>
@endforelse
@if(count($historiales))</ul>@endif