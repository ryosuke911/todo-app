<?php

namespace Tests\Feature\Todo;

use Tests\TestCase;
use App\Models\User;
use App\Models\Todo;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class TodoLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * @test
     * @group todo
     * @group high-priority
     */
    public function test_create_todo_with_tags_maintains_recorded_behavior()
    {
        $this->actingAs($this->user);
        
        // 1. タグの準備
        $tag = Tag::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'テスト用タグ'
        ]);

        // 2. Todoの作成リクエスト
        $response = $this->post('/todos', [
            'title' => 'テストTodo',
            'description' => 'これはテスト用のTodoです',
            'status' => 'pending',
            'deadline' => null,
            'tags' => [$tag->id]
        ]);

        // 3. レスポンスの検証
        $response->assertRedirect('/todos');
        
        // 4. データベースの検証
        $this->assertDatabaseHas('todos', [
            'title' => 'テストTodo',
            'description' => 'これはテスト用のTodoです',
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        // 5. タグ関連付けの検証
        $todo = Todo::where('title', 'テストTodo')->first();
        $this->assertDatabaseHas('tag_todo', [
            'todo_id' => $todo->id,
            'tag_id' => $tag->id
        ]);

        // 6. パフォーマンス検証
        $this->assertResponseTime($response, 800); // SLA: 800ms以内
    }

    /**
     * @test
     * @group todo
     * @group high-priority
     */
    public function test_access_control_for_other_user_todos()
    {
        // 1. 2人のユーザーとTodoを準備
        $otherUser = User::factory()->create();
        $otherTodo = Todo::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'pending'
        ]);

        // 2. 現在のユーザーとしてアクセス
        $this->actingAs($this->user);
        $response = $this->get('/todos');

        // 3. アクセス制御の検証
        $response->assertOk();
        $response->assertDontSee($otherTodo->title);
        
        // 4. 直接アクセスの制御を検証
        $directResponse = $this->get("/todos/{$otherTodo->id}");
        $directResponse->assertForbidden();

        // 5. パフォーマンス検証
        $this->assertResponseTime($response, 800); // SLA: 800ms以内
    }

    /**
     * レスポンスタイムがSLA内であることを確認するヘルパーメソッド
     */
    private function assertResponseTime($response, $maxTime)
    {
        $actualTime = $response->headers->get('X-Runtime') * 1000; // 秒からミリ秒に変換
        $this->assertLessThanOrEqual(
            $maxTime,
            $actualTime,
            "Response time {$actualTime}ms exceeds SLA of {$maxTime}ms"
        );
    }
} 