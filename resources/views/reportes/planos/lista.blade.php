<table class="table centered highlight" id="t_rep_planos" width="100%">
    <thead>
        <th>Nombre</th>
        <th>Seccion</th>
        <th>Campos</th>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","Reportes planos","reportes"))
            <th>Acción</th>
        @else
            <th class="hide"></th>
        @endif
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","Reportes planos","reportes"))
            <th >Eliminar</th>
        @else
            <th class="hide"></th>
        @endif
        <th>Exportar</th>
    </thead>
    <tbody>
    </tbody>
</table>

<script type="text/javascript">
    $(document).ready(function(){        
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","Reportes planos","reportes"))
            setPermisoEdit(true);
        @else
            setPermisoEdit(false);
        @endif

        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","Reportes planos","reportes"))
            setPermisoDelete(true);
        @else
            setPermisoDelete(false);
        @endif

        cargaTablaReportesPlanos();
    });
</script>







@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","Reportes planos","reportes"))
    <div id="modal-eliminar-reporte-plano" class="modal modal-fixed-footer modal-small">
        <div class="modal-content">
            <p class="titulo-modal">Eliminar</p>
            <p>¿Está seguro de eliminar este reporte plano?</p>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-eliminar-reporte-plano">
                <a href="#!" class="red-text btn-flat" onclick="javascript: eliminarReportePlano(id_select)">Aceptar</a>
                <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-eliminar-reporte-plano">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>
@endif
<div id="modal-campos-reporte" class="modal modal-fixed-footer">
    <div class="modal-content">
        <p class="titulo-modal">Listado de campos del reporte</p>
        <div class="col s12" id="contenido-campos-reporte" style="width: 100%">

        </div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close cyan-text btn-flat">Cerrar</a>
    </div>
</div>
<div id="modal-exportar-reporte-plano" class="modal modal-fixed-footer modal-sm">
    <div class="modal-content">
        <?php
        $fecha_inicio = date("Y-m-d",strtotime("-1month",strtotime(date("Y-m-d"))));
        $fecha_fin= date("Y-m-d");
        ?>
        <p class="titulo-modal">Exportar reporte plano</p>
        <div id="datos-reporte-plano" class="col s12 m12">
            <div class="col s12 m6 input-field">
                <input type="text" value="{{$fecha_inicio}}" name="fecha_inicial" id="fecha_inicial"
                       class='flatpickr' data-enable-time=false data-time_24hr=false/>
                {!! Form::label("fecha_inicial","Fecha inicio",["class"=>"active"]) !!}
            </div>
            <div class="col s12 m6 input-field">
                <input type="text" value="{{$fecha_fin}}" name="fecha_final" id="fecha_final"
                       class='flatpickr' data-enable-time=false data-time_24hr=false/>
                {!! Form::label("fecha_final","Fecha fin",["class"=>"active"]) !!}
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-eliminar-reporte-plano">
            <a href="#!" class="red-text btn-flat" onclick="fijarDatosReportePlano()">Aceptar</a>
            <a href="#!" class="modal-close cyan-text btn-flat" onclick="window.location.reload(true);">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-eliminar-reporte-plano">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>
@if(!isset($reporte))
{!! $reportes_planos->render() !!}
@endif

