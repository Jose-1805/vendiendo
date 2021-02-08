<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Consignacion;
use App\Models\CuentaBancaria;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\ProductoHistorial;
use App\Models\Resolucion;
use App\Models\TokenPuntos;
use App\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class FacturaController extends Controller
{

    public function postFacturar(Request $request){
        //dd($request->all());
        $caja = Auth::user()->cajaAsignada();
        if(Auth::user()->permitirFuncion("Crear","facturas","inicio") && $caja) {

            $resolucion = Resolucion::getActiva();

            $errores = [];

            //si no hay resolución activa se busca la resolución en espera con menor numero de inicio y se activa (si existe)
            if(!$resolucion){
                $r = Resolucion::permitidos()->where("estado","en espera")->orderBy("inicio","ASC")->first();
                if($r) {
                    $r->estado = "activa";
                    $r->save();
                    $resolucion = $r;
                }
            }


            $resolucion_espera = Resolucion::permitidos()->where("estado","en espera")->orderBy("inicio","ASC")->first();
            if($resolucion) {

                DB::beginTransaction();
                if($request->has('facturas') && is_array($request->input('facturas'))) {
                    $facturas = $request->input('facturas');
                    //INICIAR EL CICLO DE GESTION DE FACTURAS
                    foreach ($facturas as $f) {
                        if(!array_key_exists('factura_id',$f))response(["data" => ["success"=>false,"errores"=>[0=>["La información enviada es incorrecta."]]]], 200);
                        $factura_id = $f['factura_id'];
                        if (array_key_exists("id_cliente",$f)) {
                            $cliente = Cliente::permitidos()->where("id", $f["id_cliente"])->first();

                            if ($cliente) {

                                $ultimaFact = Factura::ultimaFactura();
                                $numero = 1;
                                if ($ultimaFact) {
                                    $numero = intval($ultimaFact->numero) + 1;
                                } else {
                                    $numero = intval($resolucion->inicio);
                                }
                                if ($resolucion->inicio <= $numero && $resolucion->fin >= $numero) {

                                    if (!Factura::permitidos()->where("numero", "00" . $numero)->first()) {

                                        //se desactiva la anterior resolución y se activa la que esta en espera si existe y tiene el numero de inicio igual al consucutivo que se genera para la actual factura
                                        if ($resolucion_espera && $resolucion_espera->inicio == $numero) {
                                            $resolucion->estado = "terminada";
                                            $resolucion->save();
                                            $resolucion_espera->estado = "activa";
                                            $resolucion_espera->save();
                                            $resolucion = $resolucion_espera;
                                        }
                                        $relacion_caja_usuario = Auth::user()->cajas()->select("cajas_usuarios.id")->where("cajas_usuarios.caja_id", $caja->id)
                                            ->where("cajas_usuarios.estado", "activo")->first();
                                        $continuar = true;
                                        $factura = new Factura();
                                        $factura->numero = "00" . $numero;
                                        $factura->estado = "Pagada";
                                        $factura->cliente_id = $cliente->id;
                                        $factura->usuario_creador_id = Auth::user()->id;
                                        $factura->caja_usuario_id = $relacion_caja_usuario->id;
                                        $factura->desde_app = 'si';
                                        if (Auth::user()->perfil->nombre == "administrador")
                                            $factura->usuario_id = Auth::user()->id;
                                        else
                                            $factura->usuario_id = Auth::user()->usuario_creador_id;
                                        $factura->resolucion_id = $resolucion->id;
                                        $factura->subtotal = 0;
                                        $factura->iva = 0;
                                        $factura->observaciones = $request->input("observaciones");
                                        $factura->save();

                                        //si el numero de la factura creada es igual al numero de notificacion de la resolución se envia notificación por correo
                                        if (intval($factura->numero) == $resolucion->numero_notificacion) {
                                            $usuario = User::find(Auth::user()->userAdminid());
                                            $datos = [
                                                "usuario" => $usuario,
                                                "resolucion" => $resolucion
                                            ];

                                            $resolucion_espera = Resolucion::where("usuario_id", $usuario->id)->where("estado", "en espera")->orderBy("inicio", "ASC")->first();
                                            if (!$resolucion_espera) {
                                                Mail::send('emails.vencimiento_resolucion', $datos, function ($m) use ($usuario) {
                                                    $m->from('notificaciones@vendiendo.co', 'Vendiendo.co');

                                                    $m->to($usuario->email, $usuario->nombres . " " . $usuario->apellidos)->subject('Vendiendo.co - vencimiento resolución');
                                                });
                                            }
                                        }

                                        //si es la ultima factura se termina la resolucion
                                        if ($numero == intval($resolucion->fin)) {
                                            $resolucion->estado = "terminada";
                                            $resolucion->save();
                                            //se busca si existe una resolucion en espera que tenga continue el consecutivo
                                            $resEnEspera = Resolucion::resolucionEnEspera($numero + 1);
                                            if ($resEnEspera) {
                                                $resEnEspera->estado = "activa";
                                                $resEnEspera->save();
                                            }
                                        }

                                        $aux = 1;
                                        while ($continuar) {
                                            if (array_key_exists("producto_" . $aux,$f)) {
                                                $pr = $f["producto_" . $aux];
                                                if ($pr["cantidad"] >= 0.1) {
                                                    $producto = Producto::permitidos()->where("id", $pr["producto"])->first();
                                                    if ($producto) {
                                                        $maxima_cantidad = $producto->stock;
                                                        //validar medidas unitarias y fraccionales
                                                        if (!($producto->medida_venta == "Unitaria" && $pr["cantidad"] % 1 != 0)) {

                                                            if ($pr["cantidad"] <= $maxima_cantidad) {
                                                                $historial = ProductoHistorial::find($pr['producto_historial_id']);
                                                                $factura->subtotal += ($historial->precio_costo_nuevo + (($historial->precio_costo_nuevo * $historial->utilidad_nueva) / 100)) * $pr["cantidad"];
                                                                $factura->iva += ((($historial->precio_costo_nuevo + (($historial->precio_costo_nuevo * $historial->utilidad_nueva) / 100)) * $historial->iva_nuevo) / 100) * $pr["cantidad"];

                                                                $factura->productosHistorial()->save($historial, ["cantidad" => $pr["cantidad"]]);
                                                                $producto_aux = Producto::find($pr["producto"]);
                                                                $producto_aux->stock = $producto->stock - $pr["cantidad"];
                                                                $producto_aux->save();

                                                            } else {
                                                                $errores[$factura_id][] = "La cantidad máxima permitida para el producto " . $producto->producto_nombre . " es " . $maxima_cantidad;
                                                            }
                                                        } else {
                                                            $errores[$factura_id][] = "El producto " . $producto->producto_nombre . " no acepta cantidades de ventas fraccionales, por favor ingrese un numero entero en el campo cantidad.";
                                                        }
                                                    } else {
                                                        $errores[$factura_id][] = "La información enviada es incorrecta.";
                                                    }
                                                } else {
                                                    $errores[$factura_id][] = "La cantidad de un producto debe ser mayor o igual a 1.";
                                                }
                                            } else {
                                                if ($aux == 1) {
                                                    $errores[$factura_id][] = "No se ha seleccionado ningún producto.";
                                                } else {
                                                    $continuar = false;
                                                }
                                            }
                                            $aux++;
                                        }
                                        $relacion = $caja->usuarios()->select("cajas_usuarios.*")->where("cajas_usuarios.estado", "activo")->first();

                                        $relacion->valor_final += ($factura->subtotal + $factura->iva);
                                        //SE INGRESA EL NUMERO DE PUNTOS A LA CUENTA DEL CLIENTE
                                        $valor_factura = ($factura->subtotal + $factura->iva);
                                        Cliente::savePoins($valor_factura, $cliente->id);

                                        if (array_key_exists("efectivo",$f)) {
                                            if (intval($f["efectivo"]) < intval(($factura->subtotal + $factura->iva))) {
                                                if (Auth::user()->permitirFuncion("Descuentos", "facturas", "inicio")) {
                                                    $factura->descuento = (($factura->subtotal + $factura->iva) - $request->input("efectivo"));
                                                    $relacion->valor_final -= $factura->descuento;
                                                } else {
                                                    $errores[$factura_id][] = "El valor ingresado en el campo efectivo debe ser mayor o igual al valor total de la factura.";
                                                }
                                            }
                                        }
                                        $sql = "UPDATE cajas_usuarios SET valor_final = " . $relacion->valor_final . " where id = " . $relacion->id;
                                        DB::statement($sql);


                                        $factura->save();

                                    } else {
                                        $errores[$factura_id][] = "A ocurrido un error generando el número de la factura, por favor recargue su página y diligencie nuevamente la factura.";
                                    }
                                } else {
                                    $errores[$factura_id][] = "A ocurrido un error generando el número de la factura, por favor verifique que su resolución actual no haya expirado.";
                                }
                            } else {
                                $errores[$factura_id][] = "El cliente seleccionado no existe en el sistema.";
                            }
                        } else {
                            $errores[$factura_id][] = "Seleccione un cliente.";
                        }
                    }
                    //FINALIZACION DEL CICLO DE GESTION DE FACTURAS

                    if(count($errores)){
                        return response(['data'=>['success'=>false,'errores'=>$errores]],200);
                    }else {
                        //DB::commit();
                        return response(['data'=>['success'=>true,'mensaje'=>'Sincronización realizada con éxito.']],200);
                    }
                }else{
                    return response(["data"=>["success"=>false,"errores"=>[0=>["La información enviada es incorrecta."]]]],401);
                }
            }else{
                response(["data" => ["success"=>false,"errores"=>[0=>["No existe ninguna resolución activa relacionada con su usuario."]]]], 200);
            }
        }
        return response(["data"=>["success"=>false,"errores"=>[0=>["Usted no tiene permisos para realizar esta tarea."]]]],401);
    }

}