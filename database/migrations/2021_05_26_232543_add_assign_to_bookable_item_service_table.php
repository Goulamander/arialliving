<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssignToBookableItemServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookable_item_service', function (Blueprint $table) {
            $table->increments('id');
            $table->string('timeslot_to')->nullable();
            $table->string('timeslot_from')->nullable();
            $table->integer('assign_to_user_id')->unsigned()->nullable();

            $table->foreign('assign_to_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookable_item_service', function (Blueprint $table) {
            $table->dropColumn('assign_to');
            $table->dropColumn('timeslot_from');
            $table->dropColumn('timeslot_to');
        });
    }
}
