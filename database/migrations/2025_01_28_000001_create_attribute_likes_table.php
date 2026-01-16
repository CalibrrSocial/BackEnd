<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attribute_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index(); // who liked
            $table->unsignedBigInteger('profile_id')->index(); // whose attribute was liked
            $table->string('category', 100); // e.g., "Music", "Politics", "Education"
            $table->string('attribute', 255); // e.g., "Hip Hop", "Liberal", "Computer Science"
            $table->boolean('is_liked')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
            
            // Ensure one like per user per attribute
            $table->unique(['user_id', 'profile_id', 'category', 'attribute']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_likes');
    }
};
