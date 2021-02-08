<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class RequestNuevaResolucion extends Request {

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
			"numero"=>"required|unique:resoluciones,numero",
			"prefijo"=>"max:4|alpha_num",
			"fecha"=>"required|date",
			"fecha_vencimiento"=>"required|date",
			"fecha_notificacion"=>"date",
			"inicio"=>"required|numeric|different:fin",
			"fin"=>"required|numeric",
			"numero_notificacion"=>"numeric"
		];
	}

	public function messages()
	{
		return [
			"numero.required"=>"El campo número es requerido",
			"numero.unique"=>"Ya se ha registrado una resolución con el número ingresado",
			"prefijo.max"=>"El campo prefijo puede contener máximo 4 caracteres",
			"prefijo.alpha_num"=>"El campo prefijo puede contener únicamente letras y números",
			"fecha.required"=>"El campo fecha es requerido",
			"fecha.date"=>"El campo fecha no contiene un formato válido",
			"inicio.required"=>"El campo número inicio factura es requerido",
			"inicio.numeric"=>"El campo número inicio factura debe ser numerico",
			"inicio.different"=>"El campo número inicio factura y número fin factura no pueden ser iguales",
			"fin.required"=>"El campo número fin factura es requerido",
			"fin.numeric"=>"El campo número fin factura debe ser numerico",
			"fecha_vencimiento.required"=> "El campo fecha de vencimiento es requerido",
		];
	}

}
