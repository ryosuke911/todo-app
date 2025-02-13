<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Todo;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザーを作成
        $this->user = User::factory()->create();

        // テストユーザーでログイン
        $this->actingAs($this->user);
    }

    public function test_with_data()
    {
        // テストデータを作成
        $tag = Tag::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'work'
        ]);

        // 進行中のタスク（期限切れ）
        Todo::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'deadline' => now()->subDays(1)
        ])->tags()->attach($tag->id);

        // 完了済みのタスク
        Todo::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'deadline' => now()->addDays(2)
        ])->tags()->attach($tag->id);

        // 未完了のタスク（期限切れ）
        for ($i = 0; $i < 2; $i++) {
            Todo::factory()->create([
                'user_id' => $this->user->id,
                'status' => 'pending',
                'deadline' => now()->subDays($i + 1)
            ])->tags()->attach($tag->id);
        }

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.index');

        $response->assertViewHas('statistics', function ($statistics) {
            return $statistics['total'] === 4 &&
                   $statistics['in_progress'] === 1 &&
                   $statistics['completed'] === 1 &&
                   $statistics['overdue'] === 3;
        });

        $response->assertViewHas('progressChart', function ($chart) {
            return $chart['labels'] === ['進行中', '完了', '期限超過'] &&
                   $chart['data'] === [1, 1, 3] &&
                   isset($chart['backgroundColor']);
        });

        $response->assertViewHas('tagStatistics', function ($stats) {
            return count($stats) === 1 &&
                   $stats[0]['name'] === 'work' &&
                   $stats[0]['count'] === 4;
        });

        $response->assertViewHas('dailyTaskCreation', function ($data) {
            return count($data['labels']) === 14 &&
                   count($data['data']) === 14 &&
                   array_sum($data['data']) === 4;
        });
    }

    public function test_no_data()
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.index');

        $response->assertViewHas('statistics', function ($statistics) {
            return $statistics['total'] === 0 &&
                   $statistics['in_progress'] === 0 &&
                   $statistics['completed'] === 0 &&
                   $statistics['overdue'] === 0;
        });

        $response->assertViewHas('progressChart', function ($chart) {
            return $chart['labels'] === ['進行中', '完了', '期限超過'] &&
                   $chart['data'] === [0, 0, 0] &&
                   isset($chart['backgroundColor']);
        });

        $response->assertViewHas('tagStatistics', function ($stats) {
            return empty($stats);
        });

        $response->assertViewHas('dailyTaskCreation', function ($data) {
            return count($data['labels']) === 14 &&
                   count($data['data']) === 14 &&
                   array_sum($data['data']) === 0;
        });
    }
} 