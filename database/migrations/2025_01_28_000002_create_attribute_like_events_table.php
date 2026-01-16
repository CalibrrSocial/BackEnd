<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attribute_like_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index(); // liker
            $table->unsignedBigInteger('profile_id')->index(); // liked
            $table->string('category', 100);
            $table->string('attribute', 255);
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('last_unliked_at')->nullable();
            $table->integer('notify_count')->default(0);
            $table->boolean('can_notify_again')->default(false);
            $table->timestamps();
            
            $table->unique(['user_id', 'profile_id', 'category', 'attribute']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_like_events');
    }
};
