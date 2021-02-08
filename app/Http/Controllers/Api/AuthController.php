<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\RequestInicioSesion;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware("authApi",["except"=>["postValid"]]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postValid(Request $request)
    {
        $validator = Validator::make($request->all(),RequestInicioSesion::getRules(),RequestInicioSesion::getMessages());
        if($validator->fails()){
            return response($validator->errors(),200);
        }
        if (Auth::attempt(['email' => $request->input("email"), 'password' => $request->input("password")])) {
            $user = Auth::user();
            if($user->perfil->nombre == "superadministrador"){
                Auth::logout();
                return response(["data"=>["respuesta"=>"Esta aplicación no esta permitida para el rol con el cual desea ingresar."]],200);
            }

            if(!$user->plan()){
                $user->api_key = null;
                $user->save();
                DB::statement("UPDATE planes_usuarios SET estado = 'inactivo' WHERE usuario_id = ".$user->id);
                Auth::logout();
                return response(["data"=>["respuesta"=>"El tiempo de duración de su plan a expirado, para renovar su plan contactese con vendiendo.co."]],200);
            }
            if($user->api_key != ""){
                Auth::logout();
                return response(["data"=>["respuesta"=>"Ya existe una sesión iniciada en otro dispositivo."]],200);
            }
            $api_key = $user->generarApiKey();
            $user->save();
            return response(["data"=>["user-id"=>Crypt::encrypt($user->id),"api-key"=>$api_key]],200);
        }else{
            return response(["Error"=>["Email o password incorrecto -"]],200);
        }

    }

    public function postLogout(Request $request){
        $user = Auth::user();
        $user->api_key = null;
        $user->save();
        Auth::logout();
        return response(["data"=>["success"=>true,"mensaje"=>"La sesión ha sido cerrada con éxito"]],200);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function putUpdateKey(Request $request)
    {
        dd("Update..");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteDestroyKey()
    {
        dd("Delete..");
    }
}