<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('phone')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->boolean('ghostMode')->default(1);
            $table->string('subscription')->nullable();
            $table->string('location')->nullable();
            $table->string('pictureProfile')->nullable();
            $table->string('pictureCover')->nullable();
            $table->dateTime('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('bio')->nullable();
            $table->string('education')->nullable();
            $table->string('politics')->nullable();
            $table->string('religion')->nullable();
            $table->string('occupation')->nullable();
            $table->string('sexuality')->nullable();
            $table->string('relationship')->nullable();
            $table->rememberToken();
            $table->timestamps();
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
    }
}
