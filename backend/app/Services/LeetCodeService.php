<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LeetCodeService
{
    protected string $url = 'https://leetcode.com/graphql';

    protected function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'Referer'      => 'https://leetcode.com',
            'Origin'       => 'https://leetcode.com',
            'User-Agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36',
        ];
    }

    /**
     * Send GraphQL request
     */
    protected function query(array $payload, string $methodName): ?array
    {
        try {
            Log::debug("LeetCode Sending Request [{$methodName}]", ['variables' => $payload['variables'] ?? []]);

            $response = Http::withHeaders($this->getHeaders())
                ->timeout(15)
                ->retry(3, 1000)
                ->post($this->url, $payload);

            return $this->handleGraphQLResponse($response, $methodName);

        } catch (\Exception $e) {
            Log::error("LeetCode {$methodName} Network/HTTP Exception", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Fetch core problem-solving numbers
     */
    public function getUserStats(string $username): ?array
    {
        $payload = [
            'query' => '
                query userProblemsSolved($username: String!) {
                    matchedUser(username: $username) {
                        submitStatsGlobal {
                            acSubmissionNum {
                                difficulty
                                count
                            }
                        }
                    }
                }
            ',
            'variables' => ['username' => $username]
        ];

        $data = $this->query($payload, 'getUserStats');

        if (!$data || empty($data['data']['matchedUser'])) {
            Log::warning("LeetCode getUserStats: 'matchedUser' missing from response. User might not exist.", ['raw_data' => $data]);
            return null;
        }

        return $this->parseStats($data);
    }

    /**
     * Fetch submission calendar for a specific year
     */
    public function getProgressGraph(string $username, int $year = 2026): ?array
    {
        $payload = [
            'query' => '
                query UserProfileCalendar($username: String!, $year: Int!) {
                    matchedUser(username: $username) {
                        userCalendar(year: $year) {
                            activeYears
                            streak
                            totalActiveDays
                            dccBadges {
                                timestamp
                                badge { name icon }
                            }
                            submissionCalendar
                        }
                    }
                }
            ',
            'variables' => ['username' => $username, 'year' => $year]
        ];

        $data = $this->query($payload, 'getProgressGraph');

        if (!$data || empty($data['data']['matchedUser'])) {
            return null;
        }

        $calendar = $data['data']['matchedUser']['userCalendar'] ?? null;

        // If null, return null silently so the controller fallback can execute a scan
        if (!$calendar) {
            return null;
        }

        if (isset($calendar['submissionCalendar']) && is_string($calendar['submissionCalendar'])) {
            $calendar['submissionCalendar'] = json_decode($calendar['submissionCalendar'], true) ?? [];
        }

        return $calendar;
    }

    /**
     * Fetch contest history and performance rankings
     */
    public function getPerformance(string $username): ?array
    {
        $payload = [
            'query' => '
                query userContestData($username: String!) {
                    userContestRanking(username: $username) {
                        attendedContestsCount rating globalRanking totalParticipants topPercentage badge { name }
                    }
                    userContestRankingHistory(username: $username) {
                        attended trendDirection problemsSolved totalProblems finishTimeInSeconds rating ranking contest { title startTime }
                    }
                }
            ',
            'variables' => ['username' => $username]
        ];

        $data = $this->query($payload, 'getPerformance');

        if (!$data) {
            return null;
        }

        return [
            'userContestRanking' => $data['data']['userContestRanking'] ?? null,
            'userContestRankingHistory' => $data['data']['userContestRankingHistory'] ?? [],
        ];
    }

    /**
     * Handle GraphQL response with robust error reporting
     */
    protected function handleGraphQLResponse($response, string $methodName): ?array
    {
        if ($response->failed()) {
            Log::error("LeetCode {$methodName} HTTP Error Status: " . $response->status(), [
                'body_snippet' => substr($response->body(), 0, 1000),
                'headers' => $response->headers()
            ]);
            return null;
        }

        if (!str_contains($response->header('Content-Type'), 'application/json')) {
            Log::error("LeetCode {$methodName} returned unexpected content type: " . $response->header('Content-Type'), [
                'body_raw' => substr($response->body(), 0, 1000)
            ]);
            return null;
        }

        $data = $response->json();

        if (isset($data['errors'])) {
            Log::error("LeetCode {$methodName} Internal GraphQL Errors", [
                'errors' => $data['errors']
            ]);
            return null;
        }

        if (!isset($data['data'])) {
            Log::warning("LeetCode {$methodName}: HTTP 200 OK but completely empty 'data' key wrapper.", [
                'response' => $data
            ]);
            return null;
        }

        return $data;
    }

    protected function parseStats(array $data): array
    {
        $stats = $data['data']['matchedUser']['submitStatsGlobal']['acSubmissionNum'] ?? [];

        $mapped = collect($stats)
            ->pluck('count', 'difficulty')
            ->toArray();

        return [
            'totalSolved'  => $mapped['All'] ?? 0,
            'easySolved'   => $mapped['Easy'] ?? 0,
            'mediumSolved' => $mapped['Medium'] ?? 0,
            'hardSolved'   => $mapped['Hard'] ?? 0,
        ];
    }
}