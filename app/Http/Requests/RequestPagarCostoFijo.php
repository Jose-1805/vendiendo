<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class RequestPagarCostoFijo extends Request {

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
			"valor" => "required|numeric|min:0",
			"fecha" => "required|date"
		];
	}
	
	public function messages(){
		return [
			"valor.required" => "El campo valor es requerido.",
			"valor.numeric" => "El campo valor debe ser de tipo numérico.",
			"valor.min" => "El valor mínimo permitido para el campo valor es 0.",

			"fecha.required" => "El campo fecha es requerido.",
			"fecha.required" => "El campo fecha no contiene el formato correcto.",
		];
	}


}
