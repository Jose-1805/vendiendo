<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\User;
use Illuminate\Support\Facades\Auth;

class RequestGasto extends Request {

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
			"valor"=>"required|numeric|digits_between:3,20",
			"descripcion"=>"max:500",
		];

		return $data;
	}
	public function messages(){
		return [
			"valor.required"=>"El campo valor es requerido",
			"valor.numeric"=>"El campo valor debe ser numérico",
			"valor.digits_between"=>"El campo valor es debe contener entre 3 y 20 dígitos",

			"descripcion.max"=>"El campo descripciòn debe contener máximo 500 caracteres",
		];
	}

}
