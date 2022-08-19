<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HideCartFunctionalityToBookableItemServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookable_item_service', function (Blueprint $table) {
            $table->boolean('hide_cart_functionality')->default(0);
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
            $table->dropColumn('hide_cart_functionality');
            //
        });
    }
}
