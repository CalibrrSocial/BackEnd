<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('profile_like_events')) {
            Schema::table('profile_like_events', function (Blueprint $table) {
                if (!Schema::hasColumn('profile_like_events', 'last_unliked_at')) {
                    $table->timestamp('last_unliked_at')->nullable()->after('notify_count');
                }
                if (!Schema::hasColumn('profile_like_events', 'can_notify_again')) {
                    $table->boolean('can_notify_again')->default(false)->after('last_unliked_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('profile_like_events')) {
            Schema::table('profile_like_events', function (Blueprint $table) {
                if (Schema::hasColumn('profile_like_events', 'last_unliked_at')) {
                    $table->dropColumn('last_unliked_at');
                }
                if (Schema::hasColumn('profile_like_events', 'can_notify_again')) {
                    $table->dropColumn('can_notify_again');
                }
            });
        }
    }
};
