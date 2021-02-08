<?php namespace App\Console\Commands;

use App\Models\Remision;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class NotificacionVencimientoRemision extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'vencimiento-remision';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Notifica a los administradores, vía email, sobre el vencimiento de sus remisiones.';

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
		$remisiones = Remision::where("fecha_vencimiento",$fecha)->where("estado","registrada")->get();
		$this->info($remisiones->count()." Resultados.");
		if($remisiones->count()) {
			$this->info("Enviando notificaciones....\n");
			foreach ($remisiones as $r){
				$usuario = $r->usuario;
				Mail::send('emails.vencimiento_remision', ["usuario" => $usuario, "remision" => $r], function ($m) use ($usuario) {
					$m->from('notificaciones@vendiendo.co', 'Vendiendo.co');
					$m->to($usuario->email, $usuario->nombres . " " . $usuario->apellidos)->subject('Vendiendo.co - vencimiento remisión');
				});
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
