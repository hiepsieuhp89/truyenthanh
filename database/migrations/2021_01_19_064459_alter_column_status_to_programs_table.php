<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnStatusToProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->integer('status')->default(1); // 1 - tao chua duyet, 2 duoc phe duyet
            $table->text('digiChannel')->change();
            $table->text('radioChannel')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
            //
        });
    }
}
