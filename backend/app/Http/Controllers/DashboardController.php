<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard
     * Fetch the user's cached problem-solving statistics.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // CRITICAL: Extract the linked username so the frontend can read it
        $leetcodeUsername = $user->leetcode_username;

        // Calculate custom analytics indicators like acceptance rate safely
        $totalSolved = $user->total_solved ?? 0;
        $easySolved = $user->easy_solved ?? 0;
        $mediumSolved = $user->medium_solved ?? 0;
        $hardSolved = $user->hard_solved ?? 0;

        // Simple mock analytics value for frontend placeholder visualization layout
        $acceptanceRate = $totalSolved > 0 ? round(($easySolved * 1.1 + $mediumSolved * 1.0 + $hardSolved * 0.9) / ($totalSolved ?: 1) * 45, 1) : 0;
        if ($acceptanceRate > 100) $acceptanceRate = 74.5; // Cap at typical average metric values

        return response()->json([
            'leetcode_username' => $leetcodeUsername, // <-- FIX: Sent down so React can bundle it into the sync POST payload
            'totalSolved'       => $totalSolved,
            'easySolved'        => $easySolved,
            'mediumSolved'      => $mediumSolved,
            'hardSolved'        => $hardSolved,
            'acceptanceRate'    => $acceptanceRate ?: 0,
        ], 200);
    }
}