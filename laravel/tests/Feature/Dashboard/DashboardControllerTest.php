<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Models\Todo;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function dashboard_shows_correct_statistics_with_data()
    {
        // 動作記録: with_data
        // テストデータの準備
        $now = Carbon::now();
        Carbon::setTestNow($now);

        // タスクの作成（合計4件）
        Todo::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'in_progress'
        ]);
        Todo::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed'
        ]);
        Todo::factory()->count(2)->create([  // 3件から2件に変更
            'user_id' => $this->user->id,
            'status' => 'pending',
            'deadline' => $now->copy()->subDay()  // 期限超過
        ]);

        // タグの作成とタスクへの関連付け
        $tag1 = Tag::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'work'
        ]);
        $tag2 = Tag::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'personal'
        ]);

        // ダッシュボードの表示
        $response = $this->actingAs($this->user)
            ->get('/dashboard');

        // レスポンスの検証
        $response->assertStatus(200);
        $response->assertViewIs('dashboard.index');
        
        // データの検証
        $response->assertViewHas('statistics', [
            'total' => 4,  // 5から4に修正
            'in_progress' => 1,
            'completed' => 1,
            'overdue' => 2  // 3から2に修正
        ]);

        $response->assertViewHas('progressChart');
        $response->assertViewHas('tagStatistics');
        $response->assertViewHas('dailyTaskCreation');
    }

    #[Test]
    public function dashboard_shows_correct_statistics_with_no_data()
    {
        // 動作記録: no_data
        $response = $this->actingAs($this->user)
            ->get('/dashboard');

        // レスポンスの検証
        $response->assertStatus(200);
        $response->assertViewIs('dashboard.index');
        
        // データの検証
        $response->assertViewHas('statistics', [
            'total' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'overdue' => 0
        ]);

        $response->assertViewHas('progressChart');
        $response->assertViewHas('tagStatistics');
        $response->assertViewHas('dailyTaskCreation');
    }
} 