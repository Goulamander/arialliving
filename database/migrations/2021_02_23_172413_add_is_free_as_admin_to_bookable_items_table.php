<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsFreeAsAdminToBookableItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookable_items', function (Blueprint $table) {
            $table->boolean('is_free_as_admin')->default(0)->after('is_free');
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
            $table->dropColumn('is_free_as_admin');
        });
    }
}
