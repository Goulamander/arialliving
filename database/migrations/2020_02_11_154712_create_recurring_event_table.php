<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecurringEventTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('recurring_event', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bookable_item_id')->unsigned();
            $table->date('repeat_start');
            $table->date('repeat_next');
            $table->date('repeat_end')->nullable();
            $table->integer('repeat_every');
            $table->integer('frequency');
            $table->string('frequency_week_days')->nullable();
        });
        Schema::table('recurring_event', function (Blueprint $table) {
            $table->foreign('bookable_item_id')->references('id')->on('bookable_items')->onDelete('cascade');
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recurring_event');
    }
}
