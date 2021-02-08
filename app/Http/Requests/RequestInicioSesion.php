<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class RequestInicioSesion extends Request {

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
			"email"=>"required|email",
			"password" => "required"
		];
	}


	public static function getRules()
	{
		return [
			"email"=>"required|email",
			"password" => "required"
		];
	}

	/**
	 * Get the error messages for the defined validation rules.
	 *
	 * @return array
	 */
	public function messages()
	{
		return [
			'email.required' => 'El campo email es requerido',
			'password.required'  => 'El campo clave es requerido',
			'email.email' => 'El valor del campo email es incorrecto'
		];
	}


	public static function getMessages()
	{
		return [
			'email.required' => 'El campo email es requerido',
			'password.required'  => 'El campo clave es requerido',
			'email.email' => 'El valor del campo email es incorrecto'
		];
	}
}
