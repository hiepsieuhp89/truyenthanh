<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->increments('id');

            // thong tin chung
            $table->string('name');
            $table->integer('type');
            $table->string('fileVoice');
            $table->integer('priority');

            // khung gio phat
            $table->date('startDate');
            $table->date('endDate');       

            // lich phat
            // che do phat
            $table->integer('mode');
            //phat theo lich, phat ngay

            // khung gio phat
            $table->text('time');
            
            $table->integer('areaId');

            // $table->string('fileVoice')->nullable();
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
        Schema::dropIfExists('programs');
    }
}
