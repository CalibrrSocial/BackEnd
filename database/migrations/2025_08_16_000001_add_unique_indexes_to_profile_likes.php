<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('profile_likes', function (Blueprint $table) {
            try { $table->index('user_id'); } catch (\Throwable $e) {}
            try { $table->index('profile_id'); } catch (\Throwable $e) {}
            try { $table->unique(['user_id','profile_id']); } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('profile_likes', function (Blueprint $table) {
            try { $table->dropUnique(['user_id','profile_id']); } catch (\Throwable $e) {}
            try { $table->dropIndex(['user_id']); } catch (\Throwable $e) {}
            try { $table->dropIndex(['profile_id']); } catch (\Throwable $e) {}
        });
    }
};


