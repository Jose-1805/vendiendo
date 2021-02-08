<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Models\Caja;
use App\Models\Cajas;
use Illuminate\Support\Facades\Auth;

class RequestCajaUsuarioTransaccion extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		$data = [
			"valor"=>"required|numeric",
			"tipo"=>"required|in:Retiro,Deposito,Envio a caja maestra",
			"comentario"=>"max:250",
			"caja"=>"required"
		];

		return $data;
	}

	public function messages()
	{
		return [
			"valor.required"=>"El campo valor es requerido",
			"valor.numeric"=>"El campo valor debe ser de tipo numérico",

			"tipo.required"=>"El campo tipo de transacción es obligatorio",
			"tipo.in"=>"La información enviada es incorrecta",

			"comentario.max"=>"La cantidad máxima de caracteres permitidos en el comentario es 250",

			"caja.requierd"=>"La información enviada es incorrecta",
		];
	}

}
