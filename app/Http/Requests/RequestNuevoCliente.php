<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class RequestNuevoCliente extends Request {

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
			"nombre"=>"required|max:100",
			"tipo_identificacion"=>"required|in:C.C,T.I,NIT",
			"identificacion"=>"required|max:50",
			"telefono"=>"digits_between:7,20",
			"correo"=>"email|max:100",
			"drección"=>"max:100"
		];
	}

	public function messages()
	{
		return [
			"nombre.required"=>"El campo nombre es requerido",
			"nombre.max"=>"El nombre debe tener máximo 100 caracteres",

			"tipo_identificacion.required"=>"El campo tipo de identificacion es requerido",
			"tipo_identificacion.in"=>"La información enviada es incorrecta",

			"identificacion.required"=>"El campo identificación es requerido",
			"identificacion.max"=>"El campo identificación debe tener máximo 50 caracteres",

			"telefono.digits_between"=>"El campo telefono debe contener entre 7 y 20 dígitos",

			"correo.email"=>"El campo correo no contiene una dirección de correo electrónico válida",
			"correo.max"=>"El correo debe tener máximo 100 caracteres",

			"direccion.max"=>"El direccion debe tener máximo 100 caracteres",

		];
	}

}
