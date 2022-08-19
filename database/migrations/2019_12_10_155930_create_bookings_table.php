<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('building_id')->unsigned();
            $table->integer('bookable_item_id')->unsigned();
            $table->integer('type')->length(1);
            $table->timestamp('start');
            $table->timestamp('end');
            $table->string('length_str')->length(155);

            $table->integer('qty');
            $table->text('line_items')->nullable();
            $table->decimal('subtotal', 8, 2)->nullable();
            $table->decimal('GST', 8, 2)->nullable();
            $table->decimal('bond', 8, 2)->nullable();
            $table->decimal('admin_fee', 8, 2)->nullable();
            $table->decimal('total', 8, 2)->nullable();

            $table->json('accepted_terms')->nullable();
            $table->text('signature')->nullable();
            $table->text('booking_comments')->nullable();

            $table->date('cancellation_cutoff_date')->nullable();
            $table->integer('status')->length(1);
            
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
            $table->foreign('bookable_item_id')->references('id')->on('bookable_items')->onDelete('cascade');
        });


        // Event
        Schema::create('booking_event', function (Blueprint $table) {
            $table->integer('booking_id')->unsigned();
            $table->boolean('booking_status')->default(true);
            $table->integer('attendees_num')->nullable();
        });
        Schema::table('booking_event', function (Blueprint $table) {
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_event');
        Schema::dropIfExists('bookings');
    }
}
