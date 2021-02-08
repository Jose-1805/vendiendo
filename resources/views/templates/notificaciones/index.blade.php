<div id="full-contenedor-notificaciones" class="full-contenedor-notificaciones">
<div class="z-depth-3 white contenedor-notificaciones" style="">
    <ul id="tabs-notificaciones" class="tabs scroll-style">
        <li class="tab col s4"><a id="tab-inventario" class="active font-small" href="#inventario" title="Inventario">Inventario</a></li>
        <li class="tab col s4"><a id="tab-cuentas-cobrar" class="font-small" href="#cuentas_cobrar" title="Cuentas por cobrar">Cuentas por cobrar</a></li>
        <li class="tab col s4"><a id="tab-cuentas-pagar" class="font-small" href="#cuentas_pagar" title="Cuentas por pagar">Cuentas por pagar</a></li>
    </ul>

    <div id="contenedor-btn-cerrar-notificaciones">
        <i id="btn-cerrar-notificaciones" class="fa fa-times-circle grey-text" title="Ocultar notificaciones"></i>
    </div>
    <div class="padding-10 blue-grey lighten-5" style="height: 36px;">
        <p class="left no-margin">
            <i class="fa fa-circle-o-notch fa-spin hide cyan-text" id="load-todo-leido"></i>
            <input  type="checkbox" id="todo-leido" />
            <label id="label-todo-leido" for="todo-leido" class="font-small black-text text-lighten-2">Marcar todo como leído</label>
        </p>
        <a id="btn-recargar-notificaciones" href="#" class="right black-text"><i class="fa fa-refresh"></i></a>
    </div>
    <div class="scroll-style contenedor-lista-notificaciones">
        <p class="center-align" id="load-notificaciones">Cargando notificaciones  <i class="fa fa-spin fa-circle-o-notch"></i></p>
        @include('templates.mensajes',["id_contenedor"=>"notificaciones"])
        <div id="inventario"></div>
        <div id="cuentas_cobrar"></div>
        <div id="cuentas_pagar"></div>
        <div class="col s12 center-align">
            <a id="btn-ver-mas-notificaciones" class="btn-flat cyan-text font-small" href="#">Ver más</a>
            <p id="load-ver-mas-notificaciones" class="hide"> Cargando <i class="fa fa-circle-o-notch fa-spin"></i></p>
        </div>
    </div>
</div>
</div>