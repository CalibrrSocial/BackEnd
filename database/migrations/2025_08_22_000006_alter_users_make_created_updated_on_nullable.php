<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'created_on')) {
                $table->dateTime('created_on')->nullable()->change();
            }
            if (Schema::hasColumn('users', 'updated_on')) {
                $table->dateTime('updated_on')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'created_on')) {
                $table->dateTime('created_on')->nullable(false)->change();
            }
            if (Schema::hasColumn('users', 'updated_on')) {
                $table->dateTime('updated_on')->nullable(false)->change();
            }
        });
    }
};


