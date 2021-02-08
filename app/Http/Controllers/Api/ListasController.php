<?php

namespace App\Http\Controllers\Api;

use App\Models\Cliente;
use App\Models\Unidad;
use App\Models\Categoria;

use App\Http\Controllers\Controller;

class ListasController extends Controller
{

    public function postCategorias(){

        return response(["data"=>Categoria::lista_api()],200);
    }

    public function postUnidades(){
        return response(["data"=>Unidad::lista_api()],200);
    }

    public function postClientes(){
        return response(["data" => Cliente::permitidos()->get()],200);
    }
}