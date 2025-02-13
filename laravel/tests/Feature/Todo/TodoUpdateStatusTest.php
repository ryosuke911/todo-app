<?php

namespace Tests\Feature\Todo;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoUpdateStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Todo $todo;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザーの作成
        $this->user = User::factory()->create();

        // テスト用のTodoを作成
        $this->todo = Todo::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'テストタスク',
            'description' => 'これはテストタスクです',
            'status' => 'pending',
            'deadline' => '2024-03-21'
        ]);
    }

    /**
     * @test
     * @group todo_update_status
     */
    public function owner_can_update_todo_status()
    {
        // ユーザーとして認証
        $this->actingAs($this->user);

        // 動作記録に基づく更新データ
        $updateData = [
            'status' => 'in_progress',
            'last_updated' => now()->format('Y-m-d\TH:i:s.u\Z')
        ];

        // 更新リクエストを送信
        $response = $this->patch('/todos/' . $this->todo->id . '/status', $updateData);

        // レスポンスの検証
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'in_progress'
        ]);

        // データベースの検証
        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'status' => 'in_progress',
            'user_id' => $this->user->id
        ]);

        // 完了状態への更新
        $this->todo->refresh();
        $updateData = [
            'status' => 'completed',
            'last_updated' => $this->todo->updated_at->toISOString()
        ];

        $response = $this->patch('/todos/' . $this->todo->id . '/status', $updateData);
        
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'completed'
        ]);

        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'status' => 'completed'
        ]);
    }

    /**
     * @test
     * @group todo_update_status
     */
    public function owner_cannot_update_todo_with_version_conflict()
    {
        // ユーザーとして認証
        $this->actingAs($this->user);

        // 古いタイムスタンプで更新を試行
        $updateData = [
            'status' => 'in_progress',
            'last_updated' => now()->subHour()->format('Y-m-d\TH:i:s.u\Z')
        ];

        // 更新リクエストを送信
        $response = $this->patch('/todos/' . $this->todo->id . '/status', $updateData);

        // 409 Conflictが返されることを確認
        $response->assertStatus(409);
        $response->assertJsonStructure([
            'message',
            'updated_at'
        ]);

        // データベースが変更されていないことを確認
        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'status' => 'pending'
        ]);
    }

    /**
     * @test
     * @group todo_update_status
     */
    public function owner_cannot_update_todo_with_invalid_status()
    {
        // ユーザーとして認証
        $this->actingAs($this->user);

        // 無効なステータスで更新を試行
        $updateData = [
            'status' => 'invalid_status',
            'last_updated' => now()->format('Y-m-d\TH:i:s.u\Z')
        ];

        // 更新リクエストを送信
        $response = $this->patch('/todos/' . $this->todo->id . '/status', $updateData);

        // 422 Unprocessable Entityが返されることを確認
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);

        // データベースが変更されていないことを確認
        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'status' => 'pending'
        ]);
    }

    /**
     * @test
     * @group todo_update_status
     */
    public function non_owner_cannot_update_todo_status()
    {
        // 別のユーザーを作成して認証
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        // 更新データ
        $updateData = [
            'status' => 'in_progress',
            'last_updated' => now()->format('Y-m-d\TH:i:s.u\Z')
        ];

        // 更新リクエストを送信
        $response = $this->patch('/todos/' . $this->todo->id . '/status', $updateData);

        // 403 Forbiddenが返されることを確認
        $response->assertStatus(403);

        // データベースが変更されていないことを確認
        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'status' => 'pending'
        ]);
    }

    /**
     * @test
     * @group todo_update_status
     */
    public function unauthorized_user_cannot_update_todo_status()
    {
        // 更新データ
        $updateData = [
            'status' => 'in_progress',
            'last_updated' => now()->format('Y-m-d\TH:i:s.u\Z')
        ];

        // 更新リクエストを送信
        $response = $this->patch('/todos/' . $this->todo->id . '/status', $updateData);

        // 401 Unauthorizedが返されることを確認
        $response->assertStatus(401);

        // データベースが変更されていないことを確認
        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'status' => 'pending'
        ]);
    }
} 