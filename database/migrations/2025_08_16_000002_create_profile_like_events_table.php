<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('profile_like_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index(); // liker
            $table->unsignedBigInteger('profile_id')->index(); // liked
            $table->timestamp('notified_at')->nullable();
            $table->unique(['user_id','profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_like_events');
    }
};


