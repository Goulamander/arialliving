<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Activation;

class CreateUsersTable extends Migration
{
    
    protected $activation;

    public function __construct() {
        $this->activation = new Activation();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('role_id')->unsigned()->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->string('is_flagged_reason')->nullable();
            $table->boolean('status')->default(0); // inactive
            $table->string('tokenCustomerID')->length(155)->nullable();
            $table->string('card_details')->nullable();
            $table->string('password')->default($this->activation->generateToken());
            $table->boolean('activated')->default(false);
            $table->rememberToken();
            $table->boolean('is_set_password')->default(false);
            //
            $table->softDeletes();
            $table->timestamps();
        });

        // Create table for storing roles
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            //
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
        });
        
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }

}




