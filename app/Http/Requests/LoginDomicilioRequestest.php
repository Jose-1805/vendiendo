<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class LoginDomicilioRequestest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return false;
	}
	public static function getRules()
	{
		return [
			"correo"=>"required|email",
			"password" => "required",
			"latitud" => "required",
			"longitud" => "required",
		];
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			//
		];
	}
	public static function getMessages()
	{
		return [
			'password.required'  => 'El campo contraseña es requerido',
			'correo.required' => 'El campo correo es requerido',
			'correo.email' => 'El valor del campo correo es incorrecto',
			'latitud.required' => 'Se requiere datos de posicionamiento para hacer de este servicio un servicio más eficiente',
			'longitud.required' => 'Se requiere datos de posicionamiento para hacer de este servicio un servicio más eficiente'
		];
	}

}
