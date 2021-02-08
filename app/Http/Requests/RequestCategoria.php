<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Support\Facades\Auth;

class RequestCategoria extends Request {

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
			"nombre"=>"required|max:100",
			"descripcion"=>"required|max:200"
		];
		
		if($this->has("accion") && $this->input("accion") == "Agregar"){
			$data["nombre"] .= "|unique:categorias,nombre,NULL,id,usuario_id,".Auth::user()->userAdminId();
		}else{
			$data["nombre"] .= "|unique:categorias,nombre,".$this->input('categoria').",id,usuario_id,".Auth::user()->userAdminId();
		}

		return $data;
	}

	public function messages()
	{
		return [
			"nombre.required"=>"El campo nombre es requerido.",
			"nombre.max"=>"El campo nombre debe tener máximo 100 caracteres.",
			"nombre.unique"=>"Ya existe una categoria con el nombre ingresado.",
			"descripcion.required"=>"El campo descripción es requerido.",
			"descripcion.max"=>"El campo descripción debe tener máximo 200 caracteres.",
		];
	}

}
