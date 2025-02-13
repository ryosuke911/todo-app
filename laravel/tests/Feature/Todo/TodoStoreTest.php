<?php

namespace Tests\Feature\Todo;

use App\Models\Todo;
use App\Models\User;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoStoreTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private array $tags;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザーの作成
        $this->user = User::factory()->create();

        // テスト用のタグを作成
        $this->tags = Tag::factory()->count(3)->create([
            'user_id' => $this->user->id
        ])->toArray();
    }

    /**
     * @test
     * @group todo_store_basic
     */
    public function user_can_view_todo_create_form()
    {
        // ユーザーとして認証
        $this->actingAs($this->user);

        // タスク作成フォームにアクセス
        $response = $this->get('/todos/create');

        // レスポンスの検証
        $response->assertStatus(200);
        $response->assertViewIs('todos.create');
        $response->assertViewHas('tags');
    }

    /**
     * @test
     * @group todo_store_basic
     */
    public function user_can_create_todo_with_valid_data()
    {
        // ユーザーとして認証
        $this->actingAs($this->user);

        // 動作記録に基づくテストデータ
        $todoData = [
            'title' => 'テストタスク',
            'description' => 'これはテストタスクです',
            'deadline' => '2025-03-25',
            'tags' => [$this->tags[0]['id'], $this->tags[1]['id']]
        ];

        // タスク作成リクエストを送信
        $response = $this->post('/todos', $todoData);

        // レスポンスの検証
        $response->assertStatus(302);
        $response->assertRedirect('/todos');

        // データベースの検証
        $this->assertDatabaseHas('todos', [
            'title' => 'テストタスク',
            'description' => 'これはテストタスクです',
            'deadline' => '2025-03-25',
            'user_id' => $this->user->id
        ]);

        // タグの関連付けを検証
        $todo = Todo::latest()->first();
        $this->assertCount(2, $todo->tags);
        $this->assertTrue($todo->tags->contains('id', $this->tags[0]['id']));
        $this->assertTrue($todo->tags->contains('id', $this->tags[1]['id']));
    }

    /**
     * @test
     * @group todo_store_basic
     */
    public function user_cannot_create_todo_without_required_fields()
    {
        // ユーザーとして認証
        $this->actingAs($this->user);

        // 必須フィールドを省略してリクエスト
        $response = $this->post('/todos', []);

        // バリデーションエラーの検証
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);

        // データベースに保存されていないことを確認
        $this->assertDatabaseCount('todos', 0);
    }

    /**
     * @test
     * @group todo_store_basic
     */
    public function user_cannot_create_todo_with_non_existent_tags()
    {
        // ユーザーとして認証
        $this->actingAs($this->user);

        // 存在しないタグIDでリクエスト
        $todoData = [
            'title' => 'テストタスク',
            'description' => 'これはテストタスクです',
            'deadline' => '2025-03-25',
            'tags' => [999, 1000] // 存在しないタグID
        ];

        // タスク作成リクエストを送信
        $response = $this->post('/todos', $todoData);

        // バリデーションエラーの検証
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tags.0', 'tags.1']);

        // データベースに保存されていないことを確認
        $this->assertDatabaseCount('todos', 0);
    }

    /**
     * @test
     * @group todo_store_basic
     */
    public function unauthorized_user_cannot_create_todo()
    {
        // 未認証状態でリクエスト
        $todoData = [
            'title' => 'テストタスク',
            'description' => 'これはテストタスクです',
            'deadline' => '2025-03-25'
        ];

        // タスク作成リクエストを送信
        $response = $this->post('/todos', $todoData);

        // 401 Unauthorizedが返されることを確認
        $response->assertStatus(401);

        // データベースに保存されていないことを確認
        $this->assertDatabaseCount('todos', 0);
    }

    /**
     * @test
     * @group todo_store_basic
     */
    public function authenticated_user_cannot_access_create_form()
    {
        // 未認証状態でフォームにアクセス
        $response = $this->get('/todos/create');

        // 401 Unauthorizedが返されることを確認
        $response->assertStatus(401);
    }
} 