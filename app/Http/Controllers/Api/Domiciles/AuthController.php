<?php

namespace App\Http\Controllers\Api\Domiciles;

use App\Models\UserDomicilio;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\RegisterDomicilioRequest;
use App\Http\Requests\LoginDomicilioRequestest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Validator;
use App\User;


class AuthController extends Controller
{
    public function __construct()
    {
        //$this->middleware("authApi",["except"=>["postValid"]]);
    }
    public function postRegister(Request $request){
        $validacion = Validator::make($request->all(),RegisterDomicilioRequest::getRules(),RegisterDomicilioRequest::getMessages());
        if ($validacion->fails()){
            return response($validacion->errors(),200);
        }
        $usuario = new UserDomicilio();
        DB::beginTransaction();
        $usuario->nombre = $request->get('nombre');
        $usuario->identificacion = $request->get('identificacion');
        $usuario->telefono = $request->get('telefono');
        $usuario->correo = $request->get('correo');
        $usuario->direccion = $request->get('direccion');

        $user = new User();
        $clave = $user->generarClave();

        $usuario->password = $clave;
        $usuario->save();

        $data = ["usuario"=>$usuario,"clave"=>$clave];

        Mail::send('emails.nueva_cuenta_domicilio', $data, function ($m) use ($usuario) {
            $m->from('notificaciones@vendiendo.co', 'Vendiendo.co');
            $m->to($usuario->correo, $usuario->nombre)->subject('Vendiendo.co - cuenta de usuario');
        });
        DB::commit();
        if (!$usuario)
            return response(["data"=>['Ocurrio un error, vuelve a intentarlo mas tarde']],200);

        return response(["data"=>["response"=>"Registro satisfactorio, se le acaba de enviar un correo. Abra el mensaje y siga las instrucciones para activar la cuenta. Si no encuentra el mensaje en la bandeja de entrada de su correo electrónico, búsquelo en la bandeja de 'no deseados'."]],200);
    }

    public function postActivate(Request $request)
    {
        $validacion = Validator::make($request->all(),LoginDomicilioRequestest::getRules(),LoginDomicilioRequestest::getMessages());
        if ($validacion->fails()){
            return response($validacion->errors(),200);
        }
        $user = new User();
        $usuario = UserDomicilio::select("*")
            ->where('correo',$request->get('correo'))
            ->where('password',$request->get('password'))
            ->first();
        if ($usuario){
            $api_key = $user->generarApiKey();
            $usuario->api_key = $api_key;
            $usuario->longitud = $request->get('longitud');
            $usuario->latitud = $request->get('latitud');
            $usuario->save();
            return response(["data"=>["user-id"=>Crypt::encrypt($usuario->id),"api-key"=>$api_key]],200);
        }else{
            return response(["data"=>['Los datos ingresados no coinciden']],200);
        }
    }  
}