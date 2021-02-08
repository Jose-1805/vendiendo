<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class emailPasswordRequest extends Request {

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
			'email' => 'required|email|exists:usuarios,email'
		];
	}
	public function messages()
	{
		return [
			'email.required' => 'El correo es requerido',
			'email.email' => 'Debe ingresar una dirección de correo electrónico válida',
			'email.exists' => 'La dirección de correo electrónico no esta registrada en la base de datos'
		];
	}

}
