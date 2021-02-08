<?php namespace App\Http\Controllers;

use App\General;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

use Illuminate\Http\Request;

class SessionController extends Controller {

	public function ajaxCheck()
	{
		if(Auth::user() && Auth::user()->estado_sesion == "off") {
			Session::set('logoutWarningDisplayed', true);

			Session::flush(); // remove all the session data

			Auth::logout(); // l
			return "Duplicado";//cuando se inicia sesion desde otro dispositivo o navegador
		}
		/*
         * TODO abstract this logic away to the domain
         */

		// Configuration
		$maxIdleBeforeLogout = Config::get('session.lifetime')*60;//A que minutos se cierra la sesión
		$maxIdleBeforeWarning =(Config::get('session.lifetime')-2)*60;//Dos minutos, para avisar que se va a cerrar la sesión
		$warningTime = $maxIdleBeforeLogout - $maxIdleBeforeWarning;


		// Calculate the number of seconds since the use's last activity
		$idleTime = date('U') - Session::get('lastActive');

		// Warn user they will be logged out if idle for too long
		if ($idleTime > $maxIdleBeforeWarning && empty(Session::get('idleWarningDisplayed'))) {

			Session::set('idleWarningDisplayed', true);

			return 'Tienes ' . $warningTime . ' segundos antes de ser retirado del sistema';
		}

		// Log out user if idle for too long
		if (($idleTime > $maxIdleBeforeLogout && empty(Session::get('logoutWarningDisplayed')))) {

           /* */
			// *** Do stuff to log out user here

			Session::set('logoutWarningDisplayed', true);

			Session::flush(); // remove all the session data

            $user = Auth::user();
            if($user){
                $user->estado_sesion = "off";
                $user->save();
            }

			Auth::logout(); // logout user

			return 'Retirado';
		}
		return '';
	}

	public function resetConteo(){
		Session::set('lastActive', date('U'));
		Session::forget('idleWarningDisplayed');
		Session::forget('logoutWarningDisplayed');
	}
	public function ajaxEstadoDemo(){

		$user = Auth::user();
		$fecha_vencimiento = "";
		$minutos = 0;
		if($user){
			$fecha_vencimiento = $user->fecha_primer_logueo;
			$date_time_actual = date('Y-m-d H:i:s');
			$minutos = General::calcula_minutos($date_time_actual,$fecha_vencimiento);
		}

		return $minutos;
	}

}
