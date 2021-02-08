@if(\Illuminate\Support\Facades\Auth::user()->perfil->nombre == "usuario" || \Illuminate\Support\Facades\Auth::user()->perfil->nombre == "administrador")
<?php
    if(!isset($clases))$clases = "";
    if(!isset($l))$l = 4;
    if(!isset($m))$m = 4;
    if(!isset($s))$s = 4;
    if(!isset($reportesPermitidos)){
        $user = \App\User::find(Auth::user()->userAdminId());
        $perfil = $user->perfil;
        $plan_id = $user->plan();
        $reportesPermitidos = Auth::user()->permitirReportes($user->id, $plan_id->id, 'activo');
    }

    if(!isset($hide))$hide = "hide";
?>

<div class="col s12 menu-hamburguesa {{$hide}}" id="menu-reportes" style="margin-top: 20px;" >
    @if(isset($reportesPermitidos))
        <?php $categoria = ""; ?>
        @forelse($reportesPermitidos as $r)
            @if($r->categoria != $categoria)
                <?php $categoria = $r->categoria; ?>
                <p class="col s12 titulo-modal center-align">{{$categoria}}</p>
            @endif
            <div class="item-menu-app col s{{$s}} m{{$m}} l{{$l}}">
                <div class="contenedor-img-item green lighten-1" data-url="{{url('/reporte'.$r->url)}}"><img class="" src="{{ asset('img/sistema/icons/'.$r->icono) }}" alt="1" /></div>
                <span class="truncate {{$clases}}">{{$r->label}}</span>
            </div>
        @empty
            <p>No le han sido asignados reportes, por favor comuníquese con el administrador!!!</p>
        @endforelse
    @endif
</div>
@endif