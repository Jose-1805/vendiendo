<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class PedidosGeneral extends Model {

	 public static function radioBusqueda($latitud,$longitud){

         $latitud_min = $latitud - config('options.radio_busqueda');
         $latitud_max = $latitud + config('options.radio_busqueda');
         $longitud_min = $longitud - config('options.radio_busqueda');
         $longitud_max = $longitud + config('options.radio_busqueda');

         return [
             'latitud_min'=>$latitud_min,
             'latitud_max'=>$latitud_max,
             'longitud_min'=>$longitud_min,
             'longitud_max'=>$longitud_max
         ];
     }

}
