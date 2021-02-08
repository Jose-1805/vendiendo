<?php
    if(!isset($estado))$estado = true;
?>
@include("templates.mensajes",["id_contenedor"=>"comprasIndex"])
@if(count($lista_devoluciones))
<table>
    <thead>
        @if(isset($reporte) && $reporte)
            <tr>
                <th colspan="7" style="text-align: center">DEVOLUCIONES</th>
            </tr>
        @endif
        <tr>
            <th class="text-center">Elemento</th>
            <th class="text-center" width="25px">Cantidad</th>
            <th class="text-center">Valor</th>
            <th class="text-center">Motivo</th>
            <th class="text-center">Fecha</th>
            <th class="text-center">Proveedor</th>
            <th class="text-center">Estado @if($estado) <br><label style="font-size: 10px">Sin pagar/Pagada</label> @endif </th>
        </tr>
    </thead>
    <tbody>
        @if(isset($reporte) && $reporte)
            <td ></td>
            <td ></td>
            <td ></td>
            <td ></td>
            <td ></td>
            <td ></td>
            <td ></td>
        @endif
        @foreach($lista_devoluciones as $devolucion)
            <tr>
                @if($devolucion->tipo_compra == 'Producto')
                    <td>{{$devolucion->producto->nombre}}</td>
                @else
                    <td>{{$devolucion->materia->nombre}}</td>
                @endif
                    <td class="text-center">{{$devolucion->cantidad_devolucion}}</td>
                    @if(isset($reporte) && $reporte)
                        <td class="text-center">{{number_format($devolucion->valor_devolucion,2,',','')}}</td>
                    @else
                        <td class="text-center">${{number_format($devolucion->valor_devolucion,2,',','.')}}</td>
                    @endif
                    <td class="text-justify">{{$devolucion->motivo}}</td>
                    <td class="text-center">{{$devolucion->fecha_devolucion}}</td>
                    <td class="text-center">{{$devolucion->proveedor->nombre}}</td>
                    <?php
                        $class_fa = "";
                        $tool_tip = "";
                        if($devolucion->forma_pago == "Efectivo"){
                            $class_fa = "fa fa-dollar";
                            $tool_tip = "Pago realizado en efectivo";
                        }else if($devolucion->forma_pago == "Mercancia"){
                            $class_fa = "fa fa-cube";
                            $tool_tip = "Pago realizado con mercancia";
                        }
                    ?>
                    @if($estado)
                        <?php
                        $checked ="";
                        $disabled='';
                        if($devolucion->estado == "PAGADA"){
                            $checked = "checked";
                            $disabled ='disabled';
                        }
                        ?>
                        <td>
                            <div class="switch text-center">
                                <label title="{{$tool_tip}}">
                                    <input type="checkbox" {{ $disabled }}  id="{{$devolucion->id}}" {{$checked}} onclick="cambioEstadoCuentaXCobrar(this.id,'{{$devolucion->estado}}')">
                                    <span class="lever"></span>
                                    <span class="blue-grey-text text-darken-2 {{$class_fa}}"></span>
                                </label>
                            </div>
                        </td>
                    @else
                        <td>
                            <div class="text-center">
                                <p title="{{$tool_tip}}"><span class="blue-grey-text text-darken-2 {{$class_fa}}"></span> {{$devolucion->estado}} @if(isset($reporte) && $reporte)  {{$devolucion->forma_pago}} @endif</p>
                            </div>
                        </td>
                    @endif
            </tr>
        @endforeach
    </tbody>
</table>

@else

            <div class="col s12 text-center"><h3>Sin resultados</h3></div>

@endif
