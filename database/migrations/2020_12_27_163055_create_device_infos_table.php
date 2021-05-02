<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeviceInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_infos', function (Blueprint $table) {
            $table->integer('id');
            $table->text('deviceCode')->nullable();
            $table->integer('status')->default(0); // o bật, 1 tắt, 2 đang phát sóng
            $table->integer('volume')->default(5); // 1 - 10
            $table->text('ip')->nullable();
            $table->text('version')->nullable();
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
        Schema::dropIfExists('device_infos');
    }
}
