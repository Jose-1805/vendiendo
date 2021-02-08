<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Support\Facades\Auth;

class RequestCuentaBancaria extends Request {

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
			"banco"=>"required|exists:bancos,id",
			"titular"=>"required|max:100",
			"numero"=>"required|max:30|unique:cuentas_bancos,numero,null,id,banco_id,".$this->input("banco"),
			"saldo"=>"required|numeric"
		];

		return $data;
	}

	public function messages()
	{
		return [
			"banco.required"=>"El campo banco es requerido.",
			"banco.exists"=>"La información enviada es incorrecta.",

			"titular.required"=>"El campo titular es requerido.",
			"titular.max"=>"El campo titular debe tener máximo 100 caracteres.",

			"numero.required"=>"El campo número es requerido.",
			"numero.max"=>"El campo número debe tener máximo 30 caracteres.",
			"numero.unique"=>"El número de cuenta ingresado ya se encuentra registrado en el banco seleccionado.",

			"saldo.required"=>"El campo saldo es requerido.",
			"saldo.numeric"=>"El campo saldo debe ser de tipo numerico.",
		];
	}

}
