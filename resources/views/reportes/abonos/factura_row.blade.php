<tr>
    <td >{{$elemento->numero}}</td>
    <td >{{$elemento->cliente->nombre .' - '. $elemento->cliente->identificacion }}</td>
    <td >{{$elemento->created_at}}</td>
    <td > {{number_format(($elemento->subtotal + $elemento->iva),2,',','')}}</td>
    <td > {{number_format($elemento->getSaldo(),2,',','')}}</td>
    <?php
        if($elemento->numero_cuotas == "")$elemento->numero_cuotas = 0;
    ?>
    <td >{{$elemento->numero_cuotas}}</td>
    <td >{{count($elemento->abonos)}}</td>
    <?php
        $ultimoAbono = $elemento->ultimoAbono();
    ?>
    @if($ultimoAbono)
        <td >{{$ultimoAbono->created_at}}</td>
    @else
        <td></td>
    @endif
</tr>