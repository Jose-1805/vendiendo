<div id="modal1" class="modal">
    <div class="modal-content">
        <h4>Detalle de la materia prima</h4>
        <table class="">
            <thead>
            <tr >
                <th>Nombre</th>
                <th>Cantidad</th>
                <th>Unidad</th>
            </tr>
            </thead>
            @foreach($producto->MateriasPrimas as $mp)
                <tr>
                    <td>{{$mp->nombre}}</td>
                    <td>{{$mp->pivot->cantidad}}</td>
                    <td>{{\App\Models\Unidad::find($mp->unidad_id)->sigla}}</td>
                </tr>
            @endforeach
        </table>
    </div>
    <div class="modal-footer">
        <a href="#!" class=" modal-action modal-close waves-effect waves-green btn-flat">Agree</a>
    </div>
</div>