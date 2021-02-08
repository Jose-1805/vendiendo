<div id="div-lista-categorias" class="col s12 m7 l8  {{$class_color}}" style="padding: 0px;">
    <div id="Listado" class="col s12">

        <ul class="tabs {{$class_color}}">
            @foreach($categorias as $key => $categoria)
                <li class="tab">
                    <a href="#categoria_{{$categoria->id}}">
                        {{$categoria->nombre}}
                    </a>
                </li>
            @endforeach
        </ul>

    </div>
</div>
<div class="col l1 center-align hide-on-med-and-down" id="contenedo-botones-tipo-vista">
    <a href="#!" id="btn-dos-columnas" class="tooltipped waves-effect waves-light" data-position="bottom" data-delay="50" data-tooltip="Dos columnas"><i class="fa fa-th-large"></i></a>
    <a href="#!" id="btn-tres-columnas" class="tooltipped waves-effect waves-light" data-position="bottom" data-delay="50" data-tooltip="Tres columnas"><i class="fa fa-th"></i></a>
</div>
<div id="div-lista-ventas" class="col s11 m5 l3 ">
    <div class="col s11 m10">
        <input type="search" name="buscar" id="buscar" placeholder="Busca por nombre o código de barras">
        <i class="fa fa-search white-text" id="icon-buscar"></i>
        <div class="card borde-contenedor-items hoverable" id="contenido-buscar"></div>

    </div>
    <div class="col s1 m2 text-center">
        <a style="pointer-events: auto; padding: 20px 10px !important; line-height:50px; color:white; " class='btn-home' href='{{url("/")}}'><i class="fa fa-home"></i></a>
    </div>
</div>