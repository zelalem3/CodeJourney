# 🚀 CodeJourney

**CodeJourney** is a high-performance platform that helps developers track, analyze, and gamify their algorithmic problem-solving journey. By synchronizing and caching data from **LeetCode**, CodeJourney delivers a fast, dark-themed dashboard featuring performance analytics, contribution heatmaps, progress visualizations, and competitive leaderboards.

---

# 🛠 Tech Stack

## Backend (API Layer)

- **Framework:** Laravel 11 (PHP 8.2+)
- **Database:** SQLite (Local caching layer)
- **Authentication:** Laravel Sanctum (SPA authentication)

## Frontend (UI Layer)

- **Library:** React 18 + Vite
- **Language:** TypeScript / JavaScript (ES6+)
- **HTTP Client:** Axios with centralized interceptors
- **Styling:** Responsive CSS

---

# ✨ Features

## 🔐 Secure Authentication

- Laravel Sanctum-powered SPA authentication.
- Protected API routes and session management.
- Axios interceptors automatically redirect unauthenticated users.

---

## 📊 LeetCode Profile Synchronization

- Synchronizes and caches LeetCode profile statistics locally.
- Reduces dependency on external requests and improves dashboard performance.
- Validation guards prevent invalid or empty payloads.

---

## 📈 Performance Metrics Dashboard

Track important coding statistics, including:

- Total problems solved
- Easy problems solved
- Medium problems solved
- Hard problems solved
- Acceptance rate
- Ranking information

Defensive calculations prevent division-by-zero errors and missing-data issues.

---

## 📅 Contribution Activity Heatmap

- Displays the last 53 weeks of submission activity.
- Built entirely with SVG for responsiveness and performance.
- Automatically scales using dynamic `viewBox` calculations.

---

## 📉 Contest Rating Progress

- Interactive SVG trendline showing historical rating changes.
- Visualizes competitive programming growth over time.

---

## 🏆 Global Leaderboards

- Database pagination (10 users per page).
- Sort users by:
  - Total problems solved
  - Username
  - Ranking metrics
- Designed to scale efficiently as the user base grows.

---

# 📂 Project Structure

```text
CodeJourney/
├── backend/                          # Laravel API
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── DashboardController.php
│   │       ├── LeetCodeController.php
│   │       └── LeaderboardController.php
│   │
│   ├── Models/
│   │   └── User.php
│   │
│   └── Services/
│       └── LeetCodeService.php
│
├── database/
│   └── database.sqlite
│
└── routes/
    └── api.php

frontend/
└── src/
    ├── api/
    │   └── axios.js
    │
    ├── components/
    │   ├── Dashboard.tsx
    │   ├── Leaderboard.tsx
    │   ├── ContributionHeatmap.tsx
    │   └── RatingChart.tsx
    │
    └── pages/
        ├── Home.tsx
        ├── Login.tsx
        └── Register.tsx
```

---

# ⚙️ Installation

## 1. Backend Setup

Navigate to the backend directory:

```bash
cd backend
```

Install dependencies:

```bash
composer install
```

Create the environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Create the SQLite database:

```bash
touch database/database.sqlite
```

Run migrations:

```bash
php artisan migrate
```

Clear caches:

```bash
php artisan route:clear
php artisan cache:clear
php artisan config:clear
```

Start the API server:

```bash
php artisan serve
```

---

## 2. Seed a Test LeetCode Account

Open Laravel Tinker:

```bash
php artisan tinker
```

Run:

```php
$user = App\Models\User::first();

$user->leetcode_username = 'amalmadhu06';

$user->save();
```

Exit:

```php
exit
```

---

## 3. Frontend Setup

Move to the frontend directory:

```bash
cd ../frontend
```

Install dependencies:

```bash
npm install
```

Start the Vite development server:

```bash
npm run dev
```

---

# 📸 Core Components

| Component | Purpose |
|------------|---------|
| `Dashboard.tsx` | Main statistics dashboard |
| `Leaderboard.tsx` | Global rankings and sorting |
| `ContributionHeatmap.tsx` | 53-week activity graph |
| `RatingChart.tsx` | Contest rating progression |
| `axios.js` | Centralized API configuration |
| `LeetCodeService.php` | GraphQL synchronization service |

---

# 🚀 Roadmap

## Real-Time Coding Duels

Compete against other developers in live problem-solving sessions.

**Technologies**

- Laravel Reverb
- WebSockets
- Socket.IO

---

## Team and Clan Rankings

Create groups and compete collectively through:

- Team leaderboards
- Shared progress tracking
- Seasonal competitions

---

## AI Code Mentor

Receive intelligent feedback on your solutions:

- Time complexity analysis (`O(N)` vs `O(N²)`)
- Space complexity evaluation
- Optimization suggestions
- Progressive hints instead of direct answers

---

## Achievement System

Unlock badges and milestones for:

- Daily streaks
- Contest participation
- Problem-solving milestones
- Consistency goals

---

## GitHub Integration

Combine coding activity from multiple sources:

- LeetCode
- GitHub
- Codeforces
- HackerRank

---

# 🎯 Vision

CodeJourney aims to become a unified developer growth platform where programmers can monitor their progress, compete with others, maintain consistency, and continuously improve their problem-solving skills through data-driven insights and community engagement.
