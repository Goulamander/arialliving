<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserBuildingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('building_user', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('building_id')->unsigned();
            $table->string('unit_no')->nullable();
            $table->integer('unit_type')->nullable();
            // ??
            $table->date('relation_start')->nullable();
            $table->date('relation_end')->nullable();
            $table->boolean('relation_status')->default(1); // Active or other
            $table->boolean('relation_type')->default(1); // Residency, Management, General Staff
            $table->text('notes')->nullable();
            $table->index(['relation_status']);
            $table->timestamps();
        });
        Schema::table('building_user', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('building_user');
    }
}
