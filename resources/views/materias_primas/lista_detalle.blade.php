@if(count($detalle_proveedores)>0)
    <table>
        <thead>
        <tr>
            <th>Nombre</th>
            <th>Dirección</th>
            <th>Teléfono</th>
            <th>Valor</th>
        </tr>
        </thead>
        <tbody>
        @foreach($detalle_proveedores as $dp)
            <tr>
                <td>{{$dp->proveedor_nombre}}</td>
                <td>{{$dp->proveedor_direccion}}</td>
                <td>{{$dp->proveedor_telefono}}</td>
                <td>{{$dp->valor_mp}}</td>
            </tr>
        @endforeach
        @if($materia_prima->imagen != "")
            <tr>
                <td colspan="4">
                    <p class="titulo-modal">Imagen materia prima</p>
                </td>

            </tr>
            <tr>
                <td colspan="4">
                    {!! Html::image(url("/app/public/img/materias_primas/".$materia_prima->imagen), $alt="",
                    $attributes = array('style'=>'max-height: 300px; max-width: 600px','class'=>'materialboxed','data-caption'=>$materia_prima->descripcion)) !!}
                </td>
            </tr>

        @endif
        </tbody>

    </table>
@else
    <table>
        <tr>
            <td colspan="4">Sin resultados</td>
        </tr>
    </table>
@endif


