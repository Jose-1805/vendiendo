<?php

namespace App\Http\Controllers\Api\Domiciles;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoriasRequest;
use App\Models\Producto;
use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Models\Negocio;
use App\Models\UserDomicilio;
use Validator;

class ListasController extends Controller
{
    public function postCategories(Request $request){
        $api_key = getallheaders()["api-key"];
        $user_position = UserDomicilio::savePosition($api_key,$request->get('latitud'),$request->get('longitud'));
        if ($user_position)
            return response(["data"=>Categoria::listaCategorias()],200);
        else
            return response()->json(['error' => 'Información inválida'], 401);
    }

    public function postBusiness(Request $request){
        $api_key = getallheaders()["api-key"];
        $user_position = UserDomicilio::savePosition($api_key,$request->get('latitud'),$request->get('longitud'));
        if ($request->has('categoria_id')){
            if ($user_position)
                return response(["data"=>Negocio::listaNegocios($api_key,$request->get('categoria_id'))],200);
            else
                return response()->json(['error' => 'Información inválida'], 401);
        }else{
            return response()->json(['error' => 'Información de la categoria inválida'], 401);
        }

    }
    public function postCategoriesBusiness(Request $request){
        $api_key = getallheaders()["api-key"];
        $user_position = UserDomicilio::savePosition($api_key,$request->get('latitud'),$request->get('longitud'));
        if ($request->has('negocio_id')){
            if ($user_position)
                return response(["data"=>Categoria::listaCategoriasByNegocio($request->get('negocio_id'))],200);
            else
                return response()->json(['error' => 'Información inválida'], 401);
        }else{
            return response()->json(['error' => 'Información del negocio inválida'], 401);
        }
    }
    public function postProducts(Request $request){
        $api_key = getallheaders()["api-key"];
        $user_position = UserDomicilio::savePosition($api_key,$request->get('latitud'),$request->get('longitud'));
        if ($request->has('categoria_negocio_id')){
            if ($user_position)
                return response(["data"=>Producto::listaProductosByCategoriaNegocio($request->get('categoria_negocio_id'),$request->get('pagina'))],200);
            else
                return response()->json(['error' => 'Información inválida'], 401);
        }else{
            return response()->json(['error' => 'Información de la categoria inválida'], 401);
        }
    }
}