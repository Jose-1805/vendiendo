<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		'App\Console\Commands\Inspire',
		'App\Console\Commands\RecordatorioVencimientoPlan',
		'App\Console\Commands\NotificacionVencimientoResolucion',
		'App\Console\Commands\VencimientoCotizaciones',
		'App\Console\Commands\NotificacionVencimientoRemision',
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		$schedule->command('inspire')
				 ->hourly();
		$schedule->command('recordatorio-vencimiento-plan')
			->dailyAt('08:00');
		$schedule->command('notificacion-vencimiento-resolucion')
			->dailyAt('01:00');
		$schedule->command('vencimiento-cotizaciones')
			->dailyAt('01:00');
		$schedule->command('vencimiento-remision')
			->dailyAt('01:00');
	}

}
