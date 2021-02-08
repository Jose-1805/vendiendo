<?php namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class Authenticate {

	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard  $auth
	 * @return void
	 */
	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if ($this->auth->guest() || (Auth::user() && Auth::user()->estado_sesion == "off"))
		{
			if(Auth::user() && Auth::user()->estado_sesion == "off") {
				Session::flush();
				Auth::logout();
			}

			if ($request->ajax())
			{
				return response('Unauthorized.', 401);
			}
			else
			{
				return redirect()->guest('auth/login');
			}
		}

		if(!Auth::user()->plan() && (Auth::user()->bodegas == 'no' || (Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si'))){
			if(Auth::check()){
				$user = Auth::user();
				$user->estado_sesion = "off";
				$user->save();
				$admin = User::find($user->userAdminId());

				//se debe enviar el correo a soporte para contactar al usuario
				if($admin->notificacion_renovar_plan == "no"){//no se ha enviado
					$admin->notificacion_renovar_plan = "si";
					$admin->save();
					Mail::send('emails.notificacion_renovar_plan', ["admin"=>$admin], function ($m) {
						$m->from('notificaciones@vendiendo.co', 'Vendiendo.co');

						$m->to("servicio-cliente@vendiendo.co")->subject('Renovación de plan');
					});
				}
				DB::statement("UPDATE planes_usuarios SET estado = 'inactivo' WHERE usuario_id = ".$user->id);
				Auth::logout();
			}
			if ($request->ajax())
			{
				return response('Unauthorized.', 401);
			}
			else
			{
				Session::flash("mensaje","El tiempo de duración de su plan a expirado, para renovar su plan contactese con <a href='http://vendiendo.co/#contacto' target='_blank'>vendiendo.co</a> ");
				return redirect()->guest('auth/login');
			}
		}

        $base_controller = explode('/', $request->path())[0];

        $admin = User::find(Auth::user()->userAdminId());
        if($admin->cambio_bodegas_almacenes == 'no' && $base_controller == 'migracion-ab'){
            if ($request->ajax())
            {
                return response('Unauthorized.', 401);
            }
            else
            {
                return redirect('/');
            }
        }

		$configuracion_completa = Auth::user()->configuracionCambioAB();

        $comentar = false;
        if(!$configuracion_completa && !$comentar){

            if($base_controller != 'migracion-ab') {
                if (Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si') {
                    $excepciones = ['bodega','almacen'];
                    if(in_array($base_controller,$excepciones))
                        return $next($request);
                }
            }else{

                if($admin->migracion_ab_completa == 'no') {
                    if (count(explode('/', $request->path())) > 1) {
                        if (Auth::user()->admin_bodegas == 'si')
                            return $next($request);
                    } else {
                        return $next($request);
                    }
                }
            }

            if ($request->ajax())
            {
                return response('Unauthorized.', 401);
            }
            else
            {
                return redirect('/migracion-ab');
            }
        }

		return $next($request);
	}

}
