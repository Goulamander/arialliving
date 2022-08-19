<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookableItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Main
        Schema::create('bookable_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->integer('type')->length(1);
            $table->integer('building_id')->unsigned()->nullable();
            $table->integer('category_id')->unsigned()->nullable();
            $table->integer('status')->length(1)->default(0); // draft
            $table->boolean('is_private')->default(false);
            $table->text('description');
            $table->boolean('is_signature_required')->default(false);
            $table->string('is_thumb')->nullable();
            $table->boolean('is_free')->default(true);
            $table->string('price_tag')->nullable();
            $table->decimal('admin_fee', 8, 2)->nullable();
            $table->text('office_hours')->nullable();
            $table->boolean('ignore_office_hours')->default(false);
            $table->integer('prior_to_book_hours')->default(2);
            $table->integer('cancellation_cut_off')->default(24);
            $table->text('booking_instructions')->nullable();
            $table->integer('created_by')->unassigned()->nullable();
            $table->integer('order')->unassigned()->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::table('bookable_items', function (Blueprint $table) {
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });


        // Room
        Schema::create('bookable_item_room', function (Blueprint $table) {
            $table->integer('bookable_item_id')->unsigned();
            $table->integer('daily_booking_limit')->default(2);
            $table->integer('booking_max_length')->nullable();
            $table->integer('booking_min_length')->nullable();
            $table->time('booking_from_time')->nullable();
            $table->time('booking_to_time')->nullable();
            $table->integer('booking_gap')->nullable();
            $table->boolean('allow_multiday')->default(0);
            $table->boolean('low_availability')->default(0);
            $table->boolean('is_resident_comment')->default(false);
        });
        Schema::table('bookable_item_room', function (Blueprint $table) {
            $table->foreign('bookable_item_id')->references('id')->on('bookable_items')->onDelete('cascade');
        });


        // Event
        Schema::create('bookable_item_event', function (Blueprint $table) {
            $table->integer('bookable_item_id')->unsigned();
            $table->string('location_name')->nullable();
            $table->integer('location_id')->unsigned()->nullable();
            $table->string('event_type'); // Single, Recurring
            $table->date('event_date')->nullable();
            $table->boolean('all_day')->default(false);
            $table->time('event_from')->nullable();
            $table->time('event_to')->nullable();
            $table->integer('attendees_limit')->nullable();
            $table->boolean('is_rsvp')->default(false);
            $table->integer('allow_guests')->nullable();
            $table->integer('low_seats')->nullable();
        });
        Schema::table('bookable_item_event', function (Blueprint $table) {
            $table->foreign('bookable_item_id')->references('id')->on('bookable_items')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('bookable_items')->onDelete('cascade');
        });


        // Hire
        Schema::create('bookable_item_hire', function (Blueprint $table) {
            $table->integer('bookable_item_id')->unsigned();
            $table->integer('available_qty')->nullable();
            $table->boolean('allow_multiple')->default(false);
            $table->integer('allow_multiple_max')->length(3);
            $table->decimal('item_price', 8, 2)->nullable();
            $table->string('item_price_unit')->length(10);
            $table->decimal('bond_amount', 8, 2)->nullable();
            $table->integer('booking_max_length')->nullable();
            $table->integer('booking_min_length')->nullable();
            $table->boolean('allow_multiday')->default(1);
            $table->integer('booking_gap')->nullable();
            $table->boolean('low_availability')->default(0);
        });
        Schema::table('bookable_item_hire', function (Blueprint $table) {
            $table->foreign('bookable_item_id')->references('id')->on('bookable_items')->onDelete('cascade');
        });


        // Service
        Schema::create('bookable_item_service', function (Blueprint $table) {
            $table->integer('bookable_item_id')->unsigned();
            $table->string('date_field_name')->nullable();
            $table->boolean('is_date')->default(0);

        });
        Schema::table('bookable_item_service', function (Blueprint $table) {
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
        Schema::dropIfExists('bookable_item_service');
        Schema::dropIfExists('bookable_item_hire');
        Schema::dropIfExists('bookable_item_event');
        Schema::dropIfExists('bookable_item_room');
        Schema::dropIfExists('bookable_items');
    }
}
