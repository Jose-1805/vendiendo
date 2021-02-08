<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class DevolucionRequest extends Request {

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
			"cantidad" => "required|numeric",
			"motivo"	 => "required|max:100"
		];
	}
	public function messages(){
		return [
			"cantidad.required"=>"El campo cantidad es requerido",
			"cantidad.numeric"=>"El campo cantidad debe ser numerico",

			"motivo.required"=>"El campo motivo es requerido",
			"motivo.max"=>"El campo motivo debe tener máximo 100 caracteres",

		];
	}

}
