<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMaximumNumberOfBookingsPerDayToBookableItemRoomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookable_item_room', function (Blueprint $table) {
            $table->integer('maximum_number_of_bookings_per_day')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookable_item_room', function (Blueprint $table) {
            $table->dropColumn('maximum_number_of_bookings_per_day');
        });
    }
}
