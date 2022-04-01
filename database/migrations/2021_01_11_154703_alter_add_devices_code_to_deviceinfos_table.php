<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddDevicesCodeToDeviceinfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('device_infos', function (Blueprint $table) {
            $table->string('deviceCode')->nullable()->unique()->change(); // ngay phat
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
