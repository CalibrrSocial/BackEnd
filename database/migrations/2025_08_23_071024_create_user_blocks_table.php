<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_blocks', function (Blueprint $table) {
            $table->id();
            $table->integer('blocker_id')->unsigned(); // User who is doing the blocking
            $table->integer('blocked_id')->unsigned(); // User who is being blocked
            $table->string('reason')->nullable(); // Optional reason for blocking
            $table->boolean('is_active')->default(true); // For soft unblocking
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('blocker_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('blocked_id')->references('id')->on('users')->onDelete('cascade');
            
            // Unique constraint to prevent duplicate blocks
            $table->unique(['blocker_id', 'blocked_id']);
            
            // Index for performance
            $table->index(['blocker_id', 'is_active']);
            $table->index(['blocked_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_blocks');
    }
}