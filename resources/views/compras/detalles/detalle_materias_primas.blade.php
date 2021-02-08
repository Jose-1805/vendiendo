<div class="content-table-slide col s12">
    <table class="table highlight centered margin-bottom-40" style="min-width: 500px;">
        <thead>
        <th>Materia prima</th>
        <th>Cantidad</th>
        <th>Unidad</th>
        <th>Valor</th>
        <th>Devolución</th>
        </thead>

        <tbody>
        @foreach($compra_metaria_detalle as $materia)
            <tr>
                <?php
                    $m = \App\Models\MateriaPrima::find($materia->id);
                ?>
                <td>{{$materia->nombre}}</td>
                <td>{{$materia->cantidad}}</td>
                <td>{{$m->unidad->nombre}}</td>
                <td>{{"$ ".number_format($materia->valor,2,',','.')}}</td>
                    @if($compra->estado == 'Recibida')
                <td>
                    <a href="#modal-detalle-compra" class="modal-trigger tooltipped" data-tooltip="Devolucion" onclick="openFormDevolution('{{$materia->historial}}',0,'{{$materia->cantidad}}','{{$m->unidad->nombre}}','{{$materia->nombre}}','{{$compra->id}}','MateriaPrima','{{$materia->valor}}','{{$compra->proveedor->id}}','')"><i class="fa fa-thumbs-down red-text" style="cursor: pointer;"></i></a>
                </td>
                    @else
                <td>
                    <a href="#!" class="tooltipped" data-tooltip="Pendiente de recibo" ><i class="fa fa-ban red-text" style="cursor: pointer;"></i></a>
                </td>
                    @endif
            </tr>
        @endforeach
        </tbody>
    </table>
</div>