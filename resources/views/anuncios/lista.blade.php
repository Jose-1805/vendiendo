@include("templates.mensajes",["id_contenedor"=>"lista-productos"])

<table class="bordered highlight centered" id="tabla_anuncios" style="width: 100%;">
    <thead>
    <tr>
        <th>Título</th>
        <th>Descripción</th>
        <th>Valor</th>
        <th>Desde</th>
        <th>Hasta</th>
        <th>Contacto</th>
        <th>Categoría</th>
        <th>Estado</th>
        <th>Imágenes</th>
        <th >Editar</th>
    </tr>
    </thead>
</table>

<div id="modal-imagenes-anuncio" class="modal modal-fixed-footer">
    <div class="modal-content">
        <p class="titulo-modal">Imágenes</p>
        <div id="contenedor-vista-imagenes">

        </div>
    </div>

    <div class="modal-footer">
            <a href="#!" class="modal-close cyan-text btn-flat">Cerrar</a>
    </div>
</div>
