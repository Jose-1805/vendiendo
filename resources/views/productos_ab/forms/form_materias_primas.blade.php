<?php
$valor = 0;
?>
<div class="input-field col s12 m12" id="materia-prima-div" >
    @if(\App\Models\MateriaPrima::materiasPrimasPermitidas()->get()->count() > 0)
        <p class="titulo col s12">Materias primas</p>
        <div id="contenedor-materia-prima-productos">
            @if($accion == "create")
                <div id="materia-1" class="materias-primas col s12">
                    <a class="right"><i class="fa fa-close"></i></a>
                    <div class="input-field col s12 m4">
                        <select class="select-materia-prima" id="select-materia-prima-1" name="select-materia-prima-1">
                            <option disabled selected>Seleccione una materia prima</option>
                            @foreach(\App\Models\MateriaPrima::materiasPrimasPermitidas()->get() as $pr)
                                <option data-valor="{{$pr->precioActual()}}" data-unidad="{{$pr->unidad->nombre}}" value="{{$pr->id}}">{{$pr->nombre}}</option>
                            @endforeach
                        </select>
                        <label>Materia prima</label>
                    </div>
                    <div class="input-field col s12 m2">
                        {!!Form::label("","Precio unitario",["class"=>"active"])!!}
                        {!!Form::text("precio_costo","",["id"=>"precio_unitario","disabled"=>"disabled"])!!}
                    </div>
                    <div class="input-field col s12 m2">
                        {!!Form::label("","Unidad",["class"=>"active"])!!}
                        {!!Form::text("unidad","Unidad",["id"=>"unidad","disabled"=>"disabled"])!!}
                    </div>

                    <div class="input-field col s12 m3">
                        {!!Form::label(null,"Cantidad",["class"=>"active"])!!}
                        {!!Form::text("cantidad-1",null,["id"=>"cantidad-1","maxlength"=>"10","class"=>"num-real cantidad_mp"])!!}
                    </div>
                </div>
            @elseif($accion == 'edit')
                @if(count($detalle_producto)>0)
                    <?php $aux = 1; ?>
                    @foreach($detalle_producto as $obj)
                        <div id="materia-{{$aux}}" class="materias-primas col s12">
                            <a class="right"><i class="fa fa-close"></i></a>
                            <?php
                            $unidad = "Unidad";
                            ?>
                            <div class="input-field col s12 m4">
                                <select class="select-materia-prima" id="select-materia-prima-{{$aux}}" name="select-materia-prima-{{$aux}}">
                                    <option disabled selected>Seleccione una materia prima</option>
                                    @foreach(\App\Models\MateriaPrima::materiasPrimasPermitidas()->get() as $pr)
                                        @if($obj->id == $pr->id)
                                            <?php
                                                $valor += ($pr->precio_costo * $obj->cantidad);
                                            ?>
                                            <option data-valor="{{$pr->precioActual()}}" data-unidad="{{$pr->unidad->nombre}}" value="{{$pr->id}}" selected>{{$pr->nombre}}</option>
                                            <?php
                                            $unidad = $pr->unidad->nombre;
                                            $precio_unitario = $pr->precio_costo;
                                            ?>
                                        @else
                                            <option data-valor="{{$pr->precioActual()}}" data-unidad="{{$pr->unidad->nombre}}" value="{{$pr->id}}">{{$pr->nombre}}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <label>Materia prima</label>
                            </div>
                            <div class="input-field col s12 m2">
                                {!!Form::label("","Precio unitario",["class"=>"active"])!!}
                                {!!Form::text("precio_costo",$precio_unitario,["id"=>"precio_unitario","disabled"=>"disabled"])!!}
                            </div>
                            <div class="input-field col s12 m2">
                                {!!Form::label("","Unidad",["class"=>"active"])!!}
                                {!!Form::text("unidad",$unidad,["id"=>"unidad","disabled"=>"disabled"])!!}
                            </div>
                            <div class="input-field col s12 m3">
                                {!!Form::label(null,"Cantidad",["class"=>"active"])!!}
                                {!!Form::text("cantidad-".$aux,$obj->cantidad,["id"=>"cantidad-".$aux,"maxlength"=>"10","class"=>"num-real cantidad_mp"])!!}
                            </div>
                        </div>
                        <?php $aux++; ?>
                    @endforeach
                @else
                    <p class="col s12 center">No tiene materias primas inscritas</p>
                @endif
            @endif

        </div>
        <div class="col s12">
            <p style="font-size: 20px;" class="blue-grey-text text-darken-2"><strong>Costo materias primas:</strong> <span id="costo_materias_primas">$ {{number_format($valor,2,',','.')}}</span></p>
            <a class="btn-floating right blue-grey darken-2 tooltipped" style="margin-top: -47px;" onclick="agregarMateriaProducto(this)" data-position="left" data-delay="50" data-tooltip="Agregar materia prima"><i class="fa fa-plus"></i></a>
        </div>
</div>
@else
    <p class="col s12 center">No es posible registrar productos si no existen materias primas relacionados con el administrador</p>
@endif