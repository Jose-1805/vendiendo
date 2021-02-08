@include("templates.mensajes",["id_contenedor"=>"lista-resoluciones"])
<?php
$numColumns = 5;
?>
<table class="bordered highlight centered">
    <thead>
    <tr>
        <th >Número</th>
        <th >Fecha emisión</th>
        <th >Fecha vencimiento</th>
        <th >Fecha notificación vencimiento</th>
        <th >Número inicio factura</th>
        <th >Número fin factura</th>
        <th >Número notificación vencimiento</th>
        <th >Estado</th>

        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("EditarResolucion","facturacion","configuracion"))
            <th >Editar</th>
            <?php $numColumns++; ?>
        @endif

        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("EliminarResolucion","facturacion","configuracion"))
            <th >Eliminar</th>
            <?php $numColumns++; ?>
        @endif

    </tr>
    </thead>

    <tbody>
    @if(count($resoluciones))
        @foreach($resoluciones as $resolucion)
            <tr>
                <td>{{$resolucion->numero}}</td>
                <td>{{$resolucion->fecha}}</td>
                <td>{{$resolucion->fecha_vencimiento}}</td>
                <td>{{$resolucion->fecha_notificacion}}</td>
                <td>{{$resolucion->inicio}}</td>
                <?php
                    $numero = "";
                    if($resolucion->estado == "activa" || $resolucion->estado == "terminada"){
                        $numero = $resolucion->inicio;
                        if(count($resolucion->facturas)){
                            $numero = ($numero-1) + count($resolucion->facturas);
                        }else{
                            $numero = "-";
                        }
                        $numero = "(".$numero.")";
                    }
                ?>
                <td>{{$resolucion->fin." ".$numero}}</td>
                <td>{{$resolucion->numero_notificacion}}</td>
                <td>{{$resolucion->estado}}</td>

                @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("EditarResolucion","facturacion","configuracion"))
                    <!-- CUANDO EL MODULO DE FACTURAS ESTE CREADO SE QUITAN LOS PARENTESIS DE $resolucion->facturas() -->
                    @if(!count($resolucion->facturas))
                    <td><a href="#" id="edit_{{$resolucion->id}}" onclick="traerEditarResolucion({{$resolucion->id}})"><i class="fa fa-pencil-square-o fa-2x" style="cursor: pointer;"></i></a><i id="load_edit_{{$resolucion->id}}" class="fa fa-spinner fa-spin hide"></i></td>
                    @else
                        <td></td>
                    @endif
                @endif

                @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("EliminarResolucion","facturacion","configuracion"))
                    <!-- CUANDO EL MODULO DE FACTURAS ESTE CREADO SE QUITAN LOS PARENTESIS DE $resolucion->facturas() -->
                    @if(!count($resolucion->facturas))
                        @if($resolucion->isLast())
                            <td><a href="#modal-eliminar-resolucion" class="modal-trigger" onclick="javascript: id_select = {{$resolucion->id}}"><i class="fa fa-trash fa-2x" style="cursor: pointer;"></i></a></td>
                        @else
                            <td></td>
                        @endif
                    @else
                        <td></td>
                    @endif
                @endif
            </tr>
        @endforeach
    @else
        <tr>
            <td colspan="{{$numColumns}}" class="center"><p>Sin resultados</p></td>
        </tr>
    @endif
    </tbody>
</table>



@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("EditarResolucion","facturacion","configuracion"))
    <div id="modal-editar-resolucion" class="modal modal-fixed-footer" style="max-height: 60% !important;">
        <div class="modal-content">
            <p class="titulo-modal">Editar</p>
            @include("templates.mensajes",["id_contenedor"=>"modal-editar-resolucion"])
            <div id="contenedor-datos-resolucion">

            </div>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-editar-resolucion">
                <a href="#!" class="red-text btn-flat" onclick="javascript: editarResolucion()">Aceptar</a>
                <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-editar-resolucion">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>
@endif

@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("EliminarResolucion","facturacion","configuracion"))
    <div id="modal-eliminar-resolucion" class="modal modal-fixed-footer modal-small">
        <div class="modal-content">
            <p class="titulo-modal">Eliminar</p>
            @include("templates.mensajes",["id_contenedor"=>"modal-eliminar-resolucion"])
            <p>¿Está seguro de eliminar esta resolución?</p>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-eliminar-resolucion">
                <a href="#!" class="red-text btn-flat" onclick="javascript: eliminarResolucion(id_select)">Aceptar</a>
                <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-eliminar-resolucion">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>
@endif

{!! $resoluciones->render() !!}