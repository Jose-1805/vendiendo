<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\User;
use Illuminate\Support\Facades\Auth;

class RequestAbono extends Request {

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
			"nota"=>"max:500",
			"usuario_id"=>"required|exists:usuarios,id",
			"tipo_abono"=>"required|in:factura,compra",
			"tipo_abono_id"=>"required"
		];

		if($this->input("tipo_abono") == "factura"){
			$data["tipo_abono_id"] .= "|exists:facturas,id";
		}else if($this->input("tipo_abono") == "compra"){
			$data["tipo_abono_id"] .= "|exists:compras,id";
		}
		return $data;
	}
	public function messages(){
		return [
			"valor.required"=>"El campo valor es requerido",
			"valor.numeric"=>"El campo valor debe ser numérico",
			"valor.digits_between"=>"El campo valor debe contener entre 3 y 20 dígitos",

			"nota.max"=>"El campo nota debe contener máximo 500 caracteres",

			"usuario_id.required"=>"La información enviada es incorrecta",
			"usuario_id.exists"=>"La información enviada es incorrecta",

			"tipo_abono.required"=>"La información enviada es incorrecta",
			"tipo_abono.in"=>"La información enviada es incorrecta",

			"tipo_abono_id.required"=>"La información enviada es incorrecta",
			"tipo_abono_id.exists"=>"La información enviada es incorrecta",
		];
	}

}
