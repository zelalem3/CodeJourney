<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\LeetcodeService; 

class LeetCodeController extends Controller
{
    protected LeetCodeService $leetCodeService;

    public function __construct(LeetCodeService $leetCodeService)
    {
        $this->leetCodeService = $leetCodeService;
    }

    /**
     * Sync and update LeetCode statistics using the username stored in the database.
     */
    public function connect(Request $request)
    {
        // 1. Get the currently authenticated user
        /** @var \App\Models\User $user */
        $user = $request->user();

        // 2. Verify that this user actually has a LeetCode username saved in the DB
        $username = $user->leetcode_username;

        if (!$username) {
            return response()->json([
                'status' => 'error',
                'message' => 'No LeetCode username linked to this account yet. Please link your account first.'
            ], 422); // 422 Unprocessable Entity
        }

        // 3. Call your service using the database value
        $stats = $this->leetCodeService->getUserStats($username);

        // 4. Handle API failures
        if (!$stats) {
            return response()->json([
                'status' => 'error',
                'message' => "Could not sync data. LeetCode user '{$username}' might not exist or the API is down."
            ], 400);
        }

        // 5. Update the user's statistics in the database
        $user->update([
            'total_solved'  => $stats['totalSolved'],
            'easy_solved'   => $stats['easySolved'],
            'medium_solved' => $stats['mediumSolved'],
            'hard_solved'   => $stats['hardSolved'],
        ]);

        // 6. Return the updated numbers back to your React components
        return response()->json([
            'status' => 'success',
            'message' => 'LeetCode stats synchronized successfully!',
            'data' => [
                'username'     => $username,
                'totalSolved'  => $user->total_solved,
                'easySolved'   => $user->easy_solved,
                'mediumSolved' => $user->medium_solved,
                'hardSolved'   => $user->hard_solved,
            ]
        ], 200);
    }
}