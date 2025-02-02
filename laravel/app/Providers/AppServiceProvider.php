<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // サイドバーコンポーネントを登録
        Blade::component('components.sidebar', 'sidebar');
        
        // ダッシュボードコンポーネントを登録
        Blade::component('dashboard.components.stats-card', 'dashboard-stats-card');
        Blade::component('dashboard.components.progress-chart', 'dashboard-progress-chart');
        Blade::component('dashboard.components.tag-chart', 'dashboard-tag-chart');
        Blade::component('dashboard.components.timeline-chart', 'dashboard-timeline-chart');
    }
} 