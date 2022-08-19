<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookableItemFeeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookable_item_fee', function (Blueprint $table) {
            $table->id();
            $table->integer('bookable_item_id')->unsigned();
            $table->integer('type')->default(0);
            $table->string('name')->nullable();
            $table->decimal('fee', 8, 2)->nullable();

            // $table->softDeletes();
            $table->timestamps();

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
        Schema::dropIfExists('bookable_item_fee');
    }
}
