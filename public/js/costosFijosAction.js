var id_select = 0;

var p_editar_cf = false;
var p_pagar_cf = false;

function setPermisoPagar(val){
    this.p_pagar_cf = val;
}

function setPermisoEditar(val){
    this.p_editar_cf = val;
}

$(function(){
    $("#anio,#mes").change(function () {
        window.location.href = window.location.protocol+"//"+window.location.host+"/costo-fijo/?anio="+$("#anio").val()+"&mes="+$("#mes").val();
    })

    $("#contenido-pagar-costo-fijo").keyup(function(event){
        if(event.keyCode == 13){
            guardarPago();
        }
    })

    $("#contenido-crear-costo-fijo").keyup(function(event){
        if(event.keyCode == 13){
            crear();
        }
    })

    $("#contenido-editar-costo-fijo").keyup(function(event){
        if(event.keyCode == 13){
            guardarEdicion();
        }
    })
})


function editar(id){
    id_select = id;
    $("#contenido-editar-costo-fijo").addClass("hide");
    $("#load-contenido-editar-costo-fijo").removeClass("hide");
    $("#modal-editar-costo-fijo").openModal();
    var url = $("#base_url").val()+"/costo-fijo/data-costo-fijo/"+id;
    $.post(url,{"_token":$("#general-token").val()},function(data){
        if(data.success){
            $("#contenido-editar-costo-fijo #nombre").val(data.costo_fijo.nombre);
            $("#contenido-editar-costo-fijo #label-nombre").addClass("active");
            $("#contenido-editar-costo-fijo").removeClass("hide");
            $("#load-contenido-editar-costo-fijo").addClass("hide");
        }else{
            alert("Error");
        }
    }).error(function (jqXHR,error,state) {
        mostrarErrores("contenedor-errores-costos-fijos",JSON.parse(jqXHR.responseText));
        $("body").scrollTop(0);
        $("#modal-editar-costo-fijo").closeModal();
    })
}

function guardarEdicion(){
    $("#contenedor-botones-editar-costo-fijo").addClass("hide");
    $("#progress-editar-costo-fijo").removeClass("hide");
    var url = $("#base_url").val()+"/costo-fijo/update";
    var params = {"_token":$("#general-token").val(),"nombre":$("#contenido-editar-costo-fijo #nombre").val(),"estado":$("#contenido-editar-costo-fijo #estado").val(),"id":id_select};

    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }

        /*$("#contenedor-botones-editar-costo-fijo").removeClass("hide");
        $("#progress-editar-costo-fijo").addClass("hide");*/
    }).error(function(jqXHR,error,state){
        if(jqXHR.status == "401") {
            mostrarErrores("contenedor-errores-costos-fijos", JSON.parse(jqXHR.responseText));
            $("body").scrollTop(0);
            $("#modal-editar-costo-fijo").closeModal();
        }else{
            mostrarErrores("contenedor-errores-modal-costos-fijos", JSON.parse(jqXHR.responseText));

        }
        $("#contenedor-botones-editar-costo-fijo").removeClass("hide");
        $("#progress-editar-costo-fijo").addClass("hide");
    })

}

function crear(){
    $("#contenedor-botones-crear-costo-fijo").addClass("hide");
    $("#progress-crear-costo-fijo").removeClass("hide");
    var url = $("#base_url").val()+"/costo-fijo/store";
    var params = {"_token":$("#general-token").val(),"nombre":$("#contenido-crear-costo-fijo #nombre").val(),"estado":$("#contenido-crear-costo-fijo #estado").val()};

    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }

        /*$("#contenedor-botones-crear-costo-fijo").removeClass("hide");
        $("#progress-crear-costo-fijo").addClass("hide");*/
    }).error(function(jqXHR,error,state){
        if(jqXHR.status == "401") {
            mostrarErrores("contenedor-errores-costos-fijos", JSON.parse(jqXHR.responseText));
            $("body").scrollTop(0);
            $("#modal-crear-costo-fijo").closeModal();
        }else{
            mostrarErrores("contenedor-errores-modal-crear-costos-fijos", JSON.parse(jqXHR.responseText));

        }
        $("#contenedor-botones-crear-costo-fijo").removeClass("hide");
        $("#progress-crear-costo-fijo").addClass("hide");
    })

}

function pagar(id){
    id_select = id;
    $("#contenido-editar-costo-fijo").addClass("hide");
    $("#load-contenido-editar-costo-fijo").removeClass("hide");
    $("#modal-pagar-costo-fijo").openModal();
    var url = $("#base_url").val()+"/costo-fijo/data-costo-fijo/"+id;
    $.post(url,{"_token":$("#general-token").val()},function(data){
        if(data.success){
            $("#titulo-modal-pagar").text("Pagar "+data.costo_fijo.nombre);
            $("#contenido-pagar-costo-fijo").removeClass("hide");
            $("#load-contenido-pagar-costo-fijo").addClass("hide");
        }else{
            alert("Error");
        }
    }).error(function (jqXHR,error,state) {
        mostrarErrores("contenedor-errores-costos-fijos",JSON.parse(jqXHR.responseText));
        $("body").scrollTop(0);
        $("#modal-pagar-costo-fijo").closeModal();
    })
}

function guardarPago(){
    $("#contenedor-botones-pagar-costo-fijo").addClass("hide");
    $("#progress-pagar-costo-fijo").removeClass("hide");
    var url = $("#base_url").val()+"/costo-fijo/pagar";
    var params = {"_token":$("#general-token").val(),"valor":$("#valor").val(),"fecha":$("#fecha").val(),"id":id_select};

    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }

        $("#contenedor-botones-pagar-costo-fijo").removeClass("hide");
        $("#progress-pagar-costo-fijo").addClass("hide");
    }).error(function(jqXHR,error,state){
        if(jqXHR.status == "401") {
            mostrarErrores("contenedor-errores-pagar-costos-fijos", JSON.parse(jqXHR.responseText));
            $("body").scrollTop(0);
            $("#modal-pagar-costo-fijo").closeModal();
        }else{
            mostrarErrores("contenedor-errores-modal-pagar-costos-fijos", JSON.parse(jqXHR.responseText));

        }
        $("#contenedor-botones-pagar-costo-fijo").removeClass("hide");
        $("#progress-pagar-costo-fijo").addClass("hide");
    })

}




function cargaTablaCostosFijos(){
    var i=0;
    var params = "_token="+$("#general-token").val()+"&anio="+$("#anio").val()+"&mes="+$("#mes").val();
    var t_costos_fijos = $('#t_costos_fijos').dataTable({ "destroy": true });
    t_costos_fijos.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#t_costos_fijos').on('error.dt', function(e, settings, techNote, message) {

        //var error=["Error por favor contactese con el administrador"],422;
        //mostrarErrores("contenedor-errores-cierre-caja",JSON.parse(error));
       console.log( 'An error has been reported by DataTables: ', message);
    })
    t_costos_fijos = $('#t_costos_fijos').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/costo-fijo/list-costos-fijos?"+params, 
            "type": "GET"
        },
        "columns": [
            { "data": 'nombre', "defaultContent": "",'className': "text-center" },
            { "data": 'estado',"defaultContent": "", 'className': "text-center" },
            { "data": null, "defaultContent": "",'className': "text-center" },
            { "data": null, "defaultContent": "",'className': "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {
            $(".dataTables_filter label input").css('width','auto').attr("placeholder", "Por nombre o estado");
            $(row).attr("id-data",data.id);
            $(row).append('<input type="hidden" id="id-categoria-'+data.id+'" name="id-categoria" value="'+data.id+'" />');
            $('td', row).eq(2).html(data.fecha_pago);
            $('td', row).eq(3).html(data.valor);
            if(p_pagar_cf){
                if(data.if_pagar)
                    $('td', row).eq(4).html("<a onclick=\"pagar("+data.id+")\"><i class='fa fa-money fa-2x "+data.style_pagar+"' style='cursor: pointer;''></i></a>");
                else                                        
                    $('td', row).eq(4).html('');
            }else{
                $('td', row).eq(4).css('display','none');
            }
            if(p_editar_cf){
                $('td', row).eq(5).html("<a href='#'' class='' onclick=\"editar("+data.id+")\"><i class='fa fa-pencil-square-o fa-2x' style='cursor: pointer;'></i></a>");
            }else{
                $('td', row).eq(5).css('display','none');
            }
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
        "aoColumnDefs": [{ 'bSortable': false, 'aTargets': [2,3,4,5] }] ,
        // "bInfo": false, 
        // "searching": false,
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]

    });

}