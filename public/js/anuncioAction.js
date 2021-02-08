$(function(){
    $("#categoria_anuncio").change(function(){
        if($(this).val() == "otras"){
            $("#otras_categorias").removeClass("hide");
        }else{
            $("#otras_categorias").addClass("hide");
        }
    })

    $(".imagen_anuncio").change(function(){
        mostrarImagen(this,$(this).data("preview"));
    });
})

function cargarTablaAnuncios() {
    var url = $("#base_url").val()+"/anuncio";

    //var p_boton_ver = '@((User as CustomPrincipal).funciones.Contains(constantes_permisos.Funcion_Boton_Ver_en_Solicitudes))';

    var checked = "";
    var i=1;
    var tabla_anuncios = $('#tabla_anuncios').dataTable({ "destroy": true });
    tabla_anuncios.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_anuncios').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_anuncios = $('#tabla_anuncios').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/anuncio/list-anuncios",
            "type": "GET"
        },
        "columns": [
            { "data": "titulo", 'className': "text-center" },
            { "data": "descripcion", 'className': "text-center" },
            { "data": "valor", 'className': "text-center" },
            { "data": "desde", 'className': "text-center" },
            { "data": "hasta", 'className': "text-center" },
            { "data": "contacto", 'className': "text-center" },
            { "data": "categoria", 'className': "text-center" },
            { "data": "estado", 'className': "text-center" },
            { "data": "imagenes", "className": "text-center" },
            { "data": "editar", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {

        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_anuncios.data().length){
                setTimeout(function () {
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
        },
        //"columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [1,5,8,9] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}

function agregar(){
    $("#descripcion").val(CKEDITOR.instances.descripcion.getData());
    var params = new FormData(document.getElementById("form-anuncio"));
    var url = $("#base_url").val()+"/anuncio/store";
    DialogCargando("Guardando ...");
    $.ajax({
        url: url,
        type: "post",
        dataType: "html",
        data: params,
        cache: false,
        contentType: false,
        processData: false
    }).done(function(data){
        //alert('Post correcto');
        data = JSON.parse(data);
        if(data.success){
            $("html, body").animate({
                scrollTop: 50
            }, 600);
            window.location.reload();
        }
        CerrarDialogCargando();
    }).error(function(jqXHR,error,estado){
        //alert('error');
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-accion-anuncio",JSON.parse(jqXHR.responseText));
        $("html, body").animate({ scrollTop: "0px" }, 600);
    })
}

function editar(){
    $("#descripcion").val(CKEDITOR.instances.descripcion.getData());
    var params = new FormData(document.getElementById("form-anuncio"));
    var url = $("#base_url").val()+"/anuncio/update";
    DialogCargando("Guardando ...");
    $.ajax({
        url: url,
        type: "post",
        dataType: "html",
        data: params,
        cache: false,
        contentType: false,
        processData: false
    }).done(function(data){
        //alert('Post correcto');
        data = JSON.parse(data);
        if(data.success){
            $("html, body").animate({
                scrollTop: 50
            }, 600);
            window.location.href = $("#base_url").val()+"/anuncio";
        }
        CerrarDialogCargando();
    }).error(function(jqXHR,error,estado){
        //alert('error');
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-accion-anuncio",JSON.parse(jqXHR.responseText));
        $("html, body").animate({ scrollTop: "0px" }, 600);
    })
}

function verImagenes(id){
    var url = $("#base_url").val()+"/anuncio/vista-imagenes";
    DialogCargando("Cargando ...");
    $.post(url,{id:id},function (data) {
        $("#contenedor-vista-imagenes").html(data);
        $("#modal-imagenes-anuncio").openModal();
        CerrarDialogCargando();
    }).error(function(jqXHR,error,state){
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-accion-anuncio",JSON.parse(jqXHR.responseText));
        $("html, body").animate({ scrollTop: "0px" }, 600);
    })
}