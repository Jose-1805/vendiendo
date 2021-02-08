var permisoEditarEmpleado = false;
var permisoEstadoEmpleado = false;
var permisoEstadoSesion = false;

$(document).ready(function(){
    cargarTablaControlEmpleados();

    //crear - Edita empleado - configuracion
    $("#btn-accion-controlEmpleados").click(function(){
        $("#progres-accion-controlEmpleados").removeClass("hide");
        $("#contenedor-botones-accion-controlEmpleados").addClass("hide");
        var params = $("#form-control-empleados").serialize();
        var url = $("#base_url").val()+"/control-empleados/accion";
        $.post(url,params,function(data){
            if(data.success){
                $("#progres-accion-controlEmpleados").addClass("hide");
                $("#contenedor-botones-accion-controlEmpleados").removeClass("hide");
                window.location.reload(true);
            }
        }).error(function (jqXHR,status,error) {
            mostrarErrores("contenedor-errores-accion-control-empleados",JSON.parse(jqXHR.responseText));
            $("#progres-accion-controlEmpleados").addClass("hide");
            $("#contenedor-botones-accion-controlEmpleados").removeClass("hide");
        })
    });

    //Cierra todas las sesiones - configuracion
    $("#btn-cierra-todas-las-sesion-controlEmpleados").click(function(){
        $("#progres-cierra-session-controlEmpleados").removeClass("hide");
        $("#contenedor-botones-cierra-session-controlEmpleados").addClass("hide");
        var url = $("#base_url").val()+"/control-empleados/cierra-todas-las-sesiones";
        $.post(url,{"_token":$("#general-token").val(),'id':$("#id_empleado").val(),'estado_check':$("#estado_check").val(),'fecha_inicio_sesion':$("input#fecha_inicio_sesion").val()},function(data){
            if(data.success){
                $("#progres-cierra-session-controlEmpleados").addClass("hide");
                $("#contenedor-botones-cierra-session-controlEmpleados").removeClass("hide");
                // if($('#tabla_inicio_control_empleados').val())
                //   cargarTablaControlEmpleadosInicio();
                // else
                window.location.reload(true);
            }
        }).error(function (jqXHR,status,error) {
            mostrarErrores("contenedor-errores-abrirCerrar-controlEmpleados",JSON.parse(jqXHR.responseText));
            $("#progres-cierra-session-controlEmpleados").addClass("hide");
            $("#contenedor-botones-cierra-session-controlEmpleados").removeClass("hide");
        })
    });

    //cambia estado de empleado - configuracion
    $("#btn-cambia-estado-controlEmpleados").click(function(){
        $("#progres-estado-empleado-controlEmpleados").removeClass("hide");
        $("#contenedor-botones-estado-empleado-controlEmpleados").addClass("hide");
        var url = $("#base_url").val()+"/control-empleados/cambia-estado-empleado";
        $.post(url,{"_token":$("#general-token").val(),'id':$("#id_empleado_c").val(),'estado_empleado':$("#estado_empleado_c").val()},function(data){
            // console.log(data)
            if(data.success){
                $("#progres-estado-empleado-controlEmpleados").addClass("hide");
                $("#contenedor-botones-estado-empleado-controlEmpleados").removeClass("hide");
                window.location.reload(true);
            }
        }).error(function (jqXHR,status,error) {
            mostrarErrores("contenedor-errores-estadoEmpleado-controlEmpleados",JSON.parse(jqXHR.responseText));
            $("#progres-estado-empleado-controlEmpleados").addClass("hide");
            $("#contenedor-botones-estado-empleado-controlEmpleados").removeClass("hide");
        })
    });

    // codigo de barras - inicio
    $("#codigo_barras_empleado").keypress(function(e) {
        if(e.which == 13) {
            ocultarErrores("contenedor-errores-controlEmpleados")
            ocultarConfirmacion('contenedor-confirmacion-controlEmpleados')
            var url = $("#base_url").val()+"/control-empleados/cierra-sesion-empleado";
            var val_usuario = $('#codigo_barras_empleado').val();
            if(val_usuario != ""){
                DialogCargando("Validando ...");
                $.post(url,{"_token":$("#general-token").val(),'barcode':$("#codigo_barras_empleado").val()},function(data){
                    var msj = {mensaje:data.mensaje};
                    mostrarConfirmacion('contenedor-confirmacion-controlEmpleados',msj);
                    CerrarDialogCargando();
                    $("#codigo_barras_empleado").val("");
                    if(data.success){
                        cargarTablaControlEmpleadosInicio();
                    }
                }).error(function (jqXHR,status,error) {
                    CerrarDialogCargando();
                    mostrarErrores("contenedor-errores-controlEmpleados",JSON.parse(jqXHR.responseText));
                    $("#codigo_barras_empleado").val("");
                })
            }
        }
    });

    //Cierra sesion empelado - inicio
    $("#btn-cierra-sesion-controlEmpleados").click(function(){
        $("#progres-cierra-session-controlEmpleados").removeClass("hide");
        $("#contenedor-botones-cierra-session-controlEmpleados").addClass("hide");
        var url = $("#base_url").val()+"/control-empleados/cierra-inicia-sesion";
        $.post(url,{"_token":$("#general-token").val(),'id':$("#id_empleado").val(),'estado_check':$("#estado_check").val(),'fecha_inicio_sesion':$("input#fecha_inicio_sesion").val()},function(data){
            if(data.success){
                $("#progres-cierra-session-controlEmpleados").addClass("hide");
                $("#contenedor-botones-cierra-session-controlEmpleados").removeClass("hide");
                // if($('#tabla_inicio_control_empleados').val())
                //   cargarTablaControlEmpleadosInicio();
                // else
                window.location.reload(true);
            }
        }).error(function (jqXHR,status,error) {
            mostrarErrores("contenedor-errores-abrirCerrar-controlEmpleados",JSON.parse(jqXHR.responseText));
            $("#progres-cierra-session-controlEmpleados").addClass("hide");
            $("#contenedor-botones-cierra-session-controlEmpleados").removeClass("hide");
        })
    });

    $("#form-control-empleados").keyup(function(e){
        if(e.keyCode == 13){
            $("#btn-accion-controlEmpleados").click();
        }
    })

    //inicializarMaterialize();
});

//Listar tabla empleados - configuracion
function cargarTablaControlEmpleados() {
    var url = $('#base_url').val() + '/control-empleados/list-control-empleados';
    var i=1;
    var tabla_control_empleados = $('#tabla_control_empleados').dataTable({ "destroy": true});
    tabla_control_empleados.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_control_empleados').on('error.dt', function(e, settings, techNote, message){
        console.log('An error has been reported by DataTables: ', message);
    });
    tabla_control_empleados = $('#tabla_control_empleados').DataTable({
        "processing":true,
        "serverSide":true,
        "ajax":{
            "url":url,
            "type":"GET"
        },
        "columns":[
            {"data": "nombre", "className": "text-center"},
            {"data": "cedula", "className": "text-center"},
            {"data": null , "className": "text-center"},
            {"data": null, "className": "text-center", "defaultContent":""},
            {"data": null, "className": "text-center", "defaultContent":""},
            {"data": null, "className": "text-center", "defaultContent":""},
            {"data": null, "className": "text-center", "defaultContent":""},
            {"data": null, "className": "text-center", "defaultContent":""}
        ],
        "createdRow": function(row, data, index){
            var style_estado_e = '#26a69a';
            var checked = '';
            var disabled = '';
            var title = 'Cerrar Sesion al usuario';
            if(data.estado_empleado == 'desactivo')
                style_estado_e = 'red';
            var span_2 ="<span style='font-weight: 300;font-size: 0.8rem;color: #fff;background-color: "+style_estado_e+";border-radius: 2px;padding: 2.6px !important;'><b>"+data.estado_empleado+"</b></span>";
            var html_2 = span_2;
            if(permisoEstadoEmpleado)
                html_2 = "<a href='#!' onclick=\"getCambiaEstado(this,'"+data.id+"','"+data.estado_empleado+"')\" >"+span_2+"</a>"
            if(data.estado_sesion == 'on')
                checked = "checked";
            else{
                disabled = 'disabled';
                title = 'Disponible solo para sesiones activas';
            }
            $('td',row).eq(0).html(data.nombre);
            $('td',row).eq(2).html(html_2);
            $('td',row).eq(3).html(data.fecha_llegada);
            $('td',row).eq(4).html(data.fecha_salida);
            $('td', row).eq(5).css('min-width', '10px').css('width', '10px').css('max-width', '10px');

            //$('td',row).eq(5).html("<i class='fa fa-eye fa-2x' aria-hidden='true' style='cursor:pointer;' onclick=\"getVer('"+data.id+"')\"></i>");
            $('td',row).eq(5).html("<a onclick=\"getVer('"+data.id+"')\"><i class='fa fa-search fa-2x' style='cursor: pointer;' ></i></a>");
            if(permisoEditarEmpleado)
                if(data.estado_empleado == 'activo')
                    $('td',row).eq(6).html("<a onclick=\"getEdicionEmpleado('"+data.id+"')\"><i class='fa fa-pencil-square-o fa-2x' style='cursor: pointer;' ></i></a>");
                else
                    $('td',row).eq(6).html("");
            else
                $('td',row).eq(6).addClass('hide');
            if(permisoEstadoSesion)
                if(data.estado_empleado == 'activo')
                    $('td',row).eq(7).html("<div class='switch'>"+
                        "<label>"+
                        "<input type='checkbox' "+disabled+" id='"+data.id+"' "+checked+" onclick=\"getIniciaCierraSesion(this,'"+data.id+"','"+data.fecha_llegada+"')\">"+
                        "<span class='lever' title='"+title+"'></span>"+
                        "</label>"+
                        "</div>");
                else
                    $('td',row).eq(7).html("");
            else
                $('td',row).eq(7).addClass('hide');
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_control_empleados.data().length){
                setTimeout(function () {
                    //alert()
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
        },
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2,3,4,5,6] }]
    });
}

//Edita empleado - configuracion
function getEdicionEmpleado(id){
    DialogCargando("Cargando ...");
    var url = $("#base_url").val()+"/control-empleados/form/"+id;
    $.post(url,{"_token":$("#general-token").val()},function(data){
        $("#contenido-accion-controlEmpleados").html(data);
        $("#modal-accion-controlEmpleados").openModal();
        CerrarDialogCargando();
    }).error(function(jqXHR,ststus,error){
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-accion-control-empleados",JSON.parse(jqXHR.responseText));
    })
}

//ver empleado - configuracion
function getVer(id){
    var url = $("#base_url").val()+"/control-empleados/view/"+id;
    $.post(url,{"_token":$("#general-token").val()},function(data){
        $("#contenido-ver-controlEmpleados").html(data);
        $("#modal-ver-controlEmpleados").openModal();
    }).error(function(jqXHR,ststus,error){
        mostrarErrores("contenedor-errores-accion-control-empleados",JSON.parse(jqXHR.responseText));
    })
}

//cierrar Sesion - configuracion - inicio
function getIniciaCierraSesion(input,id,fecha_inicio_sesion){
    var radio = $(input);
    if (radio.is(':checked')) {
        radio.prop('checked', false);
        $("input#estado_check").val('0');
    } else {
        radio.prop('checked', true);
        $("input#estado_check").val('1');
    }
    $("input#id_empleado").val(id);
    $("input#fecha_inicio_sesion").val(fecha_inicio_sesion);
    $("#modal-abrirCerrar-controlEmpleados").openModal();
}

// cambia estado empleado - configuracion
function getCambiaEstado(input,id,estado){
    $("input#id_empleado_c").val(id);
    $("input#estado_empleado_c").val(estado);
    $("#modal-estadoEmpleado-controlEmpleados").openModal();
}

// Listar tabla control de empleados - inicio
function cargarTablaControlEmpleadosInicio() {
    var url = $('#base_url').val() + '/control-empleados/list-control-empleados-inicio';
    var i=1;
    var tabla_inicio_control_empleados = $('#tabla_inicio_control_empleados').dataTable({ "destroy": true});
    tabla_inicio_control_empleados.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_inicio_control_empleados').on('error.dt', function(e, settings, techNote, message){
        console.log('An error has been reported by DataTables: ', message);
    });
    tabla_inicio_control_empleados = $('#tabla_inicio_control_empleados').DataTable({
        "processing":true,
        "serverSide":true,
        "ajax":{
            "url":url,
            "type":"GET"
        },
        "columns":[
            {"data": "nombre", "className": "text-center"},
            {"data": "cedula", "className": "text-center"},
            {"data": "fecha_llegada", "className": "text-center", "defaultContent":""},
            {"data": "fecha_salida", "className": "text-center", "defaultContent":""},
            {"data": "lugar", "className": "text-center", "defaultContent":""},
            {"data": null, "className": "text-center", "defaultContent":""}
        ],
        "createdRow": function(row, data, index){
            var style_estado_e = '#26a69a';
            var checked = '';
            var disabled = '';
            var title = 'Cerrar Sesion al usuario';
            if(data.estado_sesion == 'on')
                checked = "checked";
            else{
                disabled = 'disabled';
                title = 'Disponible solo para sesiones activas';
            }
            $('td',row).eq(2).html(data.fecha_llegada);
            $('td',row).eq(3).html(data.fecha_salida);

            if(!data.lugar)
                $('td',row).eq(4).addClass('hide');

            if(permisoEstadoSesion)
                if(data.estado_empleado == 'activo')
                    $('td',row).eq(5).html("<div class='switch'>"+
                        "<label>"+
                        "<input type='checkbox' "+disabled+" id='"+data.id+"' "+checked+" onclick=\"getIniciaCierraSesion(this,'"+data.id+"','"+data.fecha_llegada+"')\">"+
                        "<span class='lever' title='"+title+"'></span>"+
                        "</label>"+
                        "</div>");
                else
                    $('td',row).eq(5).html("");
            else
                $('td',row).eq(5).addClass('hide');
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i == 1){
                setTimeout(function () {
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize();
                },700)
                i++;
            }else{
                i++;
            }
        },
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [5] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]],
        // "fnInitComplete": function(oSettings, json) {
        //     console.log(oSettings)
        //     console.log(json)
        //   alert( 'DataTables has finished its initialisation.' );
        // }

    });
}

// Permisos
function setPermisoEditarEmpleado(val){
    this.permisoEditarEmpleado = val;
}

function setPermisoEstadoEmpleado(val){
    this.permisoEstadoEmpleado = val;
}

function setPermisoEstadoSesion(val){
    this.permisoEstadoSesion = val;
}