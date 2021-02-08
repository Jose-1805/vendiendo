<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTProductos extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('t_productos', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('nombre',100);
			$table->double('precio_costo');
			$table->double('precio_venta');
			$table->integer('stock');
			$table->integer('umbral');
			$table->string('tipo_producto',50);

			$table->integer('usuario_id');
			$table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');

			$table->integer('usuario_id_creator');
			$table->foreign('usuario_id_creator')->references('id')->on('usuarios')->onDelete('cascade');
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
		Schema::table('t_productos', function(Blueprint $table)
		{
			//
		});
	}

}
