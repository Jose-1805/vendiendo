<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Models\CostoFijo;
use Illuminate\Support\Facades\Auth;

class createCostoFijoRequest extends Request {

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
			'nombre' => 'required'
		];

		if(Auth::user()->bodegas == 'si'){
		    if(Auth::user()->admin_bodegas == 'si') {
                if (
                    count(
                        CostoFijo::where("usuario_id", Auth::user()->userAdminId())
                            ->whereNull("almacen_id")
                            ->where("nombre", $this->input("nombre"))->get()
                    )
                ) {
                    $data["nombre"] .= "|unique:costos_fijos,nombre";
                }
            }else{
		        $almacen = Auth::user()->almacenActual();
                if (
                    count(
                        CostoFijo::where("usuario_id", Auth::user()->userAdminId())
                            ->where("almacen_id",$almacen->id)
                            ->where("nombre", $this->input("nombre"))->get()
                    )
                ) {
                    $data["nombre"] .= "|unique:costos_fijos,nombre";
                }
            }
        }else{
            if(count(CostoFijo::where("usuario_id",Auth::user()->userAdminId())->where("nombre",$this->input("nombre"))->get())){
                $data["nombre"] .= "|unique:costos_fijos,nombre";
            }
        }

		return $data;
	}
	public function messages(){
		return [
			"nombre.required"=>"El campo nombre es requerido",
			"nombre.unique"=>"Ya existe un registro con este nombre",
		];
	}

}
