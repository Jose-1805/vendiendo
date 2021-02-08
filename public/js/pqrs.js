$(function(){    
    cargaTablaPqr();
})

function action(){
    var email = $('#email').val()
    var x = validateEmail(email)
    console.log(x);
    console.log(email);
    if(!(validateEmail(email))){
        Materialize.toast('Formato de email no válido ', 5000, 'red', 'rounded');
        return false;
    }

    var url = $("#form-pqrs").attr("action");
    var params = $("#form-pqrs").serialize();
    $("#contenedor-botones-action-pqrs").addClass("hide");
    $("#progress-action-pqrs").removeClass("hide");
    $.post(url,params,function(data){
        if(data.success){
            $("body").scrollTop(0);
            window.location.reload();
        }else {
            $("#contenedor-botones-action-pqrs").removeClass("hide");
            $("#progress-action-pqrs").addClass("hide");
        }
    }).error(function(jqXHR,state,error){
        $("body").scrollTop(0);
        mostrarErrores("contenedor-errores-action-pqrs",JSON.parse(jqXHR.responseText));
        $("#contenedor-botones-action-pqrs").removeClass("hide");
        $("#progress-action-pqrs").addClass("hide");
    });
}

function showDetalle(id){
    $("#info-pqrs").html("");
    $("#load-info-pqrs").removeClass("hide");
    $("#info-pqrs").addClass("hide");

    var url = $("#base_url").val()+"/pqrs/detalle";
    var params = {id:id,_token:$("general-token").val()};

    $("#modal-detalle-pqrs").openModal();
    $.post(url,params,function(data){
        $("#info-pqrs").html(data);
        $("#load-info-pqrs").addClass("hide");
        $("#info-pqrs").removeClass("hide");
    }).error(function(jqXHR,state,error){
        $("body").scrollTop(0);
        mostrarErrores("contenedor-errores-detalle-pqrs",JSON.parse(jqXHR.responseText));
        $("#load-info-pqrs").addClass("hide");
        $("#info-pqrs").removeClass("hide");
    });
}

function validateEmail($email) {
    var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
    return emailReg.test( $email );
}

function cargaTablaPqr(){

        var i=0;
        var params ="";
        var tabla_pqr = $('#tabla_pqr').dataTable({ "destroy": true });
        tabla_pqr.fnDestroy();
        $.fn.dataTable.ext.errMode = 'none';
        $('#tabla_pqr').on('error.dt', function(e, settings, techNote, message) {
           console.log( 'An error has been reported by DataTables: ', message);
        })
        tabla_pqr = $('#tabla_pqr').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": $("#base_url").val()+"/pqrs/list-pqr?"+params, 
                "type": "GET"
            },
            "columns": [
                { "data": 'tipo' , "defaultContent": "", 'className': "lef"},
                { "data": 'queja' , "defaultContent": "", 'className': "text-center"},
                { "data": null ,"defaultContent": "", 'className': "text-center"},
                { "data": 'nombre' , "defaultContent": "", 'className': "text-center"},
                { "data": 'created_at' , "defaultContent": "", 'className': "text-center"},
                { "data": null , "defaultContent": "", 'className': "text-center"}
            ],
            "createdRow": function (row, data, index) {
                $('td',row).eq(1).addClass("truncate").css("max-width", "300px");
                $('td', row).eq(2).html("("+data.tipo_identificacion+") "+data.identificacion);
                $('td',row).eq(4).css("min-width", "150px");
                $('td', row).eq(5).html("<a style='cursor: pointer;' onclick=\"showDetalle("+data.id+")\"><i class='fa fa-angle-right fa-2x'></i></a>");
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
            "aoColumnDefs": [{ 'bSortable': false, 'aTargets':  [0,1,2,3,4,5] }] ,
            "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
        });
    }