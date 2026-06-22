<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\LeetCodeService;
use Illuminate\Support\Facades\Log;

class SyncLeetCodeData extends Command
{
    /**
     * The name and signature of the console command.
     * --user is optional. If provided, it syncs one user. If omitted, it syncs everyone.
     *
     * @var string
     */
    protected $signature = 'leetcode:sync {--user= : The ID of the specific user to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize LeetCode statistics and calendar data for users';

    protected LeetCodeService $leetCodeService;

    public function __construct(LeetCodeService $leetCodeService)
    {
        parent::__construct();
        $this->leetCodeService = $leetCodeService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return Command::FAILURE;
            }
            if (empty($user->leetcode_username)) {
                $this->error("User {$user->name} does not have a LeetCode username configured.");
                return Command::FAILURE;
            }

            $this->syncUser($user);
        } else {
            // Sync all users who have a LeetCode username set
            $users = User::whereNotNull('leetcode_username')
                         ->where('leetcode_username', '!=', '')
                         ->get();

            if ($users->isEmpty()) {
                $this->info('No users found with a LeetCode username.');
                return Command::SUCCESS;
            }

            $this->info("Starting sync for {$users->count()} user(s)...");
            
            $this->withProgressBar($users, function ($user) {
                $this->syncUser($user);
            });
            
            $this->newLine();
        }

        $this->info('LeetCode synchronization process complete!');
        return Command::SUCCESS;
    }

    /**
     * Sync logic extracted from your controller
     */
    protected function syncUser(User $user)
    {
        $username = trim($user->leetcode_username);

        try {
            // 1. Fetch core stats
            $stats = $this->leetCodeService->getUserStats($username);
            if (!$stats) {
                Log::warning("Artisan Sync aborted: Profile null for '{$username}'");
                return;
            }

            // 2. Fetch calendar with fallback layer
            $currentYear = (int) date('Y');
            $calendar = $this->leetCodeService->getProgressGraph($username, $currentYear);

            if ($calendar && (empty($calendar['submissionCalendar']) || $calendar['submissionCalendar'] === '{}') && !empty($calendar['activeYears'])) {
                $lastActiveYear = (int) max($calendar['activeYears']);
                $calendar = $this->leetCodeService->getProgressGraph($username, $lastActiveYear);
            } elseif (!$calendar) {
                for ($scanYear = $currentYear - 1; $scanYear >= $currentYear - 5; $scanYear--) {
                    $testCalendar = $this->leetCodeService->getProgressGraph($username, $scanYear);
                    if ($testCalendar && !empty($testCalendar['submissionCalendar']) && $testCalendar['submissionCalendar'] !== '{}') {
                        $calendar = $testCalendar;
                        break;
                    }
                }
            }

            // 3. Fetch performance
            $performance = $this->leetCodeService->getPerformance($username);

            // 4. Update User
            $user->update([
                'total_solved'         => $stats['totalSolved'] ?? 0,
                'easy_solved'          => $stats['easySolved'] ?? 0,
                'medium_solved'        => $stats['mediumSolved'] ?? 0,
                'hard_solved'          => $stats['hardSolved'] ?? 0,
                'leetcode_calendar'    => $calendar,
                'leetcode_performance' => $performance,
            ]);

        } catch (\Throwable $e) {
            Log::error("Artisan LeetCode sync failure for user ID {$user->id}", [
                'username'  => $username,
                'exception' => $e
            ]);
        }
    }
}