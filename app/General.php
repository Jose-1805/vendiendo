<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class General extends Model {

    public  static function dias_transcurridos($fecha_i,$fecha_f)
    {
        $dias	= (strtotime($fecha_i)-strtotime($fecha_f))/86400;
        $dias 	= abs($dias); $dias = floor($dias);
        return $dias;
    }
    public static function sumarDias($dias){
        $hora_actual = date('Y-m-d H:i:s');
        $nuevafecha = strtotime ( '+'.$dias.' day' , strtotime ( $hora_actual ) ) ;
        $nuevafecha = date ( 'Y-m-d H:i:s' , $nuevafecha );

        return $nuevafecha;
    }
    public static function calcula_tiempo($start_time, $end_time) {
        $total_seconds = strtotime($end_time) - strtotime($start_time);
        $horas              = floor ( $total_seconds / 3600 );
        $minutes            = ( ( $total_seconds / 60 ) % 60 );
        $seconds            = ( $total_seconds % 60 );

        return $horas;
    }

    public static function sumarDiasFecha($fecha, $dias,$format='DateHour'){
        $fecha = date($fecha);
        $nuevafecha = strtotime ( '+'.$dias.' day' , strtotime ( $fecha ) ) ;
        if ($format == "DateHour")
            $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
        else if ($format == "Date")
            $nuevafecha = date ( 'Y-m-d' , $nuevafecha );
        return $nuevafecha;
    }
    public static function sumarMesFecha($fecha, $meses,$format='DateHour'){
        $fecha = date($fecha);
        $nuevafecha = strtotime ( '+'.$meses.' month' , strtotime ( $fecha ) ) ;
        if ($format == "DateHour")
            $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
        else if ($format == "Date")
            $nuevafecha = date ( 'Y-m-d' , $nuevafecha );
        return $nuevafecha;
    }
    public static function getHourOrDate($fecha, $opcion=''){
        $fecha = date($fecha);
        $dias=0;
        $nuevafecha = strtotime ( '+'.$dias.' day' , strtotime ( $fecha ) ) ;

        if ($opcion == "Date"){
            $nuevafecha = date ( 'Y-m-d' , $nuevafecha );
        }else if ($opcion = "Hour"){
            $nuevafecha = date ( 'H:i' , $nuevafecha );
        }else{
            $nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
        }
        return $nuevafecha;
    }

    public static function calcula_minutos($start_time, $end_time) {
        $total_seconds = strtotime($end_time) - strtotime($start_time);
        $minutes       = floor( ( $total_seconds / 60 ) % 60 );
        return $minutes;
    }
    public static function fechaVencimientoDemo($date_time){
        $dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        $fecha_actual = $dias[date('w',strtotime($date_time))]." ".date('d',strtotime($date_time))." de ".$meses[date('n',strtotime($date_time))-1]. " del ".date('Y',strtotime($date_time))." a las ".date('H:i',strtotime($date_time)) ;

        return $fecha_actual;
    }
    public static function fechaActualString(){
        $dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        $fecha_actual = $dias[date('w')]." ".date('d')." de ".$meses[date('n')-1]. " del ".date('Y') ;

        return $fecha_actual;
    }
    public static function limpiarString($string)
    {
        $string = trim($string);

        $string = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $string
        );

        $string = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $string
        );

        $string = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $string
        );

        $string = str_replace(
            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $string
        );

        $string = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $string
        );

        $string = str_replace(
            array('ñ', 'Ñ', 'ç', 'Ç'),
            array('n', 'N', 'c', 'C',),
            $string
        );

        //Esta parte se encarga de eliminar cualquier caracter extraño
        /*$string = str_replace(
            array("\\", "¨", "º", "-", "~",
             "#", "@", "|", "!",
             "·", "$", "%", "&", "/",
             "(", ")", "?", "'", "¡",
             "¿", "[", "^", "<code>", "]",
             "+", "}", "{", "¨", "´",
             ">", "< ", ";", ",", ":",
             ".", " "),
        '',
        $string);*/
    return $string;
}
    public static  function deleteFromArray(&$array, $deleteIt, $useOldKeys = FALSE)
    {
        $key = array_search($deleteIt,$array,TRUE);
        if($key === FALSE)
            return FALSE;
        unset($array[$key]);
        if(!$useOldKeys)
            $array = array_values($array);
        return TRUE;
    }
}
