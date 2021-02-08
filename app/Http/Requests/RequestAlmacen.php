<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\User;
use Illuminate\Support\Facades\Auth;

class RequestAlmacen extends Request {

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
		$data = [
			'nombre'=>'required|max:150',
			'prefijo'=>'required|max:4',
			'direccion'=>'required|max:150',
			'telefono'=>'required|numeric|digits_between:6,20',
			'latitud'=>'max:100',
			'longitud'=>'max:100',
			//'administrador'=>'required',
		];

		return $data;
	}
	public function messages(){
		return [
			'nombre.required'=>'El campo nombre es requerido',
			'nombre.max'=>'El campo nombre puede contener 150 caracteres como máximo',

			'prefijo.required'=>'El campo prefijo es requerido',
			'prefijo.max'=>'El campo prefijo puede contener 4 caracteres como máximo',

			'direccion.required'=>'El campo dirección es requerido',
			'direccion.max'=>'El campo dirección puede contener 150 caracteres como máximo',

			'telefono.required'=>'El campo teléfono es requerido',
			'telefono.numeric'=>'El campo teléfono no contiene un formato válido',
			'telefono.digits_between'=>'El campo teléfono debe contener entre 6 y 20 digitos',


			//'administrador.required'=>'Seleccione un administrador',

			'latitud.required'=>'Su información de ubicación no es válida',
			'latitud.max'=>'Su información de ubicación no es válida',

			'longitud.required'=>'Su información de ubicación no es válida',
			'longitud.max'=>'Su información de ubicación no es válida',
		];
	}

}
