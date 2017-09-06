<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCalculationWeeksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calculation_weeks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('calculation_id')->unsigned();
            $table->smallInteger('year')->unsigned();
            $table->tinyInteger('week')->unsigned();
            $table->float('rate')->unsigned();
            $table->float('rate_max')->unsigned();
            $table->float('rate_min')->unsigned();
            $table->decimal('amount', 10, 2)->unsigned();
            $table->decimal('profit', 10, 2)->unsigned();
            $table->boolean('complete');
            $table->date('last_day');

            $table->foreign('calculation_id')
                ->references('id')->on('currency_calculations')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calculation_weeks');
    }
}
