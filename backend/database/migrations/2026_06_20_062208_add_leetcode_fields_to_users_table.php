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
        Schema::table('users', function (Blueprint $blueprint) {
            // Nullable because a user might register without linking LeetCode immediately
            $blueprint->string('leetcode_username')->nullable()->after('password');
            
            // Stats integers defaulting to 0
            $blueprint->integer('total_solved')->default(0)->after('leetcode_username');
            $blueprint->integer('easy_solved')->default(0)->after('total_solved');
            $blueprint->integer('medium_solved')->default(0)->after('easy_solved');
            $blueprint->integer('hard_solved')->default(0)->after('medium_solved');
            
            // Acceptance rate (using float or string depending on LeetCode format)
            $blueprint->float('acceptance_rate')->default(0.0)->after('hard_solved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $blueprint) {
            $blueprint->dropColumn([
                'leetcode_username',
                'total_solved',
                'easy_solved',
                'medium_solved',
                'hard_solved',
                'acceptance_rate'
            ]);
        });
    }
};