<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $hasCreated = Schema::hasColumn('users', 'created_at');
            $hasUpdated = Schema::hasColumn('users', 'updated_at');

            if (!$hasCreated && !$hasUpdated) {
                $table->timestamps();
            } else {
                if (!$hasCreated) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!$hasUpdated) {
                    $table->timestamp('updated_at')->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'created_at') && Schema::hasColumn('users', 'updated_at')) {
                $table->dropTimestamps();
            } else {
                if (Schema::hasColumn('users', 'created_at')) {
                    $table->dropColumn('created_at');
                }
                if (Schema::hasColumn('users', 'updated_at')) {
                    $table->dropColumn('updated_at');
                }
            }
        });
    }
};


