<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Models\Producto;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionesController extends Controller {

	public function postCountNotificaciones(Request $request){
		if($request->has("tipo")) {
			return $notificaciones = Auth::user()->notificaciones()
				->where("usuarios_notificaciones.estado","no leído")
				->where("notificaciones.tipo",$request->input("tipo"))->get()->count();
		}
		return response(["Error"=>["La información ongresada es incorrecta."]],422);
	}

	public function postNotificaciones(Request $request){
		$indice = 0;
		if($request->has("indice"))
			$indice = $request->input("indice");
		if($request->has("tipo")) {
			$notificaciones = Auth::user()->notificaciones()->select("notificaciones.*","usuarios_notificaciones.estado")
				->where("notificaciones.tipo",$request->input("tipo"))->orderBy("notificaciones.created_at","DESC")->orderBy("notificaciones.id","DESC")->skip($indice)->take(10)->get();

			$vista = view("templates/notificaciones/lista")->with("notificaciones", $notificaciones)->with("tipo",$request->input("tipo"))->render();
			$cantidad = count($notificaciones);
			return ['vista'=>$vista,'cantidad'=>$cantidad];
		}
		return response(["Error"=>["La información ongresada es incorrecta."]],422);
	}

	public function postMarcarNotificacionLeida(Request $request){
		if($request->has("notificacion") && $request->input("tipo")){
			$notificacion = Notificacion::find($request->input("notificacion"));
			if($notificacion){
				$usuario = $notificacion->usuarios()->where("usuarios.id",Auth::user()->id)->first();
				if($usuario){
					$usuario->notificaciones()->updateExistingPivot($notificacion->id, ["estado"=>"leído"]);
						return ["success"=>true];
				}
				return response(["Error"=>["1 La informaciòn enviada es incorrecta"]],422);
			}
			return response(["Error"=>["2 La informaciòn enviada es incorrecta"]],422);
		}
		return response(["Error"=>["3 La informaciòn enviada es incorrecta"]],422);
	}

	public function postMarcarTodoLeido(Request $request){
		if($request->input("tipo")){
				$notificaciones = Auth::user()->notificaciones()->where("notificaciones.tipo",$request->input("tipo"))->get();
				if(count($notificaciones)){
					foreach ($notificaciones as $notificacion)
						Auth::user()->notificaciones()->updateExistingPivot($notificacion->id, ["estado"=>"leído"]);
				}
				return ["success"=>true];
		}
		return response(["Error"=>["3 La informaciòn enviada es incorrecta"]],422);
	}
}
