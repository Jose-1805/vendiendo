<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class RequestEditarResolucion extends Request {

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
		return [
			"numero"=>"required",
			"fecha"=>"required|date",
			"fecha_vencimiento"=>"required|date",
			"fecha_notificacion"=>"date",
			"inicio"=>"numeric|different:fin",
			"fin"=>"numeric",
			"numero_notificacion"=>"numeric",
			"id"=>"required"
		];
	}

	public function messages()
	{
		return [
			"numero.required"=>"El campo número es requerido",
			"numero.unique"=>"Ya se ha registrado una resolución con el número ingresado",
			"fecha.required"=>"El campo fecha es requerido",
			"fecha.date"=>"El campo fecha no contiene un formato válido",
			"inicio.numeric"=>"El campo número inicio factura debe ser numerico",
			"inicio.different"=>"El campo número inicio factura y número fin factura no pueden ser iguales",
			"fin.numeric"=>"El campo número fin factura debe ser numerico",
			"id.required"=>"La información enviada es incorrecta",
			"fecha_vencimiento.required"=> "El campo fecha de vencimiento es requerido",
		];
	}

}
