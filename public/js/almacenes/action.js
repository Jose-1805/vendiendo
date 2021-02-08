var action = null;
var center
var map = null;
var marker = null;
var latitud = null;
var longitud = null;

function setLatitud(lat) {
    latitud = lat;
}

function setLongitud(long) {
    longitud = long;
}

function init(){
    var mapdivMap = document.getElementById("mapa");
    //mapdivMap.style.width = '100%';
    mapdivMap.style.height = "300px";
    if(latitud != null && longitud != null)
        center = new google.maps.LatLng(latitud, longitud);

    var myOptions = {
        zoom: 5,
        center: center,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    map = new google.maps.Map(document.getElementById("mapa"), myOptions);
    geoposicionar();
}

function geoposicionar(){
    if(latitud != null && longitud != null) {
        setPosition(latitud, longitud);
    }else{
        if(navigator.geolocation){
            navigator.geolocation.getCurrentPosition(centrarMapa,errorPosicionar);
        }else{
            //Tu navegador no soporta geolocalización
        }
    }

}

function errorPosicionar(error) {
    switch(error.code)
    {
        case error.TIMEOUT:
            //Request timeout
            break;
        case error.POSITION_UNAVAILABLE:
            //Tu posición no está disponible
            break;
        case error.PERMISSION_DENIED:
            //Tu navegador ha bloqueado la solicitud de geolocalización
            break;
        case error.UNKNOWN_ERROR:
            //Error desconocido
            break;
    }
}

function centrarMapa(pos, z){
    setPosition(pos.coords.latitude,pos.coords.longitude)
}

function setPosition(lat,long){
    map.setZoom(16);
    $("#latitud").val(lat);
    $("#longitud").val(long);
    map.setCenter(new google.maps.LatLng(lat,long));

    if(marker == null) {
        marker = new google.maps.Marker({
            position: new google.maps.LatLng(lat, long),
            map: map,
            title: "Ubicación de bodega",
            draggable: true
        });
    }

    google.maps.event.addListener(marker,'drag',function() {
        $("#latitud").val(marker.position.lat());
        $("#longitud").val(marker.position.lng());
    });
}



$(document).ready(function(){
    //init();
});

function setAction(accion){
    action = accion;
}

function actionAlmacen() {
    var params = $("#form-almacen").serialize();
    if(action == "Crear")
        var url = $("#base_url").val()+"/almacen/store";
    else
        var url = $("#base_url").val()+"/almacen/update";

    DialogCargando("Guardando ...");

    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
    }).error(function(jqXHR,state,error){
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-almacenes-action",JSON.parse(jqXHR.responseText));
    })
}