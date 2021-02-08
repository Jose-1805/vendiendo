<?php namespace App\Console\Commands;

use App\Models\Cotizacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class VencimientoCotizaciones extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'vencimiento-cotizaciones';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Actualiza estado de cotizaciones vencidas a la fecha actual.';

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
		///$cotizaciones = Cotizacion::where("estado","generada")->whereRaw("ADDDATE(cotizaciones.created_at, INTERVAL cotizaciones.dias_vencimiento DAY) < '".date("Y-m-d H:i:s")."'")->get();
		//dd($cotizaciones->count());
		$this->info("Procesando ...");
		$this->info("Sql: UPDATE cotizaciones SET estado = 'vencida' where estado='generada' AND ADDDATE(cotizaciones.created_at, INTERVAL cotizaciones.dias_vencimiento DAY) < '".date("Y-m-d H:i:s")."'");
		DB::statement("UPDATE cotizaciones SET estado = 'vencida' where estado='generada' AND ADDDATE(cotizaciones.created_at, INTERVAL cotizaciones.dias_vencimiento DAY) < '".date("Y-m-d H:i:s")."'");
		$this->info("Proceso terminado,");
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
