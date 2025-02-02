@extends('layouts.app')

@section('title', 'ダッシュボード')

@section('header', 'ダッシュボード')

@section('content')
<div class="space-y-6">
    <!-- 統計カード -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard-stats-card
            title="総タスク数"
            :value="$statistics['total']"
            color="blue"
        />
        <x-dashboard-stats-card
            title="進行中"
            :value="$statistics['in_progress']"
            color="yellow"
        />
        <x-dashboard-stats-card
            title="完了"
            :value="$statistics['completed']"
            color="green"
        />
        <x-dashboard-stats-card
            title="期限超過"
            :value="$statistics['overdue']"
            color="red"
        />
    </div>

    <!-- グラフ -->
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <x-dashboard-progress-chart :data="$progressChart" />
        <x-dashboard-tag-chart :data="$tagStatistics" />
    </div>

    <!-- 今後の締め切りタスク -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                今後の締め切りタスク
            </h3>
            @if($upcomingDeadlines->isNotEmpty())
                <div class="space-y-4">
                    @foreach($upcomingDeadlines as $todo)
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">{{ $todo->title }}</h4>
                                <p class="text-sm text-gray-500">{{ $todo->description }}</p>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($todo->deadline)->format('Y/m/d') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">期限が設定されたタスクはありません</p>
            @endif
        </div>
    </div>

    <!-- タイムラインチャート -->
    <x-dashboard-timeline-chart :data="$dailyTaskCreation" />
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // グラフの初期化は各コンポーネントで行います
});
</script>
@endpush 