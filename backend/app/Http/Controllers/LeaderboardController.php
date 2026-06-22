<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    /**
     * GET /api/leaderboard
     * Fetch global rankings with dynamic sorting and pagination.
     */
    public function index(Request $request)
    {
        // Allowed sorting criteria to prevent SQL injection vulnerabilities
        $sortBy = $request->query('sort_by', 'total_solved');
        $allowedSorts = ['total_solved', 'leetcode_username'];
        
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'total_solved';
        }

        // Query only users who have connected a profile, ordered by performance
        $leaderboard = User::whereNotNull('leetcode_username')
            ->select(['id', 'name', 'leetcode_username', 'total_solved', 'easy_solved', 'medium_solved', 'hard_solved'])
            ->orderBy($sortBy, $sortBy === 'leetcode_username' ? 'asc' : 'desc')
            ->paginate(10); // 10 users per page split

        return response()->json($leaderboard, 200);
    }
}