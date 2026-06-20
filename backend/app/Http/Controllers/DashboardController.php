<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get the authenticated user's dashboard statistics.
     */
    public function index(Request $request)
    {
        // 1. Get the current user authenticated via Sanctum token
        $user = $request->user();

        // 2. Return the data structured exactly like the React frontend interface expects
        return response()->json([
            'leetcodeUsername' => $user->leetcode_username,
            'totalSolved'      => (int) ($user->total_solved ?? 0),
            'easySolved'       => (int) ($user->easy_solved ?? 0),
            'mediumSolved'     => (int) ($user->medium_solved ?? 0),
            'hardSolved'       => (int) ($user->hard_solved ?? 0),
            'acceptanceRate'   => (float) ($user->acceptance_rate ?? 0.0),
        ], 200);
    }
}