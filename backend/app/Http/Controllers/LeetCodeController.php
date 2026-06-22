<?php

namespace App\Http\Controllers;

use App\Services\LeetCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeetCodeController extends Controller
{
    protected LeetCodeService $leetCodeService;

    public function __construct(LeetCodeService $leetCodeService)
    {
        $this->leetCodeService = $leetCodeService;
    }

    /**
     * POST /user/connect-leetcode
     * Synchronizes LeetCode statistics, performance metrics, and submission calendar.
     */
    public function connect(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
        ]);

        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        try {
            $username = trim($validated['username']);
            
            Log::info("LeetCode sync initiated for user ID: {$user->id} with LeetCode username: '{$username}'");

            // 1. Fetch core profile and language stats
            $stats = $this->leetCodeService->getUserStats($username);

            if (!$stats) {
                Log::warning("LeetCode sync aborted: getUserStats returned null for '{$username}'");
                return response()->json([
                    'status' => 'error',
                    'message' => "Could not sync data. LeetCode user '{$username}' may not exist or LeetCode is unavailable."
                ], 400);
            }

            // 2. Fetch submission calendar (with dynamic year fallback layout)
            $currentYear = (int) date('Y');
            Log::debug("Fetching calendar data for '{$username}' for year {$currentYear}");
            $calendar = $this->leetCodeService->getProgressGraph($username, $currentYear);

            // FALLBACK LAYER 1: Object returned but matrix string is explicitly empty
            if (
                $calendar 
                && (empty($calendar['submissionCalendar']) || $calendar['submissionCalendar'] === '{}') 
                && !empty($calendar['activeYears'])
            ) {
                $lastActiveYear = (int) max($calendar['activeYears']);
                Log::info("Year {$currentYear} empty. Fast-forwarding to max active year: {$lastActiveYear}");
                $calendar = $this->leetCodeService->getProgressGraph($username, $lastActiveYear);
            } 
            // FALLBACK LAYER 2: Object is completely null (No 2026 activity). Scan backwards up to 5 years.
            elseif (!$calendar) {
                Log::info("Calendar returned null for {$currentYear}. Scanning past years sequentially...");
                for ($scanYear = $currentYear - 1; $scanYear >= $currentYear - 5; $scanYear--) {
                    $testCalendar = $this->leetCodeService->getProgressGraph($username, $scanYear);
                    
                    if ($testCalendar && !empty($testCalendar['submissionCalendar']) && $testCalendar['submissionCalendar'] !== '{}') {
                        Log::info("Successfully intercepted historical active calendar data for year {$scanYear}");
                        $calendar = $testCalendar;
                        break;
                    }
                }
            }

            // 3. Fetch user contest ranking metrics
            Log::debug("Fetching performance data for '{$username}'");
            $performance = $this->leetCodeService->getPerformance($username);
            return response()->json([
    'DEBUG_API_STATS' => $stats,
    'DEBUG_API_CALENDAR' => $calendar,
    'DEBUG_API_PERFORMANCE' => $performance
]);

            // 4. Persistence layer (relies on Eloquent array/json model casting)
            $user->update([
                'leetcode_username'    => $username,
                'total_solved'         => $stats['totalSolved'] ?? 0,
                'easy_solved'          => $stats['easySolved'] ?? 0,
                'medium_solved'        => $stats['mediumSolved'] ?? 0,
                'hard_solved'          => $stats['hardSolved'] ?? 0,
                'leetcode_calendar'    => $calendar,
                'leetcode_performance' => $performance,
            ]);

            Log::info("LeetCode sync successfully completed for '{$username}'");

            return response()->json([
                'status' => 'success',
                'message' => 'LeetCode stats synchronized successfully.',
                'data' => [
                    'leetcode_username' => $username,
                    'stats' => [
                        'totalSolved'  => $user->total_solved,
                        'easySolved'   => $user->easy_solved,
                        'mediumSolved' => $user->medium_solved,
                        'hard_solved'   => $user->hard_solved,
                    ],
                    'calendar'    => $calendar,
                    'performance' => $performance,
                ]
            ]);

        } catch (\Throwable $e) {
            Log::error('LeetCode sync critical failure', [
                'username'  => $validated['username'] ?? 'unknown',
                'exception' => $e
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to synchronize LeetCode data.'
            ], 500);
        }
    }

    /**
     * GET /user/leetcode-calendar
     * Returns the serialized heat map matrix data.
     */
    public function getCalendar(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (empty($user->leetcode_calendar)) {
            return response()->json([
                'activeYears'        => [],
                'streak'             => 0,
                'totalActiveDays'    => 0,
                'submissionCalendar' => [],
            ]);
        }

        return response()->json(
            is_string($user->leetcode_calendar)
                ? json_decode($user->leetcode_calendar, true)
                : $user->leetcode_calendar
        );
    }

    /**
     * GET /user/leetcode-performance
     * Returns contest profile standing matrices.
     */
    public function getPerformance(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (empty($user->leetcode_performance)) {
            return response()->json([
                'userContestRanking'        => null,
                'userContestRankingHistory' => [],
            ]);
        }

        return response()->json(
            is_string($user->leetcode_performance)
                ? json_decode($user->leetcode_performance, true)
                : $user->leetcode_performance
        );
    }
}