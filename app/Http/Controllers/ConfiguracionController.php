<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Consignacion;
use App\Models\CuentaBancaria;
use App\Models\Factura;
use App\Models\TipoPago;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\RequestSistemaPuntos;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RequestCambiarInformacionNegocio;

class ConfiguracionController extends Controller {

	public function __construct()
	{
		$this->middleware("auth");
		$this->middleware("terminosCondiciones");
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		return view("configuracion.index");
	}

	public function postReiniciarApiKey(){
		$user = Auth::user();
		$user->api_key = null;
		$user->save();
		return ["success"=>true];
	}

	public function postCambiarContrasena(Request $request){
		if(!($request->has("password-old") && $request->has("password-new") && $request->has("password-check"))){
			return response(["Error"=>["Todos los campos son requeridos"]],422);
		}

		if(Hash::check($request->input("password-old"),Auth::user()->password)){
			if($request->input("password-new") == $request->input("password-check")){
				$user = Auth::user();
				$user->password = Hash::make($request->input("password-new"));
				$user->save();
				Session::flash("mensaje", "La contraseña ha sido editada con éxito");
				return ["success"=>true];
			}else{
				return response(["Error"=>["La contraseña nueva no coincide con la verificación"]],422);
			}
		}else{
			return response(["Error"=>["La contraseña anterior es incorrecta"]],422);
		}
	}

	public function postEstablecerSistemaPuntos(RequestSistemaPuntos $request)
	{
		if (\Illuminate\Support\Facades\Auth::user()->plan()->puntos == "si" && \Illuminate\Support\Facades\Auth::user()->perfil->nombre == "administrador") {
			$user = User::find(Auth::user()->userAdminId());
			if ($user) {
				$user->caducidad_puntos = $request->input("caducidad_puntos");
				$user->pesos_venta = $request->input("pesos_venta");
				$user->puntos_venta = $request->input("puntos_venta");
				$user->pesos_pago = $request->input("pesos_pago");
				$user->puntos_pago = $request->input("puntos_pago");
				$user->save();
				Session::flash("mensaje", "El sistema de puntos se ha establecido con éxito");
				return ["success" => true];
			}
			return response(["Error" => ["La información enviada es incorrecta"]], 422);
		}
		return response(["Error" => ["Usted no tiene permisos para realizar esta acción"]], 401);
	}

	public function postUpdatedFacturaAbierta(Request $request){
		if(!$request->ajax())return redirect("/");

		$user = Auth::user();
		if($user->plan()->factura_abierta == "si" && $user->perfil->nombre == "administrador"){
			if($user->factura_abierta == "no")$user->factura_abierta = "si";
			else $user->factura_abierta = "no";
			$user->save();
			Session::flash("mensaje","El estado de la funcionalidad de factura abiera ha sido editado con éxito");
			return ["success"=>true];
		}
	}

	public function postCambiarInformacionNegocio(RequestCambiarInformacionNegocio $request){
		$user = Auth::user();
		if($user->perfil->nombre == "administrador"){
			$user->nombre_negocio = $request->input("nombre_negocio");
			$user->nit = $request->input("nit");
			$user->telefono = $request->input("telefono");
			$user->save();
			Session::flash("mensaje","La información del negocio ha sido editada con éxito");
			return ["success"=>true];
		}
		return response(["error"=>["Unauthorized."]],401);
	}

	public function postAdministrarPanelGraficas(Request $request){
		$user = Auth::user();
		if($user->perfil->nombre == "administrador"){
			$user->panel_graficas = "no";
			$user->save();
			$users = User::permitidos()->get();

			foreach ($users as $u){
				$u->panel_graficas = "no";
				$u->save();
			}

			if($request->has("usuarios") && is_array($request->input("usuarios"))){
				$users = User::permitidos()->whereIn("id",$request->input("usuarios"))->get();

				foreach ($users as $u){
					$u->panel_graficas = "si";
					$u->save();
				}

				if(in_array($user->id,$request->input("usuarios"))){
					$user->panel_graficas = "si";
					$user->save();
				}
			}
			Session::flash("mensaje","La información ha sido actualizada con éxito");
			return ["success"=>true];
		}
		return response(["error"=>["Unauthorized."]],401);
	}

	public function postAdministrarFormasPago(Request $request){
		$user = Auth::user();
		if(
            ($user->perfil->nombre == "administrador" && $user->bodegas == 'no')
            ||($user->perfil->nombre == "administrador" && $user->bodegas == 'si' && $user->admin_bodegas == 'si')
        ){

		    if($request->has('tipos_pago')){
		        $formas_pago = $request->input('tipos_pago');
		        $admin = User::find($user->userAdminId());
		        DB::beginTransaction();
		        DB::statement('DELETE FROM usuarios_tipos_pago WHERE usuario_id = '.$admin->id);
		        $banco = null;
		        if($request->has('banco')){
		            $banco = CuentaBancaria::permitidos()->where('id',$request->input('banco'))->first();
		            if(!$banco)return response(['error'=>['La información enviada es incorrecta']],422);
                }

		        foreach ($formas_pago as $id){
		            $forma_pago = TipoPago::find($id);
		            //el valor se debe enviar a un banco
		            if($forma_pago->valor_a_caja == 'no'){
		                if(!$banco)return response(['error'=>['Para establecer la forma de pago "'.$forma_pago->nombre.'" debe seleccionar una cuenta de banco']],422);
		                else $admin->cuenta_bancaria_forma_pago = $banco->id;
                    }
                    $admin->tiposPago()->save($forma_pago,['estado'=>'habilitado']);
                }
                $admin->save();
                DB::commit();
            }


			Session::flash("mensaje","La información ha sido actualizada con éxito");
			return ["success"=>true];
		}
		return response(["error"=>["Unauthorized."]],401);
	}

	public function postRealizarConsignaciones(Request $request)
    {
        $admin = User::find(Auth::user()->userAdminId());
        $pagos_sin_consignacion = Factura::permitidos()->select('facturas_tipos_pago.*')
            ->join('facturas_tipos_pago','facturas.id','=','facturas_tipos_pago.factura_id')
            ->join('tipos_pago','facturas_tipos_pago.tipo_pago_id','=','tipos_pago.id')
            ->leftJoin('consignaciones','facturas_tipos_pago.id','=','consignaciones.factura_tipo_pago_id')
            ->whereNull('consignaciones.id')
            ->where('tipos_pago.valor_a_caja','no')
            ->orderBy('consignaciones.id','DESC')
            ->get();
        $cuenta = CuentaBancaria::permitidos()->where("id",$admin->cuenta_bancaria_forma_pago)->first();
        if($cuenta){
            DB::beginTransaction();
            foreach ($pagos_sin_consignacion as $pago) {
                $cuenta->saldo += $pago->valor;
                $consignacion = new Consignacion();
                $consignacion->valor = $pago->valor;
                $consignacion->saldo = $cuenta->saldo;
                $consignacion->cuenta_id = $cuenta->id;
                $consignacion->factura_tipo_pago_id = $pago->id;
                $consignacion->usuario_creador_id = Auth::user()->id;
                $consignacion->save();
            }
            $cuenta->save();
            DB::commit();
            return ['success'=>true];
        }else{
            return response(["Error" => ["No se ha establecido la cuenta bancaria para realizar las consignaciones."]], 422);
        }
    }
}
