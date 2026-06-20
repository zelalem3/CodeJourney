<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LeetCodeService
{
    protected string $url = 'https://leetcode.com/graphql';

    public function getUserStats(string $username)
    {
        // The exact GraphQL query string LeetCode expects
        $query = [
            'query' => '
                query userProblemsSolved($username: String!) {
                    allQuestionsCount {
                        difficulty
                        count
                    }
                    matchedUser(username: $username) {
                        submitStats {
                            acSubmissionNum {
                                difficulty
                                count
                                submissions
                            }
                        }
                    }
                }
            ',
            'variables' => [
                'username' => $username
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Referer' => 'https://leetcode.com',
        ])->post($this->url, $query);

        if ($response->failed()) {
            return null;
        }

        return $this->parseStats($response->json());
    }

    protected function parseStats(array $data): array
    {
        $stats = $data['data']['matchedUser']['submitStats']['acSubmissionNum'] ?? [];
        
        return [
            'totalSolved' => $stats[0]['count'] ?? 0,
            'easySolved'  => $stats[1]['count'] ?? 0,
            'mediumSolved'=> $stats[2]['count'] ?? 0,
            'hardSolved'  => $stats[3]['count'] ?? 0,
        ];
    }
}