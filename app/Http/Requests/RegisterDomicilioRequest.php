<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class RegisterDomicilioRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		return [
			"nombre"=>"required",
			"identificacion"=>"required|numeric",
			"direccion"=>"required",
			"telefono"=>"required",
			"correo"=>"required|email|unique:user_domicilio",
		];
	}


	public static function getRules()
	{
		return [
			"nombre"=>"required",
			"identificacion"=>"required|numeric",
			"direccion"=>"required",
			"telefono"=>"required",
			"correo"=>"required|email|unique:user_domicilio",
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
			'nombre.required' => 'El campo nombre es requerido',
			'identificacion.required' => 'El campo identificacion es requerido',
			'identificacion.numeric' => 'El campo identificacion es númerico',
			'direccion.required' => 'El campo direccion es requerido',
			'telefono.required' => 'El campo telefono es requerido',
			'correo.required' => 'El campo correo es requerido',
			'correo.email' => 'El valor del campo correo es incorrecto'
		];
	}


	public static function getMessages()
	{
		return [
			'nombre.required' => 'El campo nombre es requerido',
			'identificacion.required' => 'El campo identificacion es requerido',
			'identificacion.numeric' => 'El campo identificacion es númerico',
			'direccion.required' => 'El campo direccion es requerido',
			'telefono.required' => 'El campo telefono es requerido',
			'correo.required' => 'El campo correo es requerido',
			'correo.email' => 'El valor del campo correo es incorrecto',
			'correo.unique' => 'Ya existe un usuario registrado con este correo'
		];
	}

}
