<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenPuntos extends Model {

	protected $table = "token_puntos";
	protected $fillable = [
		"valor",
		"token",
		"fecha_vigencia",
		"cliente_id",
		"factura_id"
	];

	public function generarToken(){
		$cantidad = rand(15,20);
		$cadena = "";
        $token_unico = false;
        while(!$token_unico) {
            for ($i = 0; $i < $cantidad; $i++) {
                $cadena .= $this->getRandom();
            }
            $token_aux = TokenPuntos::where("token",$cadena)->where("fecha_vigencia",date("Y-m-d"))->get();
            if(!count($token_aux)){
                $token_unico = true;
            }
        }
		$this->token = $cadena;
	}

	public  function getRandom(){
		$an = "0123456789";
		$su = strlen($an) - 1;
		return substr($an, rand(0, $su), 1);
	}

}
