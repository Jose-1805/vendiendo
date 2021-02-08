var fecha_inicio='';
var fecha_fin='';
var reporte_id='';

var p_edit_rep_plano = false;
var p_delete_rep_plano = false;

function setPermisoEdit(val){
    this.p_edit_rep_plano = val;
}

function setPermisoDelete(val){
    this.p_delete_rep_plano = val;
}

$(function () {
    $("#btn-ver-reportes-planos").click(function () {
        var params = $("#form-filtros-reportes-planos").serialize();
        var url = $("#base_url").val()+"/reporte-plano/listar-reportes-planos";
        $("#progress-reportes-planos").removeClass('hide');
        $("#contenedor-reportes-planos").addClass('hide');
        $.post(url, params, function (data) {
            $("#contenedor-reportes-planos").html(data);
            $("#progress-reportes-planos").addClass('hide');
            $("#contenedor-reportes-planos").removeClass('hide');
            inicializarMaterialize();
        }).error(function (res) {
            $("#contenedor-reportes-planos").html(data);
            $("#progress-reportes-planos").addClass('hide');
            $("#contenedor-reportes-planos").removeClass('hide');
            mostrarErrores("contenedor-errores-reportes-planos",JSON.parse(res.responseText));
        });

    });
    $("#btn-action-form-reporte-plano").click(function () {

        if ($("#seccion").val() != ''){
            $("#progress-action-form-reporte-plano").removeClass('hide');
            $("#contenedor-action-form-reporte-plano").addClass('hide');

            var params = $("#form-reporte-plano").serialize();
            var url = $("#form-reporte-plano").attr('action');

            $.post(url,params,function (data) {
                if (data.success){
                    mostrarConfirmacion("contenedor-confirmacion-reporte-plano",{"dato":[data.mensaje]});
                    $("#contenedor-confirmacion-reporte-plano").fadeOut(6000);
                    $("#progress-action-form-reporte-plano").addClass('hide');
                    window.location.href = "/reporte-plano";
                }
            }).error(function (jqXHR,status,error) {
                if(jqXHR.status == 401){
                    var errores = {"1":["Usted no tiene permisos para relizar esta acción"]};
                    mostrarErrores("contenedor-errores-reporte-plano",errores);
                }else{
                    mostrarErrores("contenedor-errores-reporte-plano",JSON.parse(jqXHR.responseText));
                }

                $("#progress-action-form-reporte-plano").addClass('hide');
                $("#contenedor-action-form-reporte-plano").removeClass("hide");
            })
        }else {
            alert("Debe seleccionar una sección");
            //window.location.reload();
        }

    });
})
function verCamposSeccion(seccion) {
    var url = $("#base_url").val()+"/reporte-plano/show/"+seccion;

    //$("#datos-producto").empty();
    $.get(url, function(res){
        $("#lista-campos-seccion").html(res);
    });
}
function agregarListaCampos(){
    var lista="";
    var cantidad = 0;
    totalChecks = document.getElementById("form-reporte-plano").elements.length;
    for(k=0;k<totalChecks;k++){
        if(document.getElementById("form-reporte-plano").elements[k].type==="checkbox" && document.getElementById("form-reporte-plano").elements[k].id != 'seleccionar-todos'){
            cantidad ++;
            if(document.getElementById("form-reporte-plano").elements[k].checked===true){
                var campos = document.getElementById("form-reporte-plano").elements[k].id;
                lista += campos+'-';
            }else{
                //document.getElementById("seleccionar-todos").checked= false;
                lista+="";
            }
        }
    }
    var listado ='';
    listado = lista.slice(0,-1);
    if (listado.split("-").length != cantidad){
        document.getElementById("seleccionar-todos").checked= false;
    }else{
        document.getElementById("seleccionar-todos").checked= true;
    }
    //console.log(listado.split("-").length);
    if (lista.length > 0){
        listado = lista.slice(0,-1);
    }
    $("#campos").val(listado);
}
function seleccionarTodos(estado) {
    var lista="";
    var checked_status = estado;

    $(".lista-campos").each(function () {
        this.checked = checked_status;
        if (checked_status){
            var campo = this.id;
            console.log(campo);
            lista += campo+'-';
        }else {
            lista+="";
        }
    });
    var listado ='';
    if (lista.length > 0){
        listado = lista.slice(0,-1);
    }
    $("#campos").val(listado);


}
function verCampos(campos) {
    $("#modal-campos-reporte").openModal();

    var array_campos = campos.split('-');
    var html = "<ul class='collection'>";
    for (var i=0; i<array_campos.length; i++){
        var campo = array_campos[i].replace('_',' ');
        html += "<li class='collection-item'>"+MaysPrimera(campo.replace('_',' '))+"</li>";
    }
    html += "</ul>";
    console.log(html);
    $("#contenido-campos-reporte").html(html);

}
function MaysPrimera(string){
    return string.charAt(0).toUpperCase() + string.slice(1);
}
function eliminarReportePlano(id_reporte) {
    var url = $("#base_url").val()+"/reporte-plano/destroy/"+id_reporte;
    var params = {_token:$("#general-token").val(),id:id_reporte};
    $("#contenedor-botones-eliminar-reporte-plano").addClass('hide');
    $("#progress-eliminar-reporte-plano").removeClass('hide');
    $.post(url,params, function (data) {
        if (data.success){
            window.location.reload();
        }
        $("#contenedor-botones-eliminar-reporte-plano").removeClass('hide');
        $("#progress-eliminar-reporte-plano").addClass('hide');

    }).error(function (jqXHR,status,error) {
        if(jqXHR.status == 401){
            var errores = {"1":["Usted no tiene permisos para relizar esta acción"]};
            mostrarErrores("contenedor-errores-reporte-plano",errores);
        }else{
            mostrarErrores("contenedor-errores-reporte-plano",JSON.parse(jqXHR.responseText));
        }
        $("#contenedor-botones-eliminar-reporte-plano").removeClass('hide');
        $("#progress-eliminar-reporte-plano").addClass('hide');
    })
}
function openModalExportar(id_reporte) {
    $("#modal-exportar-reporte-plano").openModal({
        complete: function() {window.location.reload(true); }}
    );
    $("#datos-reporte-plano").append("<input type='hidden' id='id_reporte' value='"+id_reporte+"'>");
    Flatpickr.l10n.firstDayOfWeek = 1;
    var calendars = document.getElementsByClassName("flatpickr").flatpickr();
}
function fijarDatosReportePlano() {
    var fecha_inicio=$("#fecha_inicial").val();
    var fecha_fin=$("#fecha_final").val();
    var reporte_id=$("#id_reporte").val();

    var url = $("#base_url").val()+"/reporte-plano/excel/"+reporte_id+"/"+fecha_inicio+"/"+fecha_fin;

    window.location.href = url;
}

function cargaTablaReportesPlanos(){
    var i=0;
    var params = "";
    var t_rep_planos = $('#t_rep_planos').dataTable({ "destroy": true });
    t_rep_planos.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#t_rep_planos').on('error.dt', function(e, settings, techNote, message) {

        //var error=["Error por favor contactese con el administrador"],422;
        //mostrarErrores("contenedor-errores-cierre-caja",JSON.parse(error));
       console.log( 'An error has been reported by DataTables: ', message);
    })
    t_rep_planos = $('#t_rep_planos').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/reporte-plano/list-reportes-planos?"+params, 
            "type": "GET"
        },
        "columns": [
            { "data": 'nombre', "defaultContent": "",'className': "text-center" },
            { "data": 'seccion',"defaultContent": "", 'className': "text-center" },
            { "data": null, "defaultContent": "",'className': "text-center" },
            { "data": null, "defaultContent": "",'className': "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {
            $('td', row).eq(2).html("<a href='#!' onclick=\"verCampos('"+data.campos+"')\"><span style='font-weight: 300;font-size: 0.8rem;color: #fff;background-color: #26a69a;border-radius: 2px;padding: 2.6px !important;'><b>"+data.cantidad_aux+" </b></span></a>");
            if(p_edit_rep_plano){
                $('td', row).eq(3).html("<a href='"+data.url_edit+"' class='tooltipped' data-tooltip='Editar reporte plano'><i class='fa fa-pencil-square-o fa-2x' style='cursor: pointer;''></i></a>");
            }else{
                $('td', row).eq(3).css('display','none');
            }
             if(p_delete_rep_plano){
                $('td', row).eq(4).html("<a href='#modal-eliminar-reporte-plano' class='modal-trigger' onclick=\"javascript: id_select = "+data.id+"\"><i class='fa fa-trash fa-2x tooltipped' data-tooltip='Eliminar reporte plano'  style='cursor: pointer;'></i></a>");
            }else{
                $('td', row).eq(4).css('display','none');
            }
            $('td', row).eq(5).html("<a href='#!' onclick=\"openModalExportar("+data.id+")\"><i class='fa fa-file-excel-o fa-2x tooltipped' data-tooltip='Exportar reporte plano'  style='cursor: pointer;'></i></a>");
            
        },
        "fnRowCallback": function (row, data, index) {
            if(i === 0){
                setTimeout(function () { 
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize(); 
                },700);
               i=1;
           }else{
               i++;
           }
        },
        "columnDefs": columnDefs,        
        "iDisplayLength": 5,
        "bLengthChange": true,
        "aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2,3,4,5] }] ,
        // "bInfo": false, 
        // "searching": false,
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]

    });

}