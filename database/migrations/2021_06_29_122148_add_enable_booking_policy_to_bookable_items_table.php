<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnableBookingPolicyToBookableItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookable_items', function (Blueprint $table) {
            //
            $table->boolean('enable_booking_policy')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookable_items', function (Blueprint $table) {
            //
            $table->dropColumn('enable_booking_policy');
        });
    }
}
