<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Models\Caja;

class CreateCajaRequest extends Request {

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
		$ultima_caja = Caja::cajasPermitidas()->orderBy("caja.created_at","DESC")->first();
		if($ultima_caja)
			return [];

		return [
			"efectivo_inicial" => "required|numeric"
		];
	}
	public function messages()
	{
		return [
			'efectivo_inicial.required' => 'El campo es obligatorio',
			'efectivo_inicial.numeric' => 'El campo es numerico'
		];
	}

}
