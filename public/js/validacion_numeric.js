/*$(function(){
	$(".valid-numeric").prop("autocomplete","off");
	$("body").on("keydown",".valid-numeric",function(event) {
	   validNumeric($(this),event,0,999999999999999,0);
   	});

	$("body").on("focusout",".valid-numeric",function(event) {
		validDecimales($(this),0,999999999999999,0);
	});
})
*/

function validNumeric(element,event,min,max,decimales){
		if(min > max){
			max = min;
			min = max;
		}
		//alert(event.keyCode);
		/*if($.isNumeric($(element).val())){
			alert("Es numerico");
		}else{
			alert("No es numerico");
		}*/
		max = parseFloat(max);
		min = parseFloat(min);
		oldVal = parseFloat($(element).val());
		//console.log("Key -> "+event.keyCode);
		var numerico = true;
		if(event.shiftKey){
			event.preventDefault();
		}

		if(!$(element).val() == '-'){
			if($(element).val().length){
				if(!$.isNumeric($(element).val()))
					numerico = false;
			}
		}
		

		if(numerico){
			if (event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 37 || event.keyCode == 38 || event.keyCode == 39 || event.keyCode == 40 || event.keyCode == 110 || event.keyCode == 190 || event.keyCode == 189 || event.keyCode == 109){

					if(event.keyCode == 110 || event.keyCode == 190){
						//no se pueden poner decimales
						if(decimales < 1){event.preventDefault()}
						if($(element).val().split('.').length > 1){
							event.preventDefault();	
						}
					}else if(event.keyCode == 189 || event.keyCode == 109){//signo menos
						if($(element).val().length > 0 || min >= 0){
							event.preventDefault();
						}
					}
				}else {
					if (event.keyCode < 95) {
						if (event.keyCode < 48 || event.keyCode > 57) {
							event.preventDefault();
						}
					}else {
						if (event.keyCode < 96 || event.keyCode > 105) {
							event.preventDefault();
						}
					}

					if($(element).val().split('.').length > 0){
						if(($(element).val().split('.')[1]+"").length == decimales){
							event.preventDefault();
						}
					}
				}


		}else{
			event.preventDefault();
		}

		setTimeout(function(){
			validMinMax(min,max,element);
		},5);

}

function validMinMax(min,max,element){
	//console.log("otr1a");
	var valor = parseFloat($(element).val());
	//if(valor < min && min > 10)

	//Cambia el numero si no es posible ubicarlo dentro del rango especificado 
	if(valor > max || valor < min){
		var quitarCaracter = true;
		if(valor < min && min >= 0 && valor >= 0){
			quitarCaracter = false;
		}else if(valor > max && max < 0 && valor < 0){
			quitarCaracter = false;
		}

		if(quitarCaracter){
			var newVal  = (""+valor).substring(0,((""+valor).length)-1);
			//console.log("Ndews: "+newVal);
			//alert(newVal);
			$(element).val(newVal);
			setTimeout(function(){
				validMinMax(min,max,element);	
			},5);
		}
	}
}

function validDecimales(element,min,max,decimales){
	var valor = $(element).val();
	cantidadCeros = 0;
	if(valor != "" && $.isNumeric(valor)){
		if(valor < min){
			valor = ""+min;
		}else if(valor > max){
			valor = ""+max;
		}

		var datos = valor.split(".");
		if(datos.length > 1){
			if(decimales > 0){
				if(decimales > datos[1].length)
					cantidadCeros = decimales - datos[1].length;

				if(decimales < datos[1].length){
					valor = datos[0]+"."+(datos[1]+"").substring(0,decimales);
				}

			}else{
				valor = datos[0];
			}
		}else{//es un numero entero
			if(decimales > 0){
				valor += ".";
				cantidadCeros = decimales;
			}
		}
		
	}else{
		valor = min;		
		if(decimales > 0){
			valor = min+".";
			cantidadCeros = decimales;
		}
	}

	


	if(cantidadCeros > 0){
		for(var i = 0; i < cantidadCeros;i++){
			valor += "0";
		}
	}

	//$(element).val(eliminarCerosIzquierda(valor));
    $(element).val(valor);
}


function eliminarCerosIzquierda(valor){
	var indice = 0;
	var pre = "";
	if(valor.charAt(0) == "-"){
		indice = 1;
		pre = "-";
	}

	if((valor.charAt(indice) == "0" || valor.charAt(indice) == 0) && valor.charAt(indice + 1) != "."){
		valor = pre+""+valor.substring((indice + 1),valor.length);
		valor = eliminarCerosIzquierda(valor);
	}

	return valor;
}

function formato_numero(numero, decimales, separador_decimal, separador_miles){ // v2007-08-06
	numero=parseFloat(numero);
	if(isNaN(numero)){
		return "";
	}

	if(decimales!==undefined){
		// Redondeamos
		numero=numero.toFixed(decimales);
		//numero = Math.round(numero);
		//numero=numero.toFixed(decimales);

	}

	// Convertimos el punto en separador_decimal
	numero=numero.toString().replace(".", separador_decimal!==undefined ? separador_decimal : ",");

	if(separador_miles){
		// Añadimos los separadores de miles
		var miles=new RegExp("(-?[0-9]+)([0-9]{3})");
		while(miles.test(numero)) {
			numero=numero.replace(miles, "$1" + separador_miles + "$2");
		}
	}

	return numero;
}