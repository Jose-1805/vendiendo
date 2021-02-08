<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class EventoRequest extends Request {

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
	    if($this->has('id')){
            return [
                "titulo" => "required|max:50",
                "descripcion" => "required|max:400",
                "color" => "required",
            ];
        }else {
            return [
                "inicio" => "required",
                "fin" => "required",
                "titulo" => "required|max:50",
                "descripcion" => "required|max:400",
                "color" => "required",
            ];
        }
	}
	public function messages(){
		return [
			"inicio.required"=>"La fecha de inicio es requerida",
			"fin.required"=>"La fecha de fin es requerida",
			"titulo.required"=>"El titulo del evento es requerido",
			"descripcion.required"=>"La descripción del evento es requerida",
			"color.required"=>"El color del evento es requerido",
			"titulo.max"=>"El titulo debe tener máximo 100 caracteres",
			"descripcion.max"=>"La descripción debe tener máximo 200 caracteres",

		];
	}

}
