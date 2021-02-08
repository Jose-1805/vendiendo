<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class resetPasswordRequest extends Request {

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
			'token' => 'required',
			'email' => 'required|email',
			'password' => 'required|confirmed|min:6',
		];
	}
	public function messages()
	{
		return [
			'email.required' => 'El Correo es requerido',
			'email.email'	 => 'Debe ingresar un correo valido',
			'password.required' => 'La contraseña es requerida',
			'password.confirmed' => 'La contraseña no coincide',
			'password.min' => 'La contraseña deben tener al menos seis caracteres'
		];
	}

}
