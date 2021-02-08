var horaI = '00:00';
var horaF =  '00:00';
var inicio = '';
var fin = '';
var f = new Date();
var fecha_actual = f.getFullYear() + "-" + (f.getMonth() +1) + "-" + f.getDate();
var permiso = false;
var idioma = 'es';  //Se coloca el idioma del calendario, en este caso esta en español

$(document).ready(function() {
    Flatpickr.l10n.firstDayOfWeek = 1;
    if($('#horaI').length && $('#horaF').length) {
        document.getElementById("horaI").flatpickr({
            enableTime: true,
            noCalendar: true,
            timeFormat: "H:i",
            time_24hr: true
        });
        document.getElementById("horaF").flatpickr({
            enableTime: true,
            noCalendar: true,
            timeFormat: "H:i",
            time_24hr: true
        });
        Materialize.updateTextFields();
    }

    inicializarCalendario();

    //$('#calendar:not(".fc-event")').on('contextmenu', function(e){ e.preventDefault() }) //bloquea el clñick derecho del mouse
    $(":button.fc-basicDay-button").html('Lista simple');   //Para cambiar el nombre boton (basicDay)

});

function setPermiso(data){
    permiso = data;
}

function inicializarCalendario(){
    //Aqui se empiezan la configuracion del calendario
    $('#calendar').fullCalendar({
        googleCalendarApiKey: 'AIzaSyAH2_N1-vkr22RD4st8s_e83kJiW2M04Mg', //este valor se genera siguiendo los pasos descritos en la documentacion https://fullcalendar.io/docs/google_calendar/
        //Cabeceras que va a tener el calendario
        header: {
            left: 'prev,next today myCustomButton',
            center: 'title',
            right: 'month,basicDay,listWeek,agendaDay'
        },
        lang:idioma,
        //theme: true,      //Para cambiar el tema del calendario
        lazyFetching: false,
        allDay: true,
        editable: true,
        eventLimit: true,
        navLinks: true,
        selectable: true,
        //selectHelper: false,
        //allDaySlot : false,
        timeFormat: 'H(:mm)',
        select: function(start, end, jsEvent, view) {
            /*Este evento se ejecuta cuando das click sobre algun dia o seleccionas varios dias. Cuando se ejecuta este evento
             * este entrega el dia inicial (start) y final (end) */
            if(permiso) {
                inicio = moment(start).format();
                fin = moment(end).format();
                var continuar = 0;
                if (restaFechas(fecha_actual, inicio) >= 0 || isNaN(restaFechas(fecha_actual, inicio))) { //Valida que no se generen eventos en dias pasados
                    if (view.name == 'month') { //view.name entrega el nombre la interfaz donde estes (month,basicDay,listWeek,agendaDay)
                        if (restaFechas(inicio, fin) == 1) {
                            //Se cambia de una interfaz a otra
                            $('#calendar').fullCalendar('changeView', 'agendaDay', 'gotoDate', inicio);
                            $('#calendar').fullCalendar('gotoDate', inicio);
                            continuar = 0;
                        } else {
                            inicio = moment(start).format();
                            fin = moment(end).format();
                            var fin_aux = AddDaysToDate(moment(end).format(), -0, '-', 'add');

                            //si la fecha de inicio y fin son diferentes se habilita el cheack para crear eventos separados
                            if (inicio != fin_aux) {
                                $('#contenedor_eventos_diarios').removeClass('hide');
                            } else {
                                $('#contenedor_eventos_diarios').addClass('hide');
                            }


                            //fin =  AddDaysToDate(moment(end).format(),0,'-','add');
                            continuar = 2;
                        }
                    } else if (view.name == 'agendaDay') {
                        inicio = moment(start).format("YYYY-MM-DD");
                        //inicio = AddDaysToDate(moment(start).format(),1,'-','add');
                        fin = AddDaysToDate(moment(end).format(), -0, '-', 'add');
                        //fin = moment(end).format("YYYY-MM-DD");
                        horaI = moment(start).format("HH:mm");
                        horaF = moment(end).format("HH:mm");

                        //si la fecha de inicio y fin son diferentes se habilita el cheack para crear eventos separados
                        if (inicio != fin) {
                            $('#contenedor_eventos_diarios').removeClass('hide');
                        } else {
                            $('#contenedor_eventos_diarios').addClass('hide');
                        }
                        continuar = 1;
                    }
                    if (continuar == 1) {
                        if (inicio.toString() === fin.toString()) {
                            fin = AddDaysToDate(moment(fin).format(), 1, '-', 'add');
                        }
                        $("#modal-event").openModal({dismissible: false});
                        $("#label_inicio").addClass('active');
                        $("#label_fin").addClass('active');
                        $("#f_inicio").val(inicio + " " + horaI);
                        $("#f_fin").val(fin + " " + horaF);
                        $("#hora_label").html("<label style='color: #0a0a0a'>Inicio:</label> " + inicio + " " + horaI + " <label style='color: #0a0a0a'>Fin: </label> " + AddDaysToDate(fin, 0, '-', 'remove') + " " + horaF);
                    } else if (continuar == 2) {
                        $("#modal-event-hour").openModal();
                    }
                } else {
                    $("#modal-alert").openModal();
                    $("#modal-alert").css({'background': 'red'});
                    $("#title-alert").html("Error!");
                    $("#description-alert").html("No puede asignas eventos a dias pasados");
                    //$(this).css('background', 'red');
                    return false;
                }
            }

        },
        eventClick: function(calEvent, jsEvent, view) {
            //Este evento se ejecuta cuando das click sobre un evento
            switch (calEvent.evento_name){
                case 'costo_fijo':
                    if (calEvent.estado_pago == 'Pendiente') {
                        //if (restaFechas(fecha_actual,moment(calEvent.start).format('YYYY-MM-DD')) <= 0){
                        pagar(calEvent.id);
                        $("#fecha").val(moment(calEvent.start).format('YYYY-MM-DD'));
                        /*}else{
                         $("#modal-event-description").openModal();
                         $("#modal-event-description").css({'background':'red'});
                         $("#title-event").html('Error!');
                         $("#description-event").html("Aún no es tiempo de pagar el servicio de " + calEvent.title);
                         }*/
                    }
                    break;
                default:
                    $("#modal-event-description").openModal();
                    $("#modal-event-description").css({'background':calEvent.color});
                    $("#title-event").html(calEvent.title + " Hora: " + calEvent.hourI + " hasta " + calEvent.hourF);
                    $("#description-event").html(calEvent.description);

                    //se llena el formulario del modal de edición
                    $('#evento-form-edit #titulo').val(calEvent.title);
                    $('#evento-form-edit #descripcion').val(calEvent.description);
                    $('#evento-form-edit #id').val(calEvent.id);

                    $('.color-chek-edit').each(function(key, element){
                        if($(element).val() == calEvent.color){
                            seleccionarColorEdit($(element).attr('id'));
                        }
                    });
                    break;
            }
            return false;
        },
        eventSources: [
            /*Con esta opcion se pueden recibir eventos de varias fuentes, en estre caso se esta recibiendo eventos tanto de
             * vendiendo (url) como se google Calendar (googleCalendarId).
             * Hay que tener en cuenta que las fuentes deben esntregar los eventos en formato Json.
             * Para ejemplo analizar la funcion "listCostosFijos" del controlador EventsController*/
            {
                url:  $("#base_url").val()+'/api-costos-fijos',
            },
            {
                url:  $("#base_url").val()+'/api',
            },
            {
                googleCalendarId: 'fp3fblf5pj1683bp3e76vjot48@group.calendar.google.com', //este valor se genera siguiendo los pasos descritos en la documentacion https://fullcalendar.io/docs/google_calendar/
                className: 'nice-event'
            }
        ],
        eventDrop: function(event, delta, revertFunc) {
            //revertFunc();
            if(permiso) {
                switch (event.evento_name) {
                    case 'costo_fijo':
                        mensaje = '¿Está seguro de pagar este costo fijo?';
                        break;
                    default:
                        mensaje = '¿Está seguro de mover este evento?';
                        break;
                }
                //Este evento se ejecuta cuando mueves un evento de un dia a otro
                if (!confirm(mensaje)) {
                    revertFunc();   //regrese al estado inicial un evento si se cancela la operacion
                } else {
                    switch (event.evento_name) {
                        case 'costo_fijo':
                            if (event.estado_pago == 'Pendiente') {
                                pagar(event.id);
                                $("#fecha").val(moment(event.start).format('YYYY-MM-DD'));
                            }
                            break;
                        default:
                            /*days: 7
                             hours: 0
                             milliseconds: 0
                             minutes: 0
                             months: 0
                             seconds: 0
                             years: 0
                             __proto__: Object*/

                            if (delta._data.days != 0) {
                                var params = {
                                    id: event.id,
                                    dato: delta._data.days,
                                    tipo_dato: 'dias',
                                    _token: $('#general-token').val()
                                };
                            } else if (delta._data.hours != 0) {
                                var params = {
                                    id: event.id,
                                    dato: delta._data.hours,
                                    tipo_dato: 'horas',
                                    _token: $('#general-token').val()
                                };
                            } else if (delta._data.minutes != 0) {
                                var params = {
                                    id: event.id,
                                    dato: delta._data.minutes,
                                    tipo_dato: 'minutos',
                                    _token: $('#general-token').val()
                                };
                            }

                            var url = $('#base_url').val() + '/evento/mover';
                            $.post(url, params, function (data) {

                            }).error(function (jqXHR, error, state) {
                                alert('Ocurrio un error al mover el evento, intente nuevamente');
                                revertFunc();
                            })

                            break;
                    }
                }
            }else {
                revertFunc();
            }
        },
        eventResize: function(event, delta, revertFunc) {
            if(permiso) {
                //Este evento se ejecuta cuando cambias el tamaño de un evento (mas pequeño o mas grande)
                switch (event.evento_name) {
                    case 'costo_fijo':
                        mensaje = '¿Está seguro de cambiat este costo fijo?';
                        break;
                    default:
                        mensaje = '¿Está seguro de mover la hora de finalización de este evento?';
                        break;
                }
                //Este evento se ejecuta cuando mueves un evento de un dia a otro
                if (!confirm(mensaje)) {
                    revertFunc();   //regrese al estado inicial un evento si se cancela la operacion
                } else {
                    switch (event.evento_name) {
                        case 'costo_fijo':
                            revertFunc();
                            break;
                        default:
                            /*days: 0
                             hours: 2
                             milliseconds: 0
                             minutes: 0
                             months: 0
                             seconds: 0
                             years: 0
                             __proto__: Object*/

                            if (delta._data.hours != 0) {
                                var params = {
                                    id: event.id,
                                    dato: delta._data.hours,
                                    tipo_dato: 'horas',
                                    solo_fin: true,
                                    _token: $('#general-token').val()
                                };
                            } else if (delta._data.minutes != 0) {
                                var params = {
                                    id: event.id,
                                    dato: delta._data.minutes,
                                    tipo_dato: 'minutos',
                                    solo_fin: true,
                                    _token: $('#general-token').val()
                                };
                            }
                            var url = $('#base_url').val() + '/evento/mover';
                            $.post(url, params, function (data) {

                            }).error(function (jqXHR, error, state) {
                                alert('Ocurrio un error al mover la hora el evento, intente nuevamente');
                                revertFunc();
                            })

                            break;
                    }
                }
            }else {
                revertFunc();
            }
        },
        views: {
            month: {
                eventLimit: 3   /*numero de eventos que se muestran en cada dia, si el numero es mayor aparece un enlace "+mas".
                 cuando se presiona sobre este ultimo se despiega un modal con la lista de todos los eventos de ese dia*/
            },
            agendaDay: {
                eventLimit: 6
            }

        },
        eventRender: function(event, element) {
            if(permiso) {
                //Se ejecuta mientras se está procesando un evento.
                if (event.evento_name != "costo_fijo") {
                    //Se coloca a cada evento el icono de cerrar
                    element.append("<div><a class='boxclose' id='boxclose" + event.id + "'></a></div>");
                }
                element.find(".boxclose").click(function (e) {
                    var url = $("#base_url").val() + "/evento/delete/" + event.id;
                    //var url = event.url + "/" + event.id;
                    $("#modal-event-description").closeModal();
                    $("#modal-alert").addClass('hide');
                    e.preventDefault();
                    $.ajax({
                        url: url,
                        type: "GET",
                        dataType: "html",
                        data: '',
                        cache: false,
                        contentType: false,
                        processData: false
                    }).done(function (data) {
                        if (data == 'ok') {
                            $('#calendar').fullCalendar('removeEvents', event._id);
                            $("#modal-alert").removeClass('hide');
                            $("#modal-alert").openModal();
                            $("#modal-alert").css({'background': 'green'});
                            $("#title-alert").html("Mensaje exitoso!");
                            $("#description-alert").html("El evento se elimino de manera correcta");

                            setTimeout(function () {
                                $("#modal-alert").closeModal();
                                $("#modal-event-description").closeModal();
                                //window.location.reload();
                            }, 3000);
                        }
                    }).error(function (jqXHR) {
                        $("#modal-alert").removeClass('hide');
                        $("#modal-alert").openModal({
                            complete: function () {
                                window.location.reload()
                            }
                        });
                        $("#modal-alert").css({'background': 'red'});
                        $("#title-alert").html("Error!");
                        $("#description-alert").html("Ocurrio un error");
                        //$(this).css('background', 'red');
                    });
                });
            }
        },
        eventMouseover: function(calEvent, jsEvent) {
            //Se ejecuta cuando se pasa el mouse sobre el evento, en este caso se esta mostrando un tooltip
            var posMouse = jsEvent.pageX - jsEvent.pageXOffset;
            var textoTooltip = calEvent.description;
            jQuery(this).append('<div class="tooltip">' + textoTooltip + '</div>');
            jQuery("a > div.tooltip").css("left", posMouse - 103 + "px");
            jQuery("a > div.tooltip").css("background", calEvent.color);
            if (calEvent.color == "red")
                $("div.tooltip").toggleClass('special_a').toggleClass('special_b');
            jQuery("a > div.tooltip").fadeIn(1000);
        },
        eventMouseout: function (calEvent, jsEvent) {
            //Se ejecuta cuando se quita el mouse del evento, en este caso oculta el tooltip
            jQuery("a > div.tooltip").fadeOut(50).delay(50).queue(function () {
                jQuery(this).remove();
                jQuery(this).dequeue();
            });
        }
    });
}

function restaFechas(f1,f2){
    var aFecha1 = f1.split('-');
    var aFecha2 = f2.split('-');
    var fFecha1 = Date.UTC(aFecha1[1],aFecha1[1]-1,aFecha1[2]);
    var fFecha2 = Date.UTC(aFecha2[1],aFecha2[1]-1,aFecha2[2]);
    var dif = fFecha2 - fFecha1;
    var dias = Math.floor(dif / (1000 * 60 * 60 * 24));
    return dias;
}
function AddDaysToDate(sDate, iAddDays, sSeperator,operation) {
    //Purpose: Add the specified number of dates to a given date.
    var date = new Date(sDate);
    if (operation == "add"){
        date.setDate(date.getDate() + parseInt(iAddDays));
    }else if (operation == 'remove'){
        date.setDate(date.getDate() - parseInt(iAddDays));
    }
    return date.getFullYear() + sSeperator + LPad(date.getMonth() + 1, 2) + sSeperator + LPad(date.getDate(), 2);
}
function LPad(sValue, iPadBy) {
    sValue = sValue.toString();
    return sValue.length < iPadBy ? LPad("0" + sValue, iPadBy) : sValue;
}
function asignarHora() {
    horaI = $("#horaI").val();
    horaF = $("#horaF").val();

    $("#modal-event").openModal();
    $("#label_inicio").addClass('active');
    $("#label_fin").addClass('active');
    $("#f_inicio").val(inicio+" "+horaI);
    $("#f_fin").val(fin +" "+horaF);
    $("#hora_label").html("<label style='color: #0a0a0a'>Inicio:</label> " + inicio +" "+ horaI +" <label style='color: #0a0a0a'>Fin: </label> "+ AddDaysToDate(moment(fin).format(),1,'-','remove') +" "+horaF);
}
function seleccionarColor(id) {
    $('.color-chek').each(function(key, element){
        $(element).attr('checked', false);
    });
    $('input[id = '+id+']').attr('checked', true);
}
function seleccionarColorEdit(id) {
    $('.color-chek-edit').each(function(key, element){
        if($(element).attr('id') == id){
            $(element).attr('checked', true);
        }else{
            $(element).attr('checked', false);
        }
    });
}
function crearEvento() {

    $("#evento-form").addClass('hide');
    $("#progress-action-form-evento").removeClass('hide');
    $("#btn-action-form-evento").addClass('hide');

    var url = $("#evento-form").attr('action');
    var parametros = new FormData(document.getElementById("evento-form"));
    var color = "";

    $('.color-chek:checked').each(
        function() {
            color = $(this).val();
        }
    );
    parametros.color = color;
    $.ajax({
        url: url,
        type: "POST",
        dataType: "html",
        data: parametros,
        cache: false,
        contentType: false,
        processData: false
    }).done(function (data) {
        if (data == 'ok'){
            $("#modal-event").closeModal();
            $("#evento-form").removeClass("hide");
            $("#progress-action-form-evento").addClass('hide');
            $("#btn-action-form-evento").removeClass('hide');

            $("#modal-alert").openModal({
                complete: function() { window.location.reload(); }
            });
            $("#modal-alert").css({'background':'green'});
            $("#title-alert").html("Mensaje exitoso!");
            $("#description-alert").html("El evento se creo de manera correcta");

            setTimeout(function () {
                $("#modal-alert").closeModal();
                window.location.reload();
            }, 3000);
        }
        
    }).error(function (jqXHR) {
        var html = "<ul>";
        $.each(JSON.parse(jqXHR.responseText), function (key, value) {
            html += "<li>" + value + "</li>";
        });
        html += "</u>";
        $("#evento-form").removeClass("hide");
        $("#progress-action-form-evento").addClass("hide");
        $("#btn-action-form-evento").removeClass("hide");

        $("#mensaje-confirmacion-evento").show(1000, function () {
            $("#mensaje-confirmacion-evento").addClass('contenedor-errores');
            $("#mensaje-confirmacion-evento").html(html);
            //html="";
            //$("#mensaje-confirmacion-evento").html("D' oh!. Acaba de ocurir un problema");

        });
        setTimeout(function () {
            $("#mensaje-confirmacion-evento").fadeOut(2000);
            $("#mensaje-confirmacion-evento").html("");
            inicializarMaterialize();
        }, 3000);

    });
}

function editarEvento() {

    $("#evento-form-edit").addClass('hide');
    $("#progress-action-form-evento-edit").removeClass('hide');
    $("#btn-action-form-evento-edit").addClass('hide');

    var url = $("#evento-form-edit").attr('action');
    var parametros = new FormData(document.getElementById("evento-form-edit"));
    var color = "";

    $('.color-chek-edit:checked').each(
        function() {
            color = $(this).val();
        }
    );
    parametros.color = color;
    $.ajax({
        url: url,
        type: "POST",
        dataType: "html",
        data: parametros,
        cache: false,
        contentType: false,
        processData: false
    }).done(function (data) {
        if (data == 'ok'){
            $("#modal-event-edit").closeModal();
            $("#evento-form-edit").removeClass("hide");
            $("#progress-action-form-evento-edit").addClass('hide');
            $("#btn-action-form-evento-edit").removeClass('hide');

            $("#modal-alert").openModal({
                complete: function() { window.location.reload(); }
            });
            $("#modal-alert").css({'background':'green'});
            $("#title-alert").html("Mensaje exitoso!");
            $("#description-alert").html("El evento se edito de manera correcta");

            setTimeout(function () {
                $("#modal-alert").closeModal();
                window.location.reload();
            }, 3000);
        }

    }).error(function (jqXHR) {
        var html = "<ul>";
        $.each(JSON.parse(jqXHR.responseText), function (key, value) {
            html += "<li>" + value + "</li>";
        });
        html += "</u>";
        $("#evento-form-edit").removeClass("hide");
        $("#progress-action-form-evento-edit").addClass("hide");
        $("#btn-action-form-evento-edit").removeClass("hide");

        $("#mensaje-confirmacion-evento").show(1000, function () {
            $("#mensaje-confirmacion-evento-edit").addClass('contenedor-errores');
            $("#mensaje-confirmacion-evento-edit").html(html);
            //html="";
            //$("#mensaje-confirmacion-evento").html("D' oh!. Acaba de ocurir un problema");

        });
        setTimeout(function () {
            $("#mensaje-confirmacion-evento-edit").fadeOut(2000);
            $("#mensaje-confirmacion-evento-edit").html("");
            inicializarMaterialize();
        }, 3000);

    });
}

function pagar(id){
    id_select = id;
    $("#contenido-editar-costo-fijo").addClass("hide");
    $("#load-contenido-editar-costo-fijo").removeClass("hide");
    $("#modal-pagar-costo-fijo").openModal({
        complete: function() {window.location.reload()}
    });
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