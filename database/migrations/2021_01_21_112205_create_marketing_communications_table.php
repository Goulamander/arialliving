<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketingCommunicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketing_communications', function (Blueprint $table) {
            $table->id();
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->text('replace')->nullable();
            $table->boolean('send_via')->default(0)->comment('0 - Email, 1 - SMS'); // 0 - Email, 1 - SMS
            $table->boolean('status')->default(0)->comment('0 - Draft, 1 - Send'); // 0 - Draft, 1 - Send
            $table->text('receiver')->nullable();
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marketing_communications');
    }
}
