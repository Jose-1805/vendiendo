<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Support\Facades\Auth;

class RequestOperacionCajaMaestra extends Request {

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
			"cuenta_bancaria"=>"required|exists:cuentas_bancos,id",
			"valor"=>"required|numeric",
			"obsevacion"=>"max:250",
		];

        if(!(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'no'))
            $data["tipo"]="required|in:Retiro,Consignación";
		return $data;
	}

	public function messages()
	{
		return [
			"tipo.required"=>"El campo tipo de operación es requerido.",
			"tipo.in"=>"La información enviada es incorrecta.",

			"cuenta_bancaria.required"=>"El campo cuenta bancaria es requerido.",
			"cuenta_bancaria.exists"=>"La información enviada es incorrecta.",

			"valor.required"=>"El campo valor es requerido.",
			"valor.numeric"=>"El campo valor debe ser de tipo numerico.",

			"observacion.required"=>"El campo observación es requerido.",
			"observacion.max"=>"El campo observación debe tener máximo 250 caracteres.",
		];
	}

}
