<?php namespace App\Models;

use App\PedidosGeneral;
use App\Models\UserDomicilio;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Negocio extends Model {

	protected $table = "negocios";
    protected $fillable = ['nombre','descripcion','latitud','longitud'];

    public static function listaNegocios($api_key,$categoria_id){
        $user = UserDomicilio::where('api_key',$api_key)->first();
        $radio = PedidosGeneral::radioBusqueda($user->latitud,$user->longitud);

        $negocios = User::select("*")
            ->whereBetween('latitud', [$radio['latitud_min'], $radio['latitud_max']])
            ->whereBetween('longitud', [$radio['longitud_max'],$radio['longitud_min']])
            ->where('categoria_id',$categoria_id)
            ->get();
        //dd(count($negocios));

        $negocios_aux = [];
        $i=0;
        foreach ($negocios as $value){
            $negocios_aux[$i] = [ "id" => $value->id, "nombre" => $value->nombre_negocio];
            //echo harvestine($user->latitud,$user->longitud, $value->latitud, $value->longitud);
            $i++;
        }
        return $negocios_aux;
    }

}
