<?php $numColumns = 2; ?>
<table class="bordered highlight centered">
    <thead>
    <tr>
        <th>Nombre</th>
        <th>{!! \App\TildeHtml::TildesToHtml("Duración") !!}</th>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","planes","configuracion"))
            <th >Editar</th>
            <?php $numColumns++; ?>
        @endif
    </tr>
    </thead>
    <tbody id="datos">
        @forelse($planes as $plan)
            <tr>
                <td>{{$plan->nombre}}</td>
                <td>{{$plan->duracion." Meses"}}</td>

                @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","planes","configuracion"))
                    <td><a href="{{url('/plan/edit/'.$plan->id)}}"><i class="fa fa-pencil-square-o fa-2x" style="cursor: pointer;"></i></a></td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{$numColumns}}" class="center"><p>Sin resultados</p></td>
            </tr>
        @endforelse
    </tbody>

</table>
{!! $planes->render() !!}

