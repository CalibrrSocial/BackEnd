<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateReportsTableForBlocking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            // Add new columns for enhanced reporting
            $table->integer('reported_user_id')->after('user_id'); // User being reported
            $table->string('reason_category')->after('info'); // Predefined reason category
            $table->boolean('auto_blocked')->default(true)->after('reason_category'); // Whether reporting auto-blocks
            $table->string('reporter_email')->nullable()->after('auto_blocked'); // Reporter's email
            $table->string('reported_user_email')->nullable()->after('reporter_email'); // Reported user's email
            $table->string('reporter_name')->nullable()->after('reported_user_email'); // Reporter's full name
            $table->string('reported_user_name')->nullable()->after('reporter_name'); // Reported user's full name
            
            // Add foreign key constraint
            $table->foreign('reported_user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Add index for performance
            $table->index(['reported_user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['reported_user_id']);
            $table->dropColumn([
                'reported_user_id',
                'reason_category', 
                'auto_blocked',
                'reporter_email',
                'reported_user_email',
                'reporter_name',
                'reported_user_name'
            ]);
        });
    }
}