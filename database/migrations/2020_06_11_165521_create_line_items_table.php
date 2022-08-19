<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLineItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('item_id')->unsigned();
            $table->string('name');
            $table->text('desc');
            $table->decimal('price', 8, 2)->nullable();
            $table->string('thumb')->nullable();
            $table->integer('status')->length(1)->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::table('line_items', function(Blueprint $table) {
            $table->foreign('item_id')->references('id')->on('bookable_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('line_items');
    }
}
