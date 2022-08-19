<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuildingPageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('building_page', function (Blueprint $table) {
            $table->id();
            $table->integer('building_id')->unsigned();
            $table->text('content');
            $table->timestamps();
        });

        // Relation
        Schema::table('building_page', function(Blueprint $table){
            $table->foreign('building_id')->references('id')->on('buildings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('building_page');
    }
}
