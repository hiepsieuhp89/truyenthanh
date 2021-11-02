<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTurnOnTimeToDeviceinfos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('device_infos', function (Blueprint $table) {
            $table->dateTime('turn_on_time')->nullable()->before('turn_off_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deviceinfos', function (Blueprint $table) {
            //
        });
    }
}
