<?php namespace App\Http\Controllers;

use App\Event;
use App\General;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\CostoFijo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventsController extends Controller {

    public function __construct()
    {
        $this->middleware("modEventos");
        $this->middleware("auth");
    }

    public function getIndex(){
        if(!Auth::user()->permitirFuncion("Ver","Eventos","inicio"))
            return redirect('/');

        return view('calendar');
    }
    public function listCostosFijos(){
        $costosFijos = CostoFijo::permitidos()->where('estado','habilitado')->get();
        $eventos = array();
        foreach ($costosFijos as $key => $costosFijo){
            foreach ($costosFijo->pagosCostosFijo()->orderby('fecha','DESC')->get() as $keyCF => $pagoCF){
                if ($keyCF == 0){
                    $eventos[count($eventos)] = Array(
                        "title"         => $costosFijo->nombre,
                        "start"         => General::sumarMesFecha($pagoCF->fecha, config('options.tiempo_programacion_costo_fijo'), "Date"),
                        "end"           => General::sumarMesFecha($pagoCF->fecha, config('options.tiempo_programacion_costo_fijo'), "Date"),
                        "hourI"         => "00:00",
                        "hourF"         => "00:00",
                        "color"         => "red",
                        "description"   => "Debes realizar el pago para evitar los recargos por mora de este costo fijo",
                        "url"           => "",
                        "id"            => $costosFijo->id,
                        "evento_name"   => "costo_fijo",
                        "editable"      => true,
                        "estado_pago"   => "Pendiente",
                    );
                }
                $eventos[count($eventos)] = Array(
                    "title"         => $costosFijo->nombre,
                    "start"         => General::sumarDiasFecha($pagoCF->fecha, 0,"Date"),
                    "end"           => General::sumarDiasFecha($pagoCF->fecha, 1, "Date"),
                    "hourI"         => "00:00",
                    "hourF"         => "00:00",
                    "color"         => "green",
                    "description"   => "Se pago el valor de $".number_format($pagoCF->valor,2,',','.'),
                    "url"           => "",
                    "id"            => $costosFijo->id,
                    "evento_name"   => "costo_fijo",
                    "editable"      => false,
                    "estado_pago"   => "Pagado",
                );
            }
        }
        return response()->json($eventos);
    }

    public function listEvents()
    {
        $data = array();
        $id = Event::permitidos()->lists('id');
        $lugar = Event::permitidos()->lists('titulo');
        $inicio = Event::permitidos()->lists('inicio');
        $fin = Event::permitidos()->lists('fin');
        $descripcion = Event::permitidos()->lists('descripcion');
        $color = Event::permitidos()->lists('color');
        $count = count($id);

        for($i=0; $i<$count; $i++){
            $fechaI = General::getHourOrDate($inicio[$i],"Date");
            $fechaF = General::getHourOrDate($fin[$i],"Date");
            $dias = General::dias_transcurridos($inicio[$i],$fin[$i]);
            if ($dias <= 1){
                $fechaI = $inicio[$i];
                $fechaF = $fin[$i];
            }

            $data[$i] = array(
                "title"         => $lugar[$i],
                "start"         => $inicio[$i],
                "end"           => $fin[$i],
                "hourI"         => General::getHourOrDate($inicio[$i],"Hour"),
                "hourF"         => General::getHourOrDate($fin[$i],"Hour"),
                "color"         => $color[$i],
                "description"   => $descripcion[$i],
                "url"           => "http://www.efcastillo.fireosapp:8787/evento/update-resize/".$id[$i],
                "id"            => $id[$i],
                "dias"          =>$dias,
            );
        }
        return response()->json($data);

    }
    public function postStore(Requests\EventoRequest $request){
        if(Auth::user()->permitirFuncion("Gestionar","Eventos","inicio")) {
            $fin = date('Y-m-d H:i:s', strtotime('-1days', strtotime($request->input('fin'))));

            //si el evento dura más de un dia se establece si se agrega un evento por día un evento continuo
            $fin_aux = date('Y-m-d', strtotime($fin));
            $inicio_aux = date('Y-m-d', strtotime($request->input('inicio')));
            if ($inicio_aux != $fin_aux) {
                if ($request->has('eventos_diarios')) {
                    $dias = (strtotime($inicio_aux) - strtotime($fin_aux)) / 86400;
                    $dias = abs($dias);
                    $dias = floor($dias);
                    $dias_aux = $dias + 1;
                    for ($i = 0; $i <= $dias; $i++) {
                        $fin_ = date('Y-m-d H:i:s', strtotime('-' . $dias_aux . 'days', strtotime($fin)));
                        $dias_aux--;
                        $inicio_ = date('Y-m-d H:i:s', strtotime('+' . $i . 'days', strtotime($request->input('inicio'))));
                        $evento = new Event();
                        $evento->fill($request->all());
                        $evento->fin = $fin_;
                        $evento->inicio = $inicio_;
                        $evento->usuario_creador_id = Auth::user()->id;
                        $evento->usuario_id = Auth::user()->userAdminId();
                        $evento->save();
                    }
                } else {
                    $evento = new Event();
                    $evento->fill($request->all());
                    $evento->fin = $fin;
                    $evento->usuario_creador_id = Auth::user()->id;
                    $evento->usuario_id = Auth::user()->userAdminId();
                    $evento->save();
                }
            } else {
                //dd($request->all());
                $evento = new Event();
                $evento->fill($request->all());
                $evento->fin = $fin;
                $evento->usuario_creador_id = Auth::user()->id;
                $evento->usuario_id = Auth::user()->userAdminId();
                $evento->save();
            }
            return "ok";
        }else{
            return response(['error'=>['Unauthorized.']],401);
        }
    }
    public function postUpdate(Requests\EventoRequest $request){
        if(Auth::user()->permitirFuncion("Gestionar","Eventos","inicio")){
            $evento = Event::permitidos()->where('id',$request->input('id'))->first();

            /*dd($request->all());

            if (General::getHourOrDate($evento->fin,'Date') != General::getHourOrDate($fin, 'Date')){
                $fecha_nuevaF = General::getHourOrDate($fin,'Date')." ".$horaF;
            }else{
                $horaF = General::getHourOrDate($fin,'Hour');
                $fecha_nuevaF = General::getHourOrDate($fin,'Date')." ".$horaF;
            }*/


            $evento->update([
                'titulo' => $request->get('titulo'),
                'descripcion' => $request->get('descripcion'),
                'color' => $request->get('color'),
            ]);

            if ($evento){
                return "ok";
            }
        }else{
            return response(['error'=>['Unauthorized.']],401);
        }
    }
    public function getUpdateResize($id_evento,$fin){
        if(Auth::user()->permitirFuncion("Gestionar","Eventos","inicio")) {
            $evento = Event::where('id', $id_evento)->first();
            $horaF = General::getHourOrDate($evento->fin, 'Hour');
            if (General::getHourOrDate($evento->fin, 'Date') != General::getHourOrDate($fin, 'Date')) {
                $fecha_nuevaF = General::getHourOrDate($fin, 'Date') . " " . $horaF;
            } else {
                $horaF = General::getHourOrDate($fin, 'Hour');
                $fecha_nuevaF = General::getHourOrDate($fin, 'Date') . " " . $horaF;
            }
            $evento->update([
                'fin' => $fecha_nuevaF,
            ]);
            if ($evento) {
                return "ok";
            }
        }else{
            return response(['error'=>['Unauthorized.']],401);
        }
    }
    public function getDelete($evento_id){
        if(Auth::user()->permitirFuncion("Gestionar","Eventos","inicio")) {
            $evento = Event::where('id', $evento_id)->first();
            $evento->delete();
            if ($evento) {
                return "ok";
            }
        }else{
            return response(['error'=>['Unauthorized.']],401);
        }

    }

    public function postNotificaciones(){
        $fecha = date('Y-m-d H:i:s');
        $eventos = Event::permitidos()->select('eventos.*')
            ->leftJoin('notificaciones_eventos_usuarios','eventos.id','=','notificaciones_eventos_usuarios.evento_id')
            ->where(function ($q){
                $q->whereNull('notificaciones_eventos_usuarios.id')
                    ->orWhere('notificaciones_eventos_usuarios.usuario_id','<>',Auth::user()->id);
            })
            ->where('inicio','<=',$fecha)->where('fin','>=',$fecha)->get();

        foreach ($eventos as $ev){
            $ev->notificacionesUsuarios()->save(Auth::user());
        }
        return ['registros'=>count($eventos),'eventos'=>$eventos];
    }

    public function postMover(Request $request)
    {
        if(Auth::user()->permitirFuncion("Gestionar","Eventos","inicio")) {
            $evento = Event::permitidos()->where('id', $request->input('id'))->first();
            if ($evento) {
                $dato = intval($request->input('dato'));

                switch ($request->input('tipo_dato')) {
                    case 'dias':
                        $evento->fin = date('Y-m-d H:i:s', strtotime($dato . ' days', strtotime($evento->fin)));
                        $evento->inicio = date('Y-m-d H:i:s', strtotime($dato . ' days', strtotime($evento->inicio)));
                        break;
                    case 'horas':
                        $evento->fin = date('Y-m-d H:i:s', strtotime($dato . ' hours', strtotime($evento->fin)));
                        if (!$request->has('solo_fin'))
                            $evento->inicio = date('Y-m-d H:i:s', strtotime($dato . ' hours', strtotime($evento->inicio)));
                        break;
                    case 'minutos':
                        $evento->fin = date('Y-m-d H:i:s', strtotime($dato . ' minutes', strtotime($evento->fin)));
                        if (!$request->has('solo_fin'))
                            $evento->inicio = date('Y-m-d H:i:s', strtotime($dato . ' minutes', strtotime($evento->inicio)));
                        break;
                }
                $evento->save();
                return ['success' => true];
            }

            return response(['error' => ['La información enviada es incorrecta']], 422);
        }else{
            return response(['error'=>['Unauthorized.']],401);
        }
    }

}
