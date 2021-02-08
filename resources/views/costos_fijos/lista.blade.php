<table class="bordered highlight centered" id="t_costos_fijos" width="100%">
    <thead>
    <tr>
        <th>Nombre</th>
        <th>Estado</th>
        <th>Fecha pago</th>
        <th>Valor</th>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Pagar","costos fijos","inicio") && \App\Models\Caja::abierta())
            <th>Pagar</th>
        @else
            <th class="hide"></th>
        @endif

        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","costos fijos","inicio"))
            <th >Editar</th>
        @else
            <th class="hide"></th>
        @endif
    </tr>
    </thead>
    <tbody id="datos">
    </tbody>
</table>

<script type="text/javascript">
    $(document).ready(function(){        
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Pagar","costos fijos","inicio") && \App\Models\Caja::abierta())
            setPermisoPagar(true);
        @else
            setPermisoPagar(false);
        @endif

        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","costos fijos","inicio"))
            setPermisoEditar(true);
        @else
            setPermisoEditar(false);
        @endif

        cargaTablaCostosFijos();
    });
</script>




@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","costos fijos","inicio"))
    <div id="modal-editar-costo-fijo" class="modal modal-fixed-footer modal-small" style="max-height: 55% !important;">
        <div class="modal-content">
            <p class="titulo-modal">Editar</p>
            @include("templates.mensajes",["id_contenedor"=>"modal-costos-fijos"])
            <div class="row center-align" id="load-contenido-editar-costo-fijo">
                <i class="fa fa-spinner fa-spin" style="font-size: x-large;"></i>
                <p>Cargando</p>
            </div>
            <div class="row hide" id="contenido-editar-costo-fijo">
                @include('costos_fijos.form')
            </div>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-editar-costo-fijo">
                <a href="#!" class="cyan-text btn-flat" onclick="guardarEdicion();">Aceptar</a>
                <a href="#!" class="red-text modal-close btn-flat">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-editar-costo-fijo">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>
@endif

@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Pagar","costos fijos","inicio"))
    <div id="modal-pagar-costo-fijo" class="modal modal-fixed-footer modal-small" style="max-height: 55% !important;">
        <div class="modal-content">
            <p class="titulo-modal" id="titulo-modal-pagar">Pagar</p>
            @include("templates.mensajes",["id_contenedor"=>"modal-pagar-costos-fijos"])
            <div class="row center-align" id="load-contenido-pagar-costo-fijo">
                <i class="fa fa-spinner fa-spin" style="font-size: x-large;"></i>
                <p>Cargando</p>
            </div>
            <div class="row hide" id="contenido-pagar-costo-fijo">
                <div class="col s12 input-field">
                    {!! Form::text("valor",null,["id"=>"valor","class"=>"num-entero"]) !!}
                    {!! Form::label("valor","Valor",["id"=>"label-valor"]) !!}
                </div>
                <div class="col s12 input-field">
                    {!! Form::input("date","fecha",date("Y-m-d"),["id"=>"fecha"]) !!}
                    {!! Form::label("fecha","Fecha",["id"=>"label-fecha","class"=>"active"]) !!}
                </div>
                <div class="col s12">
                    <p class="font-small">Asegurese de que la información ingresada es correcta, ninguno de los datos registrados podrá ser editado posteriormente.</p>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-pagar-costo-fijo">
                <a href="#!" class="cyan-text btn-flat" onclick="guardarPago();">Aceptar</a>
                <a href="#!" class="red-text modal-close btn-flat">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-pagar-costo-fijo">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>
@endif

