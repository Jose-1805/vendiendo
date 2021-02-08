$(function(){
    if(localStorage.getItem("state") === null){
        localStorage.setItem("state","-1");
        window.location.reload(true);
    }else{
        if(localStorage.state != "-1"){
            localStorage.setItem("state","-1");
            window.location.reload(true);
        }
    }


	$("#btn-inicio-sesion").click(function(){
        ocultarErrores("contenedor-errores-login");
        $("#progres-inicio-sesion").removeClass("hide");
        $("#contenedor-boton-inicio").addClass("hide");

        if($("#email").val() && $("#password").val()){
            $.post($("#base_url").val()+"/auth/authenticate",$("#form-inicio-sesion").serialize(),function(data){
                if(data.success){
                    window.location.reload(true);
                }else{
                    if(data.error){
                        data = {"1":[data.error]};
                        mostrarErrores("contenedor-errores-login",data);
                    }else {
                        data = {"1": ["Los datos de ingreso son incorrectos"]};
                        mostrarErrores("contenedor-errores-login", data);
                    }
                }
                $("#progres-inicio-sesion").addClass("hide");
                $("#contenedor-boton-inicio").removeClass("hide");
            }).error(function(jqXHR,error,state){
                mostrarErrores("contenedor-errores-login",JSON.parse(jqXHR.responseText));
                $("#progres-inicio-sesion").addClass("hide");
                $("#contenedor-boton-inicio").removeClass("hide");
            })
        }else{
            //var data = [{mensaje:"Todos los campos son obligatorios"}];
            var data = {"1":["Los datos de ingreso son requeridos"]};
            mostrarErrores("contenedor-errores-login",data);
            $("#progres-inicio-sesion").addClass("hide");
            $("#contenedor-boton-inicio").removeClass("hide");
        }
    })

    $("#form-inicio-sesion").keyup(function(e){
        if(e.keyCode == 13){
            $("#btn-inicio-sesion").click();
        }
    })

    $("#btn-reestablecer-password").click(function(){
        ocultarErrores("contenedor-errores-reestablecer-password");
        $("#progres-reestablecer-password").removeClass("hide");
        $("#contenedor-botones-reestablecer-password").addClass("hide");

        if($("#email").val()){
            $.post($("#base_url").val()+"/password/email",$("#form-reestablecer-password").serialize(),function(data){
                data = {"1":["Se ha enviado un email con la información para reestablecer la contraseña"]};
                mostrarConfirmacion("contenedor-confirmacion-reestablecer-password",data);
                $("#progres-reestablecer-password").addClass("hide");
                $("#contenedor-botones-reestablecer-password").removeClass("hide");
            }).error(function(jqXHR,error,state){
                mostrarErrores("contenedor-errores-reestablecer-password",JSON.parse(jqXHR.responseText));
                $("#progres-reestablecer-password").addClass("hide");
                $("#contenedor-botones-reestablecer-password").removeClass("hide");
            })
        }else{
            //var data = [{mensaje:"Todos los campos son obligatorios"}];
            var data = {"1":["El campo email es requerido"]};
            mostrarErrores("contenedor-errores-reestablecer-password",data);
            $("#progres-reestablecer-password").addClass("hide");
            $("#contenedor-botones-reestablecer-password").removeClass("hide");
        }
    })

    $("#form-reestablecer-password").keyup(function(e){
        if(e.keyCode == 13){
            $("#btn-reestablecer-password").click();
        }
    })
})