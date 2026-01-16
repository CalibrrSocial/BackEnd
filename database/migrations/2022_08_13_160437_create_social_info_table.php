<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_infos', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('snapchat')->nullable();
            $table->string('linkedIn')->nullable();
            $table->string('twitter')->nullable();
            $table->string('resume')->nullable();
            $table->string('coverLetter')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('contact')->nullable();
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
        Schema::dropIfExists('social_info');
    }
}
