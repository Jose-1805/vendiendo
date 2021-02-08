<div class="" id="contenedor-secciones" style="">
        <?php
        $class_color = "orange accent-2";
        ?>
        <div class="row contenedor-seccion">

            <div class="col s12 encabezado-session">
                <div class="background-titulo-seccion col s12">
                    <img class="" src="{{ asset('img/sistema/BarraNaranja.png') }}" alt="1" />
                </div>
                <p class="col s12 titulo-seccion">Configuración de migración</p>
            </div>

            <div class="contenedor-items col s12">
                <div class="col s12 grey lighten-2">
                    <p class="font-small blue-text text-accent-2"><strong>Inportante: </strong> a continuación, se encuentran las opciones que debe configurar para completar
                    el proceso de migración a la versión de bodegas y almacenes de <strong>Vendiendo.co</strong>. El sistema desplegará las opciones configurables en color amarillo
                    y las opciones que no son configurables, en determinado momento, aparecerán en color gris; el simbolo (<i class="fa fa-check-circle green-text"></i>) aparecerá en las opciones
                    cuya configuración se ha realizado completa y correctamente. A medida que realice las configuraciones permitidas, el sistema habilitará nuevas opciones si es necesario
                    y así mismo deshabilitará las opciones que ya no puedan estar disponibles.</p>
                </div>
                <div class="col s12 borde-contenedor-items">
                    <?php
                            $active = true;
                            $configured = false;
                            if(\App\Models\Bodega::permitidos()->count())
                                $configured = true;
                    ?>
                    @include('migracion_ab.opcion',["data"=>["responsive_class"=>"s4 m3 l2","href"=>url('bodega'),"color_class"=>$class_color,/*"color_style"=>"",*/"src"=>false,"fa_class"=>"fa-cubes","label"=>"Bodegas","tooltip"=>"Bodegas","active"=>$active,"configured"=>$configured]])

                    <?php
                        $active = true;
                        $configured = false;
                        if(\App\Models\Almacen::permitidos()->count())
                            $configured = true;
                    ?>
                    @include('migracion_ab.opcion',["data"=>["responsive_class"=>"s4 m3 l2","href"=>url('almacen'),"color_class"=>$class_color,/*"color_style"=>"",*/"src"=>false,"fa_class"=>"fa-archive","label"=>"Almacenes","tooltip"=>"Almacenes","active"=>$active,"configured"=>$configured]])

                    <!--configuracion_usuarios-->
                    <?php
                        $privilegios = Auth::user()->privilegiosConfigurarFuncion('usuarios');
                        $active = $privilegios[0];
                        $configured = $privilegios[1];
                    ?>
                    @include('migracion_ab.opcion',["data"=>["responsive_class"=>"s4 m3 l2","href"=>url('migracion-ab/usuarios'),"color_class"=>$class_color,/*"color_style"=>"",*/"src"=>false,"fa_class"=>"fa-user-circle-o","label"=>"Usuarios","tooltip"=>"Usuarios","active"=>$active,"configured"=>$configured]])

                    <!--configuracion_costos_fijos-->
                    <?php
                        $privilegios = Auth::user()->privilegiosConfigurarFuncion('costos fijos');
                        $active = $privilegios[0];
                        $configured = $privilegios[1];
                    ?>
                    @include('migracion_ab.opcion',["data"=>["responsive_class"=>"s4 m3 l2","href"=>url('migracion-ab/costos-fijos'),"color_class"=>$class_color,/*"color_style"=>"",*/"src"=>false,"fa_class"=>"fa-money","label"=>"Costos fijos","tooltip"=>"Costos fijos","active"=>$active,"configured"=>$configured]])

                    <!--configuracion_objetivos_ventas-->
                    <?php
                        $privilegios = Auth::user()->privilegiosConfigurarFuncion('objetivos ventas');
                        $active = $privilegios[0];
                        $configured = $privilegios[1];
                    ?>
                    @include('migracion_ab.opcion',["data"=>["responsive_class"=>"s4 m3 l2","href"=>url('migracion-ab/objetivos-ventas'),"color_class"=>$class_color,/*"color_style"=>"",*/"src"=>false,"fa_class"=>"fa-line-chart","label"=>"Objetivos de ventas","tooltip"=>"Objetivos de ventas","active"=>$active,"configured"=>$configured]])

                    <!--configuracion_productos-->
                    <?php
                        $privilegios = Auth::user()->privilegiosConfigurarFuncion('productos');
                        $active = $privilegios[0];
                        $configured = $privilegios[1];
                    ?>
                    @include('migracion_ab.opcion',["data"=>["responsive_class"=>"s4 m3 l2","href"=>url('migracion-ab/productos'),"color_class"=>$class_color,/*"color_style"=>"",*/"src"=>false,"fa_class"=>"fa-bell","label"=>"Productos","tooltip"=>"Productos","active"=>$active,"configured"=>$configured]])

                    <!--configuracion_facturas-->
                    <?php
                        $privilegios = Auth::user()->privilegiosConfigurarFuncion('facturas');
                        $active = $privilegios[0];
                        $configured = $privilegios[1];
                    ?>
                    @include('migracion_ab.opcion',["data"=>["responsive_class"=>"s4 m3 l2","href"=>url('migracion-ab/facturas'),"color_class"=>$class_color,/*"color_style"=>"",*/"src"=>false,"fa_class"=>"fa-calculator","label"=>"Facturas","tooltip"=>"Facturas","active"=>$active,"configured"=>$configured]])

                    <!--configuracion_remisiones-->
                    <?php
                        $privilegios = Auth::user()->privilegiosConfigurarFuncion('remisiones');
                        $active = $privilegios[0];
                        $configured = $privilegios[1];
                    ?>
                    @include('migracion_ab.opcion',["data"=>["responsive_class"=>"s4 m3 l2","href"=>url('migracion-ab/remisiones'),"color_class"=>$class_color,/*"color_style"=>"",*/"src"=>false,"fa_class"=>"fa-paper-plane","label"=>"Remisiones","tooltip"=>"Remisiones","active"=>$active,"configured"=>$configured]])
                </div>
            </div>
        </div>
</div>

@section('js')
    @parent
@stop