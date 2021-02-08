<?php namespace App\Http\Controllers\Auth;

use App\General;
use App\Http\Controllers\Controller;
use App\Http\Requests\RequestInicioSesion;
use App\Models\Factura;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Registration & Login Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles the registration of new users, as well as the
	| authentication of existing users. By default, this controller uses
	| a simple trait to add these behaviors. Why don't you explore it?
	|
	*/

	use AuthenticatesAndRegistersUsers;

	/**
	 * Create a new authentication controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
	 * @return void
	 */
	public function __construct(Guard $auth, Registrar $registrar)
	{
		$this->auth = $auth;
		$this->registrar = $registrar;

		$this->middleware('guest', ['except' => ['getLogout','postReiniciarApiKey']]);

    }


	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function getLogin()
	{
        //dd(Auth::check());
		return view('auth/login');
	}

	public function getLogout(){
		$user = Auth::user();
		if($user){
			$user->estado_sesion = "off";
			$user->save();
		}
		Auth::logout();
		return redirect("/home");
	}

	public function postAuthenticate(RequestInicioSesion $request)
	{
        /*
        $login = true;
        $connect_user = new User;
        $connect_user->setConnection('mysql_bodegas');
        $user = $connect_user->where("email",$request->input("email"))->first();

        if(!$user) {
            $connect_user->setConnection('mysql');
            $user = $connect_user->where("email",$request->input("email"))->first();
        }

        if($user){
            if(Hash::check($request->input("password"),$user->password))$login = true;
        }
        */

		if (Auth::attempt(['email' => $request->input("email"), 'password' => $request->input("password")]))
        //if($user && $login)
		{

            //Auth::login($user);
			$user = Auth::user();
			if($user){
				if($user->estado_sesion == "off") {
					if (config('options.version_proyecto') == 'DEMO') {
						$date_time_actual = date('Y-m-d H:i:s');
						$nuevafecha = strtotime('+' . config('options.numero_dias_demo') . ' day', strtotime($date_time_actual));
						$nuevafecha = date('Y-m-d H:i:s', $nuevafecha);
						if ($user->fecha_primer_logueo == null || $user->fecha_primer_logueo == '') {
							//inicia sesio por primera vez
							$user->fecha_primer_logueo = $nuevafecha;
							$user->estado_sesion = "on";
							$user->save();
							$mensaje = "Su cuenta se ha activado, tienes " . config('options.numero_dias_demo') . " dias para disfrutar de la plataforma Vendiendo.com";
							Session::flash("mensaje_validacion_session_demo", $mensaje);
							return ["success" => true];
						} else {
							//se valida el tiempo del demo
							if (General::calcula_minutos($date_time_actual, $user->fecha_primer_logueo) > 0) {
								//demo activo
								$user->estado_sesion = "on";
								$user->save();
								return ["success" => true];
							} else {
								//demo inactivo
								$user->estado_sesion = "off";
								$user->save();
								Auth::logout();
								Session::flush();
								$vinculo = "<a href='http://vendiendo.co' target='_blank'>Vendiendo.co</a>";
								return ["success" => false, "error" => "Su cuenta se encuentra desactivada, comuniquese con el administrador o visitanos en " . $vinculo];
							}
						}
					}else {
						$user->estado_sesion = "on";
						$user->save();
						return ["success" => true];
					}
				}else{
					$user->estado_sesion = "off";
					$user->save();
					Auth::logout();
					Session::flush();
					return ["success" => false,"error"=>"Los datos ingresados corresponden a un usuario que actualmente tiene una sesión activa, por la seguridad de su información cerraremos las sesiones activas en otros dispositivos."];
				}
			}

			$facturaAbierta = Factura::permitidos()->where("estado","abierta")->where("created_at","<",date("Y-m-d"))->get();
			foreach ($facturaAbierta as $f){
				$f->estado = "cerrada";
				$f->save();
			}
			return ["success"=>true];
		}else{
			return ["success" => false];
		}
	}
    /*public function getReestablecerPassword(){
	public function postReiniciarApiKey(){
		$user = Auth::user();
		$user->api_key = "";
		$user->save();
		return ["success"=>true];
	}
	/*public function getReestablecerPassword(){
        return view('reestablecerPassword');
    }*/

	/*public function postReestablecerPassword(Request $request){
        if($request->has("email")) {
            $request->input("email");
            Auth::user()->res
            return ["success"=>true];
        }
        return ["success"=>false];
    }*/
}
