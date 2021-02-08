@if(count($detalle_productos)>0)
            <?php
                if($tipo_producto=='Terminado'){
                    $data = [];

                }

            ?>
            <table class="">
            <thead>
            @if($tipo_producto!='Terminado')
                <tr>
                    <th>Nombre</th>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                </tr>
            @else
                <tr>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>Precio costo</th>
                </tr>
            @endif
            </thead>
            @foreach($detalle_productos as $mp)
                @if($tipo_producto!='Terminado')
                <tr>
                    <td>{{$mp->nombre}}</td>
                    <td>{{$mp->cantidad}}</td>
                    <td>{{$mp->unidad->sigla}}</td>
                </tr>
                @else
                    <tr>
                        <td>{{$mp->nombre}}</td>
                        <td>{{$mp->direccion}}</td>
                        <td>{{$mp->telefono}}</td>
                        <td>$ {{number_format($mp->valor,2,',','.')}}</td>
                    </tr>
                @endif
            @endforeach
                @if($producto->imagen != "")
                    <tr>
                        <td colspan="4">
                            <p class="titulo-modal">Imagen producto</p>
                        </td>

                    </tr>
                    <tr>
                        <td colspan="4">
                            {!! Html::image(url("/app/public/img/productos/".$producto->id."/".$producto->imagen), $alt="",
                            $attributes = array('style'=>'max-height: 300px; max-width: 600px','class'=>'materialboxed','data-caption'=>$producto->descripcion)) !!}
                        </td>
                    </tr>

                @endif
        </table>
@else
    <table>
        <tr>
            <td colspan="4">Sin resultados</td>
        </tr>
    </table>
@endif