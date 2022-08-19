<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('type')->length(1)->default(1);
            $table->text('comment')->nullable();
            // relations
            $table->integer('resident_id')->unsigned()->nullable();
            $table->integer('booking_id')->unsigned()->nullable();
            $table->integer('building_id')->unsigned()->nullable();
            $table->integer('bookable_item_id')->unsigned()->nullable();
            $table->timestamps();
        });
        Schema::table('comments', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // relations
            $table->foreign('resident_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
            $table->foreign('bookable_item_id')->references('id')->on('bookable_items')->onDelete('cascade');
        });

        // Comment replies relation
        Schema::create('comment_replies', function (Blueprint $table) {
            $table->integer('comment_id')->unsigned();
            $table->integer('reply_comment_id')->unsigned();
        });

        Schema::table('comment_replies', function(Blueprint $table) {
            $table->foreign('comment_id')->references('id')->on('comments')->onDelete('cascade');
            $table->foreign('reply_comment_id')->references('id')->on('comments')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('comment_replies');
    }
}
