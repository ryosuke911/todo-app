<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $userId = auth()->id();

        $data = [
            'statistics' => $this->dashboardService->getTaskStatistics($userId),
            'progressChart' => $this->dashboardService->getProgressChartData($userId),
            'tagStatistics' => $this->dashboardService->getTagStatistics($userId),
            'upcomingDeadlines' => $this->dashboardService->getUpcomingDeadlines($userId),
            'dailyTaskCreation' => $this->dashboardService->getDailyTaskCreationData($userId),
        ];

        return view('dashboard.index', $data);
    }
} 