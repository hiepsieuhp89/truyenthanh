<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('deviceCode')->nullable();
            $table->integer('type');
            $table->string('fileVoice');
            $table->integer('priority')->default(1);
            $table->date('startDate')->nullable();
            $table->date('endDate')->nullable();
            $table->text('time')->nullable();
            $table->text('days')->nullable();  
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
        Schema::dropIfExists('schedules');
    }
}
