

@if(count($modulos_inicio))
    <?php
    $class_color = "orange accent-2";
    /**
     * HACER LAS CONSULTAS NECESARIAS PARA LAS GRAFICAS QUE SE VAN A GENERAR
     */
    if(Auth::user()->panel_graficas == "si"){
        //GRAFICA PARA OBJETIVOS DE VENTAS
        if(Auth::user()->plan()->objetivos_ventas == "si"){
            /*$objetivos_ventas = \App\Models\ObjetivoVenta::permitidos()->orderBy("anio","ASC")->orderBy("mes","ASC")
                    ->where(function($q){
                        $q->where("anio","=",date("Y"))
                                ->where("mes","<=",date("m"));
                    })->orWhere(function($q){
                        $q->where("anio","<",date("Y"));
                    });


            $skip = $objetivos_ventas->get()->count() - 4;*/
            $objetivos_ventas = \App\Models\ObjetivoVenta::GraficaObjetivoVentas();

            //$objetivos_ventas = $objetivos_ventas->skip($skip)->take(4)->get();
        }
        if(count($objetivos_ventas)){
            $meses = [
                    1 => "Enero",
                    2 => "Febrero",
                    3 => "Marzo",
                    4 => "Abril",
                    5 => "Mayo",
                    6 => "Junio",
                    7 => "Julio",
                    8 => "Agosto",
                    9 => "Septiembre",
                    10 => "Octubre",
                    11 => "Noviembre",
                    12 => "Diciembre",
            ];
            $mes = $meses[$objetivos_ventas[count($objetivos_ventas)-1]->mes];
        }

        //GRAFICAS DE CAJAS ACTIVAS
        if((Auth::user()->perfil->nombre == "administrador" && \App\Models\Cajas::cajasUsuariosActivo()->get()->count()) || Auth::user()->cajaAsignada()){
            if(
                (Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'no')
                ||(Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            ){
                $cajas = \App\Models\Cajas::cajasUsuariosActivo()->select("cajas.*","cajas_usuarios.valor_inicial","cajas_usuarios.valor_final")->get();
            }else{
                $cajas = Auth::user()->cajas()->select("cajas.*","cajas_usuarios.valor_inicial","cajas_usuarios.valor_final")
                        ->where("cajas_usuarios.estado","activo");
                if(Auth::user()->bdegas == 'si'){
                    $almacen = Auth::user()->almacenActual();
                    if($almacen)
                        $cajas = $cajas->where('cajas.almacen_id',$almacen.id);
                }
                $cajas = $cajas->get();
            }
        }
    }
    ?>


    <div class="row contenedor-seccion" id="inicio" >
        <div class="col s12 encabezado-session">
            <div class="background-titulo-seccion col s12">
                <img class="" src="{{ asset('img/sistema/BarraNaranja.png') }}" alt="1" />
            </div>
            <p class="col s12 titulo-seccion">Inicio</p>
        </div>
        <?php
        $aux = 0;
        $categoria = "";
        $nueva = false;
        $pintar = true;
        $size_cont_items = 12;
        $size_item_l = "l2";
        $size_item_m = "m3";
        if(Auth::user()->panel_graficas == "si"){
            $size_cont_items = 6;
            $size_item_l = "l3";
            $size_item_m = "m4";
        }
        ?>
        {{--{{var_dump($modulos_inicio)}}--}}
        <div class="contenedor-items col s12">
            <div class="col s12 m{{$size_cont_items}} borde-contenedor-items">

                @forelse($modulos_inicio as $m)
                    <?php
                    if($m->nombre == "facturas" && $m->seccion == "inicio"){
                        if(Auth::user()->perfil->nombre != "administrador"){
                            if(!(Auth::user()->cajaAsignada() && Auth::user()->permitirFuncion("Crear","facturas","inicio"))){
                                $pintar = false;
                            }
                        }
                    }

                    if(Auth::user()->bodegas == "si" && Auth::user()->admin_bodegas == "si" && $m->privilegio_administrador_bodegas == "no")
                        $pintar = false;
                    ?>
                    @if($pintar)
                        <?php
                        if($categoria != $m->categoria){
                            $categoria = $m->categoria;
                            $nueva = true;
                            $aux = 0;
                        }else{
                            $nueva = false;
                        }
                        ?>
                        @if($nueva)
                            <p class="col s12 grey-text darken-2 titulo-modal center-align margin-top-50 padding-top-20">{{$categoria}}</p>
                        @endif

                        @include('templates.secciones.item-menu',["data"=>["responsive_class"=>"s4 ".$size_item_m." ".$size_item_l,"href"=>$m->url,"color_class"=>$class_color,/*"color_style"=>"",*/"src"=>$m->image_url,"fa_class"=>$m->icon_class,"label"=>$m->label,"tooltip"=>$m->tooltip]])
                        <?php $aux++; ?>
                        @if(Auth::user()->panel_graficas == "no")
                            @if($aux % 4 == 0)
                                <div class="row hide-on-large-only hide-on-small-only"></div>
                            @endif
                            @if($aux % 3 == 0)
                                <div class="row hide-on-med-and-up"></div>
                            @endif
                            @if($aux % 6 == 0)
                                <div class="row hide-on-med-and-down"></div>
                            @endif
                        @else
                            @if($aux % 4 == 0)
                                <div class="row hide-on-med-and-down"></div>
                            @endif
                            @if($aux % 3 == 0)
                                <div class="row hide-on-large-only"></div>
                            @endif
                        @endif
                    @else
                        <?php
                        $pintar = true;
                        ?>
                    @endif
                @empty
                    <h5 class="center-align">No se han asignado módulos en esta sección.</h5>
                @endforelse

                @if(count($modulos_inicio) && $aux == 0)
                    <h5 class="center-align">En este momento no existen módulos habilitados para su usuario en esta sección.</h5>
                @endif

            </div>

            @if(Auth::user()->panel_graficas == "si")
                <div class="col s12 m6 borde-contenedor-items white hide-on-small-only padding-top-30" style="min-height: 100%;">
                    @if(Auth::user()->plan()->objetivos_ventas == "si")
                        @if(count($objetivos_ventas))
                            <div class="col s12 padding-10"><div class="col s12" style="border: solid 1px rgba(0,0,0,.1);width: 100%;height: 300px;" id="grafica-objetivos_ventas"></div></div>
                            <div class="col s12 m4 padding-10"><div class="col s12" style="border: solid 1px rgba(0,0,0,.1);width: 100%;height: 150px;" id="objetivos_ventas_mes"></div></div>
                        @else
                            <div class="col s12 m4 padding-10">
                                <div class="col s12 text-center" style="border: solid 1px rgba(0,0,0,.1);">
                                    @if(
                                        (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
                                        ||(Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && AUth::user()->admin_bodegas == 'si')
                                    )
                                        <p>Para crear objetivos de ventas ingrese <a href="{{url('/objetivos-ventas')}}">aquí</a></p>
                                    @else
                                        <p>No se han asignado objetivos de ventas</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endif

                    @if((Auth::user()->perfil->nombre == "administrador" && \App\Models\Cajas::cajasUsuariosActivo()->get()->count()) || Auth::user()->cajaAsignada())
                        <div class="col s12 m8 padding-10"><div class="col s12" style="border: solid 1px rgba(0,0,0,.1);width: 100%;height: 150px;" id="cajas_activas"></div></div>
                    @endif
                </div>
            @endif
        </div>
        <div class="footer-items col s12"></div>
    </div>
@endif

@section('js')
    @parent

    @if(Auth::user()->panel_graficas == "si")
        <script type="text/javascript">
            google.charts.load('current', {'packages':['corechart','bar']});
            @if(Auth::user()->plan()->objetivos_ventas == "si" && count($objetivos_ventas))
                google.charts.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Año/Mes', 'Ventas', 'Objetivo'],
                        <?php
                        $ik=0;
                        ?>
                        @foreach($objetivos_ventas as $o)
                    ['{{$o->anio."/".$o->mes}}',  {{ (isset($o->valorAcumulado)?$o->valorAcumulado:0) }}, {{ $o->valor }}],
                        <?php
                        if(isset($o->valorAcumulado) && $o->valorAcumulado>0)
                            $ik++;
                        ?>

                        @endforeach

                        @if($ik==1)
                    [null,null,null],
                    @endif
                ]);

                var options = {
                    title: 'Objetivos de ventas',
                    hAxis: {title: 'Mes',  titleTextStyle: {color: '#333'}},
                    vAxis: {minValue: 0},
                    chartArea:{top:40,width:'50%'}
                };

                var chart = new google.visualization.AreaChart(document.getElementById('grafica-objetivos_ventas'));
                chart.draw(data, options);
            }

            google.charts.setOnLoadCallback(drawChart2);
            function drawChart2() {
                var data = google.visualization.arrayToDataTable([
                    ['', ''],
                        @if((isset($o->valorAcumulado)?$o->valorAcumulado:0) >= $o->valor)
                    ['Ventas',  {{(isset($o->valorAcumulado)?$o->valorAcumulado:0)}}],
                    ['Faltante',  0],
                        @else
                    ['Ventas',  {{(isset($o->valorAcumulado)?$o->valorAcumulado:0)}}],
                    ['Faltante',  {{$o->valor - (isset($o->valorAcumulado)?(isset($o->valorAcumulado)?$o->valorAcumulado:0):0)}}],
                    @endif
                ]);

                var options = {
                    title: 'Objetivo de ventas {{$mes}} ',
                    pieHole: 0.5,
                    chartArea:{top:40,width:'90%'},
                    legend: {position: 'bottom'}

                };

                var chart = new google.visualization.PieChart(document.getElementById('objetivos_ventas_mes'));
                chart.draw(data, options);
            }
            @endif

            @if((Auth::user()->perfil->nombre == "administrador" && \App\Models\Cajas::cajasUsuariosActivo()->get()->count()) || Auth::user()->cajaAsignada())
                google.charts.setOnLoadCallback(drawChart3);
            function drawChart3() {
                var data = google.visualization.arrayToDataTable([
                    ['Cajas', 'Inicial ($)', 'Actual ($)', 'Venta ($)'],
                        @foreach($cajas as $c)
                    ['{{$c->nombre}}',{{$c->valor_inicial}},{{$c->valor_final}},{{$c->valor_final - $c->valor_inicial}}],
                    @endforeach
                ]);

                var options = {
                    chart:{
                        title: 'Cajas activas',
                    },
                    bars: 'vertical',
                    vAxis: {format: 'decimal'},
                };

                var chart = new google.charts.Bar(document.getElementById('cajas_activas'));
                chart.draw(data, options);
            }
            @endif
        </script>

    @endif
@endsection