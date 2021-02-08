<?php
    $productosAll = \App\Models\Producto::productosProveedor()->where("estado","Activo")->get();
?>
{!! Form::model($promocion,["id"=>"form-promocion"]) !!}
    <div class="col s12 input-field">
        {!! Form::text("descripcion",null,["id"=>"descripcion","placeholder"=>"Descripción corta de la promoción","class"=>""]) !!}
        {!! Form::label("descripcion","Descripción",["class"=>"active"]) !!}
    </div>
    <div class="col s12 m6 input-field">
        {!! Form::text("fecha_inicio",date("Y-m-d"),["id"=>"fecha_inicio","placeholder"=>"","class"=>"flatpickr"]) !!}
        {!! Form::label("fecha_inicio","Fecha de inicio",["class"=>"active"]) !!}
    </div>
    <div class="col s12 m6 input-field">
        {!! Form::text("fecha_fin",date("Y-m-d"),["id"=>"fecha_fin","placeholder"=>"","class"=>"flatpickr"]) !!}
        {!! Form::label("fecha_fin","Fecha de fin",["class"=>"active"]) !!}
    </div>
    <div class="col s12 input-field margin-bottom-20">
        {!! Form::select("producto",[""=>"seleccione"]+\App\Models\Producto::listaProductosProveedorNombreCategoria(),$promocion->producto_id,["id"=>"producto"]) !!}
        {!! Form::label("producto","Producto",["class"=>""]) !!}
    </div>
    <br>
    <div class="col s12 m6 input-field">
        {!! Form::text("valor_actual",null,["id"=>"valor_actual","class"=>"","disabled"=>"disabled","placeholder"=>" "]) !!}
        {!! Form::label("valor_actual","Valor actual",["class"=>"active"]) !!}
    </div>

    <div class="col s12 m6 input-field">
        {!! Form::text("valor_con_descuento",null,["id"=>"valor_con_descuento","class"=>"num-real","placeholder"=>" "]) !!}
        {!! Form::label("valor_con_descuento","Valor con descuento",["class"=>"active"]) !!}
    </div>

{!! Form::close() !!}

@section('js')
    @parent
    <script>
        var productosAllPrecios = [];
        $(document).ready(function(){
            document.getElementById("fecha_inicio").flatpickr({
                minDate: "today"
            });
            document.getElementById("fecha_fin").flatpickr({
                minDate: "today"
            });
        });
        @foreach($productosAll as $pr)
            productosAllPrecios[{{$pr->id}}] = {{$pr->precio_costo}};
        @endforeach
    </script>
@endsection