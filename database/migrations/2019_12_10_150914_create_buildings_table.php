<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buildings', function (Blueprint $table) {
            // building details
            $table->increments('id');
            $table->string('name');
            $table->string('street_address_1')->nullable();
            $table->string('street_address_2')->nullable();
            $table->string('suburb')->nullable();
            $table->integer('postcode')->length(4)->nullable();
            $table->string('state', 3)->nullable();
            $table->string('is_thumb')->nullable();
            // on-site contact details
            $table->string('contact_name')->nullable();
            $table->string('mobile')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('office_hours')->nullable();
            //
            $table->softDeletes();
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
        Schema::dropIfExists('buildings');
    }
}
