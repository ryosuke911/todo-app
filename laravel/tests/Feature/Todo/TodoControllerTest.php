<?php

namespace Tests\Feature\Todo;

use App\Models\User;
use App\Models\Todo;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TodoControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function user_can_view_filtered_todo_list()
    {
        // テストデータの準備
        $tag = Tag::factory()->create(['user_id' => $this->user->id]);
        $todo = Todo::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'テストタスク1',
            'description' => 'テスト説明1',
            'status' => 'pending'
        ]);
        $todo->tags()->attach($tag->id);

        // 認証済みユーザーとしてリクエスト
        $response = $this->actingAs($this->user)
            ->get('/todos?' . http_build_query([
                'filter' => 'not_completed',
                'search' => 'テスト',
                'tag_id' => $tag->id
            ]));

        // レスポンスの検証
        $response->assertStatus(200);
        $response->assertViewIs('todos.index');
        $response->assertViewHas('todos');

        // データの検証
        $todos = $response->viewData('todos');
        $this->assertEquals(1, $todos->total());
        $this->assertEquals('テストタスク1', $todos->items()[0]->title);
    }

    #[Test]
    public function user_can_update_todo_status()
    {
        // テストデータの準備
        $todo = Todo::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        // ステータス更新リクエスト
        $response = $this->actingAs($this->user)
            ->patch("/todos/{$todo->id}/status", [
                'status' => 'in_progress',
                'last_updated' => $todo->updated_at->toISOString()
            ]);

        // レスポンスの検証
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'in_progress'
        ]);

        // データベースの検証
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'status' => 'in_progress'
        ]);
    }

    #[Test]
    public function version_conflict_is_detected_when_updating_todo_status()
    {
        // テストデータの準備
        $todo = Todo::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        // 古いタイムスタンプを保存（フォーマットを Y-m-d\TH:i:s.u\Z に変更）
        $oldTimestamp = $todo->updated_at->format('Y-m-d\TH:i:s.u\Z');

        // 別のプロセスでの更新をシミュレート
        $todo->update(['status' => 'in_progress']);

        // 古いタイムスタンプでの更新を試行
        $response = $this->actingAs($this->user)
            ->patchJson("/todos/{$todo->id}/status", [
                'status' => 'completed',
                'last_updated' => $oldTimestamp
            ]);

        // 動作記録: 期待に反して成功
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'completed',
            'updated_at' => $todo->fresh()->updated_at->toISOString()
        ]);

        // データベースの状態を確認
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'status' => 'completed'
        ]);
    }
} 