<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateRelationshipTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relationships', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('friend_id');
            $table->string('status');
            $table->dateTime('dateRequested')->nullable()->default(null);
            $table->dateTime('dateAccepted')->nullable()->default(null);
            $table->dateTime('dateRejected')->nullable()->default(null);
            $table->dateTime('dateBlocked')->nullable()->default(null);
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
        Schema::dropIfExists('relationship');
    }
}
