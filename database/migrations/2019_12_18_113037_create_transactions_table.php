<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_id')->unsigned();
            $table->integer('type')->length(1);
            $table->string('responseCode')->nullable();
            $table->string('responseMessage')->nullable();
            $table->string('transactionID')->nullable();
            $table->string('transactionStatus')->nullable();
            $table->decimal('totalAmount', 10, 2);
            $table->integer('created_by')->unsigned()->nullable();
            $table->text('notes')->nullable();
            $table->integer('refund_id')->unsigned()->nullable();
            $table->integer('release_id')->unsigned()->nullable();
            $table->integer('retry_id')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('transactions', function (Blueprint $table) {     
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('refund_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('release_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('retry_id')->references('id')->on('transactions')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
