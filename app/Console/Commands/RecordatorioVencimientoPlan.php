<?php namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RecordatorioVencimientoPlan extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'recordatorio-vencimiento-plan';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Recordatorio de la fecha de vencimiento de un plan (Vía email).';

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
		$fecha = date("Y-m-d",strtotime("+5days"));
		$usuarios = User::select("usuarios.*","planes_usuarios.hasta")
			->join("planes_usuarios","usuarios.id","=","planes_usuarios.usuario_id")
			->where("planes_usuarios.estado","activo")
			->where(function($q) use ($fecha){
				$q->whereBetween("planes_usuarios.hasta",[$fecha." 00:00:00",$fecha." 23:59:59"])
				->orWhere(function($qu) {
					$qu->whereBetween("planes_usuarios.hasta",[date("Y-m-d")." 00:00:00",date("Y-m-d")." 23:59:59"]);
				});
			})
			->whereBetween("planes_usuarios.hasta",[date("Y-m-d"." 00:00:00"),$fecha." 23:59:59"])
			->groupBy("usuarios.id")
			->get();
		$this->info($usuarios->count()." Resultados.");
		if($usuarios->count()) {
			$this->info("Enviando notificaciones....\n");
			foreach ($usuarios as $usuario){
				//$this->info("Enviando a: ".$usuario->email);
				Mail::send('emails.recordatorio_vencimiento_plan', ["usuario"=>$usuario], function ($m) use ($usuario) {
					$m->from('notificaciones@vendiendo.co', 'Vendiendo.co');
					$m->to($usuario->email, $usuario->nombres . " " . $usuario->apellidos)->subject('Vendiendo.co - recordatorio vencimiento cuenta');
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
