<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCuentaPorCobrarsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cuentas_por_cobrar', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('compra_id');
			$table->integer('elemento_id');
			$table->integer('cantidad_devolucion');
			$table->integer('valor_devolucion');
			$table->string('motivo',255);
			$table->string('tipo_compra',255);
			$table->string('estado');
			$table->date('fecha_devolucion');
			$table->integer('usuario_id');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cuentas_por_cobrar');
	}

}
