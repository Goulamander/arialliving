<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->string('label')->nullable();
            $table->text('value')->nullable();
            $table->string('group');
            $table->string('sub_group');
            $table->string('type');
            $table->text('choice')->nullable();
            $table->text('replace')->nullable();
            $table->integer('is_thumb')->length(1)->default(0);
            $table->integer('order')->default(0);
            $table->string('class')->nullable();
            $table->string('condition')->nullable();
            $table->boolean('is_config')->default(false);
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
        Schema::dropIfExists('settings');
    }
}
