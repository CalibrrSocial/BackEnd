<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('profile_like_events')) {
            Schema::table('profile_like_events', function (Blueprint $table) {
                if (!Schema::hasColumn('profile_like_events', 'notify_count')) {
                    $table->unsignedTinyInteger('notify_count')->default(0)->after('notified_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('profile_like_events')) {
            Schema::table('profile_like_events', function (Blueprint $table) {
                if (Schema::hasColumn('profile_like_events', 'notify_count')) {
                    $table->dropColumn('notify_count');
                }
            });
        }
    }
};


