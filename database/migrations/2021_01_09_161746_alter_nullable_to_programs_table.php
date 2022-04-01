<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterNullableToProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->integer('priority')->default(1)->change();
            $table->date('startDate')->nullable()->change();
            $table->date('endDate')->nullable()->change();  
            $table->text('time')->nullable()->change();  
            $table->text('days')->nullable()->change();  
            $table->integer('mode')->nullable()->change(); 
            $table->integer('inteval')->nullable()->change(); 
            $table->integer('creatorId')->nullable()->change(); // nguoi tao
            $table->integer('approvedId')->nullable()->change(); // nguoi phe duyet
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
