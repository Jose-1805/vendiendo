<?php namespace App\Console\Commands;

use App\Models\Resolucion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class NotificacionVencimientoResolucion extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'notificacion-vencimiento-resolucion';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Envia notificaciónes por correo para informar sobre la vencimiento de resoluciones a los usuarios.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$this->info("Consultando informacion...");
		$fecha = date("Y-m-d");
		$resoluciones = Resolucion::where("fecha_notificacion",$fecha)->where("estado","activa")->get();
		$this->info($resoluciones->count()." Resultados.");
		if($resoluciones->count()) {
			$this->info("Enviando notificaciones....\n");
			foreach ($resoluciones as $r){
				$usuario = $r->usuario;
				$resolucion_espera = Resolucion::where("usuario_id",$usuario->id)->where("estado","en espera")->orderBy("inicio","ASC")->first();
				if(!$resolucion_espera) {
					Mail::send('emails.vencimiento_resolucion', ["usuario" => $usuario, "resolucion" => $r], function ($m) use ($usuario) {
						$m->from('notificaciones@vendiendo.co', 'Vendiendo.co');
						$m->to($usuario->email, $usuario->nombres . " " . $usuario->apellidos)->subject('Vendiendo.co - vencimiento resolución');
					});
				}
			}
		}
		$this->info("Proceso terminado.");
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			//['example', InputArgument::REQUIRED, 'An example argument.'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}
