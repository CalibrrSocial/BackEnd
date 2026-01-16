<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('users', function (Blueprint $table) {
			if (!Schema::hasColumn('users', 'hometown')) {
				$table->string('hometown')->nullable();
			}
			if (!Schema::hasColumn('users', 'high_school')) {
				$table->string('high_school')->nullable();
			}
			if (!Schema::hasColumn('users', 'class_year')) {
				$table->string('class_year')->nullable();
			}
			if (!Schema::hasColumn('users', 'campus')) {
				$table->string('campus')->nullable();
			}
			if (!Schema::hasColumn('users', 'career_aspirations')) {
				$table->string('career_aspirations')->nullable();
			}
			if (!Schema::hasColumn('users', 'postgraduate')) {
				$table->string('postgraduate')->nullable();
			}
			if (!Schema::hasColumn('users', 'postgraduate_plans')) {
				$table->string('postgraduate_plans')->nullable();
			}
		});
	}

	public function down(): void
	{
		Schema::table('users', function (Blueprint $table) {
			if (Schema::hasColumn('users', 'hometown')) {
				$table->dropColumn('hometown');
			}
			if (Schema::hasColumn('users', 'high_school')) {
				$table->dropColumn('high_school');
			}
			if (Schema::hasColumn('users', 'class_year')) {
				$table->dropColumn('class_year');
			}
			if (Schema::hasColumn('users', 'campus')) {
				$table->dropColumn('campus');
			}
			if (Schema::hasColumn('users', 'career_aspirations')) {
				$table->dropColumn('career_aspirations');
			}
			if (Schema::hasColumn('users', 'postgraduate')) {
				$table->dropColumn('postgraduate');
			}
			if (Schema::hasColumn('users', 'postgraduate_plans')) {
				$table->dropColumn('postgraduate_plans');
			}
		});
	}
};


