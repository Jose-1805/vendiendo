<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Models\Caja;
use App\Models\Cajas;
use Illuminate\Support\Facades\Auth;

class RequestCaja extends Request {

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
		$data = [];
		
		if($this->has("caja")){
			$caja = Cajas::find($this->input("caja"));
			if(!$caja->permitirAccion()) {
				$data["estado"] = "required|in:abierta,cerrada,otro";
				$data["razon_estado"] = "required_if:estado,otro|max:200";
			}else{
			    if(Auth::user()->bodegas == 'si') {
			        $almacen = Auth::user()->almacenActual();
                    $data["nombre"] = "required|max:50|unique:cajas,nombre," . $this->input('caja') . ",id,almacen_id," . $almacen->id;
                } else {
                    $data["nombre"] = "required|max:50|unique:cajas,nombre," . $this->input('caja') . ",id,usuario_id," . Auth::user()->userAdminId();
                }

				$data["prefijo"] = "required|max:5|unique:cajas,prefijo,".$this->input("caja").",id,usuario_id,".Auth::user()->userAdminId();
			}
		}else{
            if(Auth::user()->bodegas == 'si') {
                $almacen = Auth::user()->almacenActual();
                $data["nombre"] = "required|max:50|unique:cajas,nombre," . $this->input('caja') . ",id,almacen_id," . $almacen->id;

                $data["nombre"] = "required|max:50|unique:cajas,nombre,null,id,almacen_id,".$almacen->id;
                $data["prefijo"] = "required|max:5|unique:cajas,prefijo,null,id,almacen_id,".$almacen->id;
            }else{
                $data["nombre"] = "required|max:50|unique:cajas,nombre,null,id,usuario_id,".Auth::user()->userAdminId();
                $data["prefijo"] = "required|max:5|unique:cajas,prefijo,null,id,usuario_id,".Auth::user()->userAdminId();
            }

		}

		return $data;
	}

	public function messages()
	{
		return [
			"nombre.required"=>"El campo nombre es requerido.",
			"nombre.max"=>"El campo nombre debe tener máximo 50 caracteres.",
			"nombre.unique"=>"Ya existe una caja con el nombre ingresado.",

			"prefijo.required"=>"El campo prefijo es requerido.",
			"prefijo.max"=>"El campo prefijo debe tener máximo 5 caracteres.",
			"prefijo.unique"=>"Ya existe una caja con el prefijo ingresado.",

			"estado.required"=>"El campo estado es requerido.",
			"estado.in"=>"La información enviada es incorrecta.",

			"razon_estado.required_if" => "El campo razón de estado es obligatorio",
			"razon_estado.max" => "El campo razón de estado puede contener máximo 200 caracteres",
		];
	}

}
