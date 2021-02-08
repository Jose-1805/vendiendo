var precioCosto = 0;
var utilid = 0;
var ivaa = 0;
var i = 1;
$(document).ready(function(){
    //alert("cargo"+ options_proveedores);

    $("#imagen").change(function(){
        mostrarImagen(this,"preview");
        $("#imagen_producto").remove();
    });
});

function viewMateriasPrimas(id,accion) {
    inicializarMaterialize();
    var url = $("#base_url").val()+"/productos/contenido";
    var count_proveedor = $("#proveedores-count").val();
    var count_materia_prima = $("#materias-primas-count").val();
    if(id =="Terminado"){
        $("#contenedor-stock").addClass("hide");
        $("#precios").addClass("hide");
        $.ajax({
            url: url+"/Terminado/"+accion,
            success: function(data) {
                $('#form-contenido').empty();
                $('#form-contenido').html(data);
                $("#costo_materias_primas_hidden").html("Precio costo (Costo materias primas: $"+number_format(0,2)+")");
                if(count_proveedor == 0)
                    $("#mensaje-advertencia").html("<p style='text-align: center; background-color: #80d8ff; color: #0D47A1'><i class='fa fa-exclamation-triangle' aria-hidden='true'></i> Para la creación de un producto terminado se requiere que haya registrado al menos un proveedor</p>");
                inicializarMaterialize();
            }
        });

    }else if(id=="Compuesto" || id=="Preparado" ){
        $("#precios").removeClass("hide");
        $("#contenedor-stock").removeClass("hide");
        $.ajax({
            url: url+"/Compuesto/"+accion,
            success: function(data) {
                $('#form-contenido').empty();
                $('#form-contenido').html(data);
                $("#costo_materias_primas_hidden").html("Precio costo (Costo materias primas: $"+number_format(0,2)+")");
                if(count_materia_prima == 0)
                    $("#mensaje-advertencia").html("<p style='text-align: center; background-color: #80d8ff; color: #0D47A1'><i class='fa fa-exclamation-triangle' aria-hidden='true'></i> Para la creación de un producto compuesto o preparado se requiere que haya registrado al menos una materia prima</p>");
                inicializarMaterialize();
            }
        });

    }
}

