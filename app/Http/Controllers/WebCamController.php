<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use App\Models\WebCam;
use App\Models\WebCamUbicacion;
use Illuminate\Pagination\Paginator;
use App\Http\Requests\RequestWebCam;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\User;
use Illuminate\Support\Facades\URL;

use Illuminate\Http\Request;

class WebCamController extends Controller {

	public function __construct()
	{
		$this->middleware("auth");
		$this->middleware("modWebCam");
		$this->middleware("modConfiguracion");
		$this->middleware("terminosCondiciones");
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex(Request $request)
	{
		 $filtro = "";
		if($request->has("filtro")){
			$filtro = $request->get("filtro");
			$f = "%".$filtro."%";
			$webCam = WebCam::with('ubicacionID')->with('userID')->where("nombre","like",$f)->groupby('nombre')->orderBy("updated_at", "DESC")->paginate(10);
		}else {
			$webCam = WebCam::with('ubicacionID')->with('userID')->groupby('nombre')->orderBy("updated_at", "DESC")->paginate(10);
		}
	 	$ubicaciones = WebCamUbicacion::lists('nombre','id');
		return view("webcam.index")->with("webCam",$webCam)->with("filtro",$filtro)->with('ubicaciones',$ubicaciones);
	}

	public function getListWebcam(Request $request){
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = 'web_cam.nombre';//$columna[$sortColumnIndex]['data'];

        $webCam = WebCam::select("web_cam.id as id", "web_cam.url as url", "web_cam.nombre as nombre", "web_cam.alias as alias", "web_cam_ubicacion.nombre as nombre_ubicacion")
            ->join("web_cam_ubicacion", "web_cam_ubicacion.id", "=", "web_cam.ubicacion_id")
            ->groupby('web_cam.nombre')->orderBy("web_cam.updated_at", "DESC");

        $webCam = $webCam->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $webCam->count();
        //BUSCAR
        if ($search['value'] != null) {
            $webCam = $webCam->whereRaw(
                " ( web_cam.nombre LIKE '%" . $search["value"] . "%' OR " .
                " alias LIKE '%" . $search["value"] . "%' OR" .
                " web_cam_ubicacion.nombre LIKE '%" . $search["value"] . "%')");
        }

        $parcialRegistros = $webCam->count();
        $webCam = $webCam->skip($start)->take($length);

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $webCam->get()];

        return response()->json($data);

    }


	public function postFiltro(Request $request){
		$filtro = "";
		if($request->has("filtro")){
			$filtro = $request->get("filtro");
			$f = "%".$filtro."%";
			$webCam = WebCam::with(array('ubicacionID' => function($query) use ($f)
				{
				     $query->orWhere("web_cam_ubicacion.nombre","like",$f);
				}))->with('userID')
			->orWhere("web_cam.nombre","like",$f)
			->orWhere("web_cam.alias","like",$f)
			->groupby('nombre')->orderBy("updated_at", "DESC")->paginate(10);

			//return $webCam->toSql();
		}else {
			$webCam = WebCam::with('ubicacionID')->with('userID')->groupby('web_cam.nombre')->orderBy("updated_at", "DESC")->paginate(10);
		}
		$webCam->setPath(url('/webcam'));
		return view("webcam.lista")->with("webCam",$webCam);
	}

	public function postAccion(RequestWebCam $request)
	{
		if($request->has("accion")) {
			if($request->input("accion") == "Agregar") {
				if(Auth::user()->permitirFuncion("Crear","Web Cam","inicio")) {
					$webcam = new webcam();
					$webcam->fill($request->all());
					$webcam->usuario_creator_id = Auth::user()->id;
					if (Auth::user()->perfil->nombre == "usuario") {
						$webcam->usuario_id = Auth::user()->usuario_creador_id;
					} else {
						$webcam->usuario_id = Auth::user()->id;
					}

					$webcam->save();
					Session::flash("mensaje", "La Web cam ha sido registrada con éxito.");
					return ["success" => true, "mensaje" => "La Web cam ha sido registrada con éxito.."];
				}else{
					return response(["Error","Usted no tiene permisos para realizar esta tarea."],401);
				}
			}else{
				if($request->input("accion") == "Editar") {
					if(Auth::user()->permitirFuncion("Editar","Web Cam","inicio")) {
						$webcam = webcam::find($request->input("webcam"));
						if($webcam) {
							$webcam->fill($request->all());
							$webcam->save();
							Session::flash("mensaje", "La Web cam ha sido editada con éxito");
							return ["success" => true, "mensaje" => "La Web cam ha sido editada con éxito."];
						}else{
							return response(["Error","La información enviada es incorrecta."],422);
						}
					}else{
						return response(["Error","Usted no tiene permisos para realizar esta tarea."],401);
					}
				}
			}
		}
		return reponse(["error"=>["La información enviada es incorrecta"]],422);
	}

	public function postForm(Request $reques, $id){
		if(Auth::user()->permitirFuncion("Editar","Web Cam","inicio")) {
			$data_webcam = WebCam::where("id",$id)->first();
	 		$ubicaciones = WebCamUbicacion::lists('nombre','id');
			if($data_webcam){
				return view("webcam.form")->with("data_webcam",$data_webcam)->with('ubicaciones',$ubicaciones)->with("accion","Editar");
			}
			return response(["Error","La información enviada es incorrecta."],422);
		}else{
			return response(["Error","Usted no tiene permisos para realizar esta tarea."],401);
		}
	}

	public function postDestroy($id)
	{
		if(Auth::user()->permitirFuncion("Eliminar","Web Cam","inicio")) {
			$webcam = WebCam::where("id",$id)->first();
			if ($webcam && $webcam->exists) {
				
				$webcam->delete();
				Session::flash("mensaje", "La Web cam ha sido eliminada con éxito");
				return ["success" => true];
			} else {
				return response(["error" => "La información es incorrecta"],422);
			}
		}
		return response("Unauthorized",401);
	}


	public function postView(Request $reques, $id){
		if(Auth::user()->permitirFuncion("Ver","Web Cam","inicio")) {
			$data_webcam = WebCam::where("id",$id)->first();
			if($data_webcam){
				return view("webcam.view")->with("data_webcam",$data_webcam);
			}
			return response(["Error","La información enviada es incorrecta."],422);
		}else{
			return response(["Error","Usted no tiene permisos para realizar esta tarea."],401);
		}
	}
}
