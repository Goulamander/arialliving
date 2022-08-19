<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreDealTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the Stores
        Schema::create('retail_stores', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('building_id')->unsigned();
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('name')->nullable();
            $table->text('description');
            $table->integer('status')->length(1)->default(1);
            $table->string('thumb')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::table('retail_stores', function(Blueprint $table){
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Create the deals
        Schema::create('retail_deals', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('store_id')->unsigned();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description');
            $table->text('terms');
            $table->integer('status')->length(1);
            $table->string('thumb')->nullable();
            $table->integer('allowed_redeem_num')->nullable();
            $table->integer('created_by')->unsigned();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::table('retail_deals', function(Blueprint $table){
            $table->foreign('store_id')->references('id')->on('retail_stores')->onDelete('cascade');
        });

        // Create the deals
        Schema::create('user_deal_redeems', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('retail_deal_id')->unsigned();
            $table->timestamps();
        });
        Schema::table('user_deal_redeems', function(Blueprint $table){
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('retail_deal_id')->references('id')->on('retail_deals')->onDelete('cascade');
            $table->string('code')->length(20);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_deal_redeems');
        Schema::dropIfExists('deals');
        Schema::dropIfExists('stores');
    }
}
