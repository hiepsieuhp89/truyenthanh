<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertLastTurnOffTimeToDeviceInfos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('device_infos', function (Blueprint $table) {
            $table->dateTime('turn_off_time')->nullable(); // nguoi tao
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('device_infos', function (Blueprint $table) {
            //
        });
    }
}
