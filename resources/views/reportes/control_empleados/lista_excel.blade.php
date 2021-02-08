<table class="table centered highlight">
    <thead>
        <tr>
            <th colspan="5" style="border-bottom:1px solid rgba(0,0,0,.5);">CONTROL DE EMPLEADOS</th>
        </tr> 
        @if(isset($reporte))
        <tr><td></td><td></td><td></td><td></td><td></td></tr>
        @endif
        <tr>
            <th>Fecha</th>
            <th>Nombre</th>
            <th>Cedula</th>
            <th>Fecha de llegada</th>
            <th>Fecha de salida</th>
            @if(Auth::user()->bodegas == 'si')
                <th>Lugar</th>
            @endif
        </tr>
    </thead>
    <tbody >
    @if(isset($reporte))
        <tr><td></td><td></td><td></td><td></td><td></td></tr>
    @endif

    @forelse($empleados as $em)
            <tr>
                <td>{{date("Y-m-d", strtotime($em->creacion_registro)) }}</td>
                <td>{{$em->nombre}}</td>
                <td>{{$em->cedula}}</td>                
                <td>{{$em->fecha_llegada}}</td>
                <td>{{$em->fecha_salida}}</td>

                @if(Auth::user()->bodegas == 'si')
                    <?php
                    $lugar = null;
                    if(Auth::user()->bodegas == 'si'){
                        if($em->bodega_id){
                            $bodega = \App\Models\Bodega::find($em->bodega_id);
                            $lugar = $bodega->nombre;
                        }
                        if($em->almacen_id){
                            $almacen = \App\Models\Almacen::find($em->almacen_id);
                            $lugar = $almacen->nombre;
                        }
                    }
                    ?>
                    <td>{{$lugar}}</td>
                @endif

            </tr>
    @empty
        <tr>
            <td colspan="5"><center>No se han registrado...</center></td>
        </tr>
    @endforelse


    </tbody>
</table>
