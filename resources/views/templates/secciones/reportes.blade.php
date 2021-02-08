@if(\Illuminate\Support\Facades\Auth::user()->perfil->nombre == "usuario" || \Illuminate\Support\Facades\Auth::user()->perfil->nombre == "administrador")
    <?php $color_class = "green lighten-1"?>
    <div class="row contenedor-seccion" id="reportes" style="display: none;">

        <div class="col s12 encabezado-session">
            <div class="background-titulo-seccion col s12">
                <img class="" src="{{ asset('img/sistema/BarraVerde.png') }}" alt="1" />
            </div>
            <p class="col s12 titulo-seccion">Reportes</p>
        </div>

        <div class="contenedor-items col s12">
            <div class="borde-contenedor-items">
                <?php $aux = 0;$categoria = ""; ?>
                @forelse($reportesPermitidos as $r)
                    @if(
                        (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
                        || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
                        || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->reporte_id))
                        || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
                    )
                        @if($r->categoria != $categoria)
                            <?php $aux = 0;$categoria = $r->categoria; ?>
                            <p class="col s12 grey-text darken-2 titulo-modal center-align margin-top-50 padding-top-20">{{$r->categoria}}</p>
                        @endif

                        @include('templates.secciones.item-menu',["data"=>["responsive_class"=>"s4 m3 l2","href"=>url('/reporte'.$r->url),"color_class"=>$color_class,"src"=>asset('img/sistema/icons/'.$r->icono),"label"=>$r->label]])
                        <?php $aux++; ?>
                        @if($aux % 4 == 0)
                            <div class="row hide-on-large-only hide-on-small-only"></div>
                        @endif
                        @if($aux % 3 == 0)
                            <div class="row hide-on-med-and-up"></div>
                        @endif
                        @if($aux % 6 == 0)
                            <div class="row hide-on-med-and-down"></div>
                        @endif
                    @endif
                @empty
                    <h3>No le han sido asignados reportes, por favor comuníquese con el administrador!!!</h3>
                @endforelse
            </div>
        </div>

        <div class="footer-items col s12"></div>
    </div>
@endif
