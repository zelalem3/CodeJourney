<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Check if the columns don't exist yet before adding them
            if (!Schema::hasColumn('users', 'total_solved')) {
                $table->integer('total_solved')->default(0);
                $table->integer('easy_solved')->default(0);
                $table->integer('medium_solved')->default(0);
                $table->integer('hard_solved')->default(0);
            }

            if (!Schema::hasColumn('users', 'leetcode_calendar')) {
                $table->json('leetcode_calendar')->nullable();
            }

            if (!Schema::hasColumn('users', 'leetcode_performance')) {
                $table->json('leetcode_performance')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop columns cleanly if rolling back the migration
            $table->dropColumn([
                'total_solved',
                'easy_solved',
                'medium_solved',
                'hard_solved',
                'leetcode_calendar',
                'leetcode_performance'
            ]);
        });
    }
};