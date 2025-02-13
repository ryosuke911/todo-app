<?php

namespace Tests\Feature\Todo;

use App\Models\Todo;
use App\Models\User;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoUpdatePolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Todo $todo;
    private array $tags;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザーの作成
        $this->user = User::factory()->create();

        // テスト用のタグを作成
        $this->tags = Tag::factory()->count(2)->create([
            'user_id' => $this->user->id
        ])->toArray();

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
     * @group todo_policy_update
     */
    public function owner_can_update_todo_title()
    {
        // ユーザーとして認証
        $this->actingAs($this->user);

        // 動作記録に基づく更新データ
        $updateData = [
            'title' => '更新後のタスク',
            'last_updated' => now()->format('Y-m-d\TH:i:s.u\Z')
        ];

        // 更新リクエストを送信
        $response = $this->put('/todos/' . $this->todo->id . '/title', $updateData);

        // レスポンスの検証
        $response->assertStatus(302);
        $response->assertRedirect('/todos');

        // データベースの検証
        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'title' => '更新後のタスク',
            'user_id' => $this->user->id
        ]);
    }

    /**
     * @test
     * @group todo_policy_update
     */
    public function owner_can_update_todo_description()
    {
        // ユーザーとして認証
        $this->actingAs($this->user);

        // 動作記録に基づく更新データ
        $updateData = [
            'description' => 'これは更新後のタスクです',
            'last_updated' => now()->format('Y-m-d\TH:i:s.u\Z')
        ];

        // 更新リクエストを送信
        $response = $this->put('/todos/' . $this->todo->id . '/description', $updateData);

        // レスポンスの検証
        $response->assertStatus(302);
        $response->assertRedirect('/todos');

        // データベースの検証
        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'description' => 'これは更新後のタスクです',
            'user_id' => $this->user->id
        ]);
    }

    /**
     * @test
     * @group todo_policy_update
     */
    public function owner_can_update_todo_deadline()
    {
        // ユーザーとして認証
        $this->actingAs($this->user);

        // 動作記録に基づく更新データ
        $updateData = [
            'deadline' => '2025-04-01',
            'last_updated' => now()->format('Y-m-d\TH:i:s.u\Z')
        ];

        // 更新リクエストを送信
        $response = $this->put('/todos/' . $this->todo->id . '/deadline', $updateData);

        // レスポンスの検証
        $response->assertStatus(302);
        $response->assertRedirect('/todos');

        // データベースの検証
        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'deadline' => '2025-04-01',
            'user_id' => $this->user->id
        ]);
    }

    /**
     * @test
     * @group todo_policy_update
     */
    public function owner_cannot_update_todo_with_version_conflict()
    {
        // ユーザーとして認証
        $this->actingAs($this->user);

        // 古いタイムスタンプで更新を試行
        $updateData = [
            'title' => '更新後のタスク',
            'last_updated' => now()->subHour()->format('Y-m-d\TH:i:s.u\Z')
        ];

        // 更新リクエストを送信
        $response = $this->put('/todos/' . $this->todo->id . '/title', $updateData);

        // 409 Conflictが返されることを確認
        $response->assertStatus(409);

        // データベースが変更されていないことを確認
        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'title' => 'テストタスク'
        ]);
    }

    /**
     * @test
     * @group todo_policy_update
     */
    public function owner_cannot_update_todo_with_invalid_data()
    {
        // ユーザーとして認証
        $this->actingAs($this->user);

        // 無効なデータで更新を試行
        $updateData = [
            'deadline' => '2023-01-01', // 過去の日付（無効）
            'last_updated' => now()->format('Y-m-d\TH:i:s.u\Z')
        ];

        // 更新リクエストを送信
        $response = $this->put('/todos/' . $this->todo->id . '/deadline', $updateData);

        // 422 Unprocessable Entityが返されることを確認
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['deadline']);

        // データベースが変更されていないことを確認
        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'deadline' => '2024-03-21'
        ]);
    }

    /**
     * @test
     * @group todo_policy_update
     */
    public function owner_can_update_todo_tags()
    {
        // ユーザーとして認証
        $this->actingAs($this->user);

        // タグをTodoに関連付け
        $this->todo->tags()->attach($this->tags[0]['id']);

        // 動作記録に基づく更新データ
        $updateData = [
            'tags' => [$this->tags[1]['id']],
            'last_updated' => now()->format('Y-m-d\TH:i:s.u\Z')
        ];

        // 更新リクエストを送信
        $response = $this->put('/todos/' . $this->todo->id . '/tags', $updateData);

        // レスポンスの検証
        $response->assertStatus(302);
        $response->assertRedirect('/todos');

        // タグの関連付けが正しく更新されていることを確認
        $this->assertDatabaseMissing('todo_tag', [
            'todo_id' => $this->todo->id,
            'tag_id' => $this->tags[0]['id']
        ]);
        $this->assertDatabaseHas('todo_tag', [
            'todo_id' => $this->todo->id,
            'tag_id' => $this->tags[1]['id']
        ]);
    }

    /**
     * @test
     * @group todo_policy_update
     */
    public function non_owner_cannot_update_todo_field()
    {
        // 別のユーザーを作成して認証
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        // 更新データ
        $updateData = [
            'title' => '更新後のタスク',
            'last_updated' => now()->format('Y-m-d\TH:i:s.u\Z')
        ];

        // 更新リクエストを送信
        $response = $this->put('/todos/' . $this->todo->id . '/title', $updateData);

        // 403 Forbiddenが返されることを確認
        $response->assertStatus(403);

        // データベースが変更されていないことを確認
        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'title' => 'テストタスク'
        ]);
    }

    /**
     * @test
     * @group todo_policy_update
     */
    public function unauthorized_user_cannot_update_todo_field()
    {
        // 更新データ
        $updateData = [
            'title' => '更新後のタスク',
            'last_updated' => now()->format('Y-m-d\TH:i:s.u\Z')
        ];

        // 更新リクエストを送信
        $response = $this->put('/todos/' . $this->todo->id . '/title', $updateData);

        // 401 Unauthorizedが返されることを確認
        $response->assertStatus(401);

        // データベースが変更されていないことを確認
        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'title' => 'テストタスク'
        ]);
    }
} 