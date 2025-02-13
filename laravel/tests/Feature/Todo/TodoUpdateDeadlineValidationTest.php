<?php

namespace Tests\Feature\Todo;

use App\Models\Todo;
use App\Models\User;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoUpdateDeadlineValidationTest extends TestCase
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
            'title' => '更新後のタスク',
            'description' => 'これは更新後のタスクです',
            'status' => 'pending',
            'deadline' => '2025-04-01'
        ]);

        // タグを関連付け
        $this->todo->tags()->attach([$this->tags[0]['id'], $this->tags[1]['id']]);
    }

    /**
     * @test
     * @group todo_update_deadline_validation
     */
    public function cannot_update_todo_with_past_deadline()
    {
        // ユーザーとして認証
        $this->actingAs($this->user);

        // 過去の期限で更新を試行
        $updateData = [
            'deadline' => '2024-03-25',
            'last_updated' => now()->format('Y-m-d\TH:i:s.u\Z')
        ];

        // 更新リクエストを送信
        $response = $this->put('/todos/' . $this->todo->id . '/deadline', $updateData);

        // レスポンスの検証
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['deadline' => '期限は現在時刻より後の日時を指定してください。']);

        // データベースが変更されていないことを確認
        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'deadline' => '2025-04-01'
        ]);
    }

    /**
     * @test
     * @group todo_update_deadline_validation
     */
    public function cannot_update_todo_with_invalid_date_format()
    {
        // ユーザーとして認証
        $this->actingAs($this->user);

        // 不正な日付形式で更新を試行
        $updateData = [
            'deadline' => 'invalid-date',
            'last_updated' => now()->format('Y-m-d\TH:i:s.u\Z')
        ];

        // 更新リクエストを送信
        $response = $this->put('/todos/' . $this->todo->id . '/deadline', $updateData);

        // レスポンスの検証
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['deadline']);

        // データベースが変更されていないことを確認
        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'deadline' => '2025-04-01'
        ]);
    }

    /**
     * @test
     * @group todo_update_deadline_validation
     */
    public function can_update_todo_with_valid_future_deadline()
    {
        // ユーザーとして認証
        $this->actingAs($this->user);

        // 未来の期限で更新
        $newDeadline = now()->addDays(7)->format('Y-m-d');
        $updateData = [
            'deadline' => $newDeadline,
            'last_updated' => now()->format('Y-m-d\TH:i:s.u\Z')
        ];

        // 更新リクエストを送信
        $response = $this->put('/todos/' . $this->todo->id . '/deadline', $updateData);

        // レスポンスの検証
        $response->assertStatus(302)
                ->assertRedirect('/todos');

        // データベースが更新されていることを確認
        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'deadline' => $newDeadline
        ]);
    }
} 