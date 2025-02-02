<?php

namespace App\Services;

use App\Models\Todo;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * タスクの統計情報を取得
     */
    public function getTaskStatistics(int $userId): array
    {
        $todos = Todo::where('user_id', $userId);
        
        return [
            'total' => $todos->count(),
            'in_progress' => $todos->where('status', 'in_progress')->count(),
            'completed' => $todos->where('status', 'completed')->count(),
            'overdue' => $todos->where('status', 'in_progress')
                ->where('deadline', '<', Carbon::now())
                ->count(),
        ];
    }

    /**
     * タスク進捗状況のグラフデータを生成
     */
    public function getProgressChartData(int $userId): array
    {
        $todos = Todo::where('user_id', $userId)->get();
        
        $inProgress = $todos->where('status', 'in_progress')->count();
        $completed = $todos->where('status', 'completed')->count();
        $overdue = $todos->where('status', 'in_progress')
            ->where('deadline', '<', Carbon::now())
            ->count();

        return [
            'labels' => ['進行中', '完了', '期限超過'],
            'data' => [$inProgress, $completed, $overdue],
            'backgroundColor' => ['#3498db', '#2ecc71', '#e74c3c'],
        ];
    }

    /**
     * タグ別のタスク数を取得
     */
    public function getTagStatistics(int $userId): array
    {
        return Tag::where('user_id', $userId)
            ->withCount(['todos' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->orderByDesc('todos_count')
            ->limit(10)
            ->get()
            ->map(function ($tag) {
                return [
                    'name' => $tag->name,
                    'count' => $tag->todos_count,
                ];
            })
            ->toArray();
    }

    /**
     * 今後の締め切りタスクを取得
     */
    public function getUpcomingDeadlines(int $userId): Collection
    {
        return Todo::where('user_id', $userId)
            ->where('status', 'in_progress')
            ->where('deadline', '>=', Carbon::now())
            ->orderBy('deadline')
            ->limit(5)
            ->get();
    }

    /**
     * 日別タスク作成数の推移データを取得
     */
    public function getDailyTaskCreationData(int $userId): array
    {
        $startDate = Carbon::now()->subDays(13);
        
        $dailyCounts = Todo::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        $labels = [];
        $data = [];
        
        for ($i = 0; $i < 14; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $labels[] = $date;
            $data[] = $dailyCounts[$date] ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
} 