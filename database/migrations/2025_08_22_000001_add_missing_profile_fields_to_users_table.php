<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add missing social and preference fields
            if (!Schema::hasColumn('users', 'favorite_music')) {
                $table->text('favorite_music')->nullable();
            }
            if (!Schema::hasColumn('users', 'favorite_tv')) {
                $table->text('favorite_tv')->nullable();
            }
            if (!Schema::hasColumn('users', 'favorite_games')) {
                $table->text('favorite_games')->nullable();
            }
            if (!Schema::hasColumn('users', 'greek_life')) {
                $table->string('greek_life')->nullable();
            }
            if (!Schema::hasColumn('users', 'studying')) {
                $table->string('studying')->nullable();
            }
            if (!Schema::hasColumn('users', 'club')) {
                $table->string('club')->nullable();
            }
            if (!Schema::hasColumn('users', 'jersey_number')) {
                $table->string('jersey_number')->nullable();
            }
            
            // Add missing system fields
            if (!Schema::hasColumn('users', 'ghost_mode_flag')) {
                $table->boolean('ghost_mode_flag')->default(0);
            }
            if (!Schema::hasColumn('users', 'subscription_type')) {
                $table->string('subscription_type')->nullable();
            }
            
            // Add new image column names if they don't exist
            if (!Schema::hasColumn('users', 'profile_pic')) {
                $table->string('profile_pic')->nullable();
            }
            if (!Schema::hasColumn('users', 'cover_image')) {
                $table->string('cover_image')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToRemove = [
                'favorite_music',
                'favorite_tv', 
                'favorite_games',
                'greek_life',
                'studying',
                'club',
                'jersey_number',
                'ghost_mode_flag',
                'subscription_type',
                'profile_pic',
                'cover_image'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
