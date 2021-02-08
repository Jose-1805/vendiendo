$(function(){
    $(document).on('keyup','.num-real',function(event) {
        var substrings = this.value.split('.');
        var count = substrings.length - 1;
        //console.log(event.keyCode);
        esNumerico=$.inArray(event.keyCode,[8,37,38,39,40,46,190,110,48,49,50,51,52,53,54,55,56,57,96,97,98,99,100,101,102,103,104,105]);
        if( esNumerico!==-1 ) {
            //console.log(this.name);
            /*$(this).removeClass("alert alert-warning");
            $(this).addClass("alert alert-success");*/
        }
        else {
            this.value = parseFloat(this.value);
            if(isNaN(this.value))
                this.value='';

            /*$(this).addClass("alert alert-warning");
            $(this).removeClass("alert alert-success");*/
        }

        if(count>1) {
            this.value = parseFloat(this.value);

            /*$(this).addClass("alert alert-warning");
            $(this).removeClass("alert alert-success");*/
        }

        if(substrings.length > 1){
            if(event.keyCode != 37 && event.keyCode != 39 && event.keyCode != 38 && event.keyCode != 40 && event.keyCode != 8) {
                var decimal = substrings[1];
                if (substrings[1].length > 2)
                    decimal = substrings[1].substring(0, 2);
                this.value = parseInt(substrings[0]) + "." + decimal;
            }

        }

        //validNumericShow($(this));

        $(".toast-numericos #valor").text(" "+number_format(this.value,2));
        $(".numeric-show").change();

    });

    $(document).on('focusout','.num-real,.num-entero,.num-tel',function(event) {
        if (!($(this).hasClass("excepcion"))) {
            if($(this).hasClass("num-real")) {
                var valor = parseFloat(this.value).toFixed(2);
            }else if($(this).hasClass("num-entero") || $(this).hasClass("num-tel")){
                var valor = parseInt(this.value);
            }
            if(!$.isNumeric(valor))valor = 0;
            this.value = valor;

            if($(this).data("max")){
                if(parseFloat($(this).data("max")) < parseFloat($(this).val())){
                    alert("El valor ingresado es incorrecto, el màximo valor permitido es "+$(this).data("max"));
                    $(this).focus();
                }
            }
        }

        //validNumericShow($(this));
        $(".numeric-show").change();

        $(".toast-numericos #valor").text("$ 0");
        $(".toast-numericos").addClass("hide");
    });

    $(document).on('keyup','.num-entero,.num-tel',function(event) {
        esNumerico=$.inArray(event.keyCode,[8,37,39,46,48,49,50,51,52,53,54,55,56,57,96,97,98,99,100,101,102,103,104,105]);
        if( esNumerico!==-1 ) {
            /*$(this).removeClass("alert alert-warning");
            $(this).addClass("alert alert-success");*/
        }
        else {
            this.value = parseInt(this.value);
            if(isNaN(this.value))
                this.value='';

            /*$(this).addClass("alert alert-warning");
            $(this).removeClass("alert alert-success");*/
        }

        if($(this).hasClass("num-tel")) {  
            formatted =  (this.value != "") ? this.value :0;         
            if(this.value.length > 3)
                formatted = this.value.substr(0, 3) + '-' + this.value.substr(3, 3) + '-' + this.value.substr(6,4);

            $(".toast-numericos #valor").text(" "+formatted);     

        }else{       
            $(".toast-numericos #valor").text(" "+number_format(this.value,0));
        }

    });

    $(document).on("focus",".num-entero, .num-real,.num-tel",function(){
        $(".toast-numericos").eq(0).css({
            top:($(this).offset().top-50)+"px",
            left:$(this).offset().left+"px"
        });

        if($(this).hasClass("num-entero") || $(this).hasClass("num-tel")){
            var decimals = 0;
        }else{
            var decimals = 2;
        }

        var valor = $(this).val();
        if($.isNumeric(valor)){
            if(!$(this).hasClass("num-tel")) {
                valor = number_format(valor,decimals);     
            }else{
                if(this.value.length > 3)
                    valor = this.value.substr(0, 3) + '-' + this.value.substr(3, 3) + '-' + this.value.substr(6,4);
            }           
        }else{
            valor = 0;
        }

        $(".toast-numericos #valor").text(" "+valor);
        $(".toast-numericos").removeClass("hide");
    });

    $(document).on("change",".numeric-show",function(){
        validNumericShow($(this));
    })
});
function validNumericShow(elemento){
    var valor = $(elemento).val();
    if($(elemento).hasClass("numeric-show")){
        if($.isNumeric(valor)) {
            $(elemento).parent().children(".numeric-hidden").val(parseFloat(valor).toFixed(5));

            if (!$(elemento).is(":focus")) {
                $(elemento).val(parseFloat(valor).toFixed(2));
            }
        }
    }
}