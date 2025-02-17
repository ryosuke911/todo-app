<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Models\Todo;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // 固定の日時を設定
        Carbon::setTestNow('2025-02-13 23:47:30');

        // テストユーザーを作成
        $this->user = User::factory()->create();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Carbon::setTestNow(); // テスト用の固定時刻をリセット
    }

    public function test_with_data()
    {
        // タグを作成
        $workTag = Tag::factory()->create([
            'name' => 'work',
            'user_id' => $this->user->id
        ]);

        $personalTag = Tag::factory()->create([
            'name' => 'personal',
            'user_id' => $this->user->id
        ]);

        // テストデータを作成
        $todos = [
            // 進行中のタスク
            Todo::factory()->create([
                'title' => 'Task 1',
                'status' => 'in_progress',
                'user_id' => $this->user->id,
                'created_at' => now(),
                'deadline' => now()->addDays(5)
            ]),
            // 完了済みのタスク
            Todo::factory()->create([
                'title' => 'Task 2',
                'status' => 'completed',
                'user_id' => $this->user->id,
                'created_at' => now(),
                'deadline' => now()->addDays(10)
            ]),
            // 期限切れのタスク1
            Todo::factory()->create([
                'title' => 'Task 3',
                'status' => 'pending',
                'user_id' => $this->user->id,
                'created_at' => now(),
                'deadline' => now()->subDays(2)
            ]),
            // 期限切れのタスク2
            Todo::factory()->create([
                'title' => 'Task 4',
                'status' => 'in_progress',
                'user_id' => $this->user->id,
                'created_at' => now(),
                'deadline' => now()->subDays(1)
            ])
        ];

        // タグを関連付け
        $todos[0]->tags()->attach($workTag->id);
        $todos[1]->tags()->attach($personalTag->id);

        // 認証済みユーザーとしてリクエスト
        $response = $this->actingAs($this->user)
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.index');

        // 統計データの検証
        $response->assertViewHas('statistics', function ($statistics) {
            return $statistics['total'] === 4 &&
                   $statistics['in_progress'] === 2 &&  // Task 1とTask 4
                   $statistics['completed'] === 1 &&    // Task 2
                   $statistics['overdue'] === 2;        // Task 3とTask 4
        });

        // 進捗チャートの検証
        $response->assertViewHas('progressChart', function ($chart) {
            return isset($chart['labels']) &&
                   isset($chart['data']) &&
                   isset($chart['backgroundColor']) &&
                   $chart['labels'] === ['進行中', '完了', '期限超過'] &&
                   $chart['data'] === [2, 1, 2];
        });

        // タグ統計の検証
        $response->assertViewHas('tagStatistics', function ($stats) {
            return count($stats) === 2 &&
                   collect($stats)->pluck('count')->sum() === 2 &&
                   collect($stats)->pluck('name')->contains('work') &&
                   collect($stats)->pluck('name')->contains('personal');
        });

        // 日別タスク作成数の検証
        $response->assertViewHas('dailyTaskCreation', function ($data) {
            return isset($data['labels']) &&
                   isset($data['data']) &&
                   count($data['labels']) === 14 &&
                   count($data['data']) === 14 &&
                   $data['data'][13] === 4; // 最終日（今日）のタスク数
        });
    }

    public function test_no_data()
    {
        // 認証済みユーザーとしてリクエスト
        $response = $this->actingAs($this->user)
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.index');

        // 統計データの検証
        $response->assertViewHas('statistics', function ($statistics) {
            return $statistics['total'] === 0 &&
                   $statistics['in_progress'] === 0 &&
                   $statistics['completed'] === 0 &&
                   $statistics['overdue'] === 0;
        });

        // 進捗チャートの検証
        $response->assertViewHas('progressChart', function ($chart) {
            return isset($chart['labels']) &&
                   isset($chart['data']) &&
                   isset($chart['backgroundColor']) &&
                   $chart['data'] === [0, 0, 0];
        });

        // タグ統計の検証
        $response->assertViewHas('tagStatistics', function ($stats) {
            return empty($stats);
        });

        // 日別タスク作成数の検証
        $response->assertViewHas('dailyTaskCreation', function ($data) {
            return isset($data['labels']) &&
                   isset($data['data']) &&
                   count($data['labels']) === 14 &&
                   count($data['data']) === 14 &&
                   array_sum($data['data']) === 0;
        });
    }

    public function test_unauthorized()
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
} 