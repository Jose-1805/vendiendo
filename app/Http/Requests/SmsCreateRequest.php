<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class SmsCreateRequest extends Request {

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
		$ayer = date('Y-m-d', mktime(0, 0, 0, date('m'),date('d')-1,date('Y')));

		$hora_actual = date('Y-m-d H:i:s');
		$nuevafecha = strtotime ( '+'.config('options.tiempo_programacion_sms').' minute' , strtotime ( $hora_actual ) ) ;
		$nuevafecha = date ( 'Y-m-d H:i:s' , $nuevafecha );


		return [
			"titulo"=>"required|between:1,20",
			"mensaje"=>"required|between:1,120",
			"f_h_programacion"=>"required|after:".$nuevafecha,
			"telefonos"=>"required|max:5499"
		];
	}
	public function messages()
	{
		$hora_actual = date('Y-m-d H:i:s');
		$nuevafecha = strtotime ( '+'.config('options.tiempo_programacion_sms').' minute' , strtotime ( $hora_actual ) ) ;
		$nuevafecha = strtotime ( '+1 minute' , strtotime ( date ( 'Y-m-d H:i' , $nuevafecha ) ) );
		$nuevafecha = date ( 'Y-m-d H:i' , $nuevafecha );
		return [
			'titulo.required' => 'El nombre del negocio es obligatorio',
			'titulo.between' => 'El nombre del negocio debe tener mínimo 1 carácter y máximo 20 carácteres',

			'mensaje.required' => 'El campo mensaje es obligatorio',
			'mensaje.between' => 'El campo mensaje debe tener mínimo 1 carácter y máximo 120 carácteres',

			'f_h_programacion.required' => 'El campo fecha es obligatorio',
			'f_h_programacion.after' => 'El campo fecha no puede ser inferior al dia y hora actual, podras crear el mensaje de texto a partir de '.$nuevafecha,

			'telefonos.required' => 'Debe asignar al menos un telefono',
			'telefonos.max' => 'Solo puede enviar hasta 500 mensajes de texto',

		];
	}

}
