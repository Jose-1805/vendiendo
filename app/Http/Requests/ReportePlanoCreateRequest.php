<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class ReportePlanoCreateRequest extends Request {

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
			"nombre"=>"required",
			"seccion"=>"required",
			"campos"=>"required"
		];
	}
	public function messages()
	{
		return [
			'nombre.required' => 'El campo nombre es obligatorio',
			'seccion.required' => 'El campo seccion es obligatorio',
			'campos.required' => 'Debe asignar al menos un campo al reporte',

		];
	}

}
