<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreatorToProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->integer('creatorId'); // nguoi tao
            $table->integer('approvedId'); // nguoi phe duyet
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
        });
    }
}
