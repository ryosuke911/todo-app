<?php

namespace Tests\Feature\Todo;

use App\Models\Todo;
use App\Models\User;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class TodoListTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private array $todos;
    private array $tags;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザーの作成と認証
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // テスト用のタグを作成
        $this->tags = Tag::factory()->count(3)->create([
            'user_id' => $this->user->id
        ])->toArray();

        // テスト用のTodoを作成
        $this->todos = [];
        // 未完了タスク
        $this->todos[] = Todo::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'テストタスク1',
            'description' => 'テスト説明1',
            'status' => 'pending',
            'deadline' => '2024-03-20'
        ]);
        // 完了タスク
        $this->todos[] = Todo::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'テストタスク2',
            'description' => 'テスト説明2',
            'status' => 'completed',
            'deadline' => '2024-03-21'
        ]);
        // タグ付きタスク
        $todo = Todo::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'テストタスク3',
            'description' => 'テスト説明3',
            'status' => 'pending',
            'deadline' => '2024-03-22'
        ]);
        $todo->tags()->attach($this->tags[0]['id']);
        $this->todos[] = $todo;
    }

    /**
     * @test
     * @group todo_service_get_todos
     */
    public function user_can_view_filtered_todo_list()
    {
        // 動作記録に基づくリクエストパラメータ
        $params = [
            'filter' => 'not_completed',
            'search' => 'テスト',
            'deadline' => '2024-03-21',
            'tag_id' => $this->tags[0]['id']
        ];

        // 動作記録: GETリクエストで/todosにアクセス
        $response = $this->get('/todos?' . http_build_query($params));

        // 動作記録の検証: status: 200
        $response->assertStatus(200);
        $response->assertViewIs('todos.index');

        // 動作記録の検証: data_structure
        $response->assertViewHas('todos');
        $todos = $response->viewData('todos');
        $this->assertEquals('Illuminate\Pagination\LengthAwarePaginator', get_class($todos));
        $this->assertEquals(10, $todos->perPage());

        // ソート順の検証
        $items = $todos->items();
        for ($i = 0; $i < count($items) - 1; $i++) {
            $this->assertTrue(
                $items[$i]->deadline <= $items[$i + 1]->deadline ||
                ($items[$i]->deadline == $items[$i + 1]->deadline &&
                 $items[$i]->created_at >= $items[$i + 1]->created_at)
            );
        }

        // タグ情報の検証
        $response->assertViewHas('tags');
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $response->viewData('tags'));
    }

    /**
     * @test
     * @group todo_service_get_todos
     */
    public function unauthorized_user_cannot_view_todo_list()
    {
        // ログアウト
        auth()->logout();

        // アクセス試行
        $response = $this->get('/todos');

        // リダイレクトの検証
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * @test
     * @group todo_service_get_todos
     */
    public function user_can_only_view_their_own_todos()
    {
        // 別のユーザーのTodoを作成
        $otherUser = User::factory()->create();
        $otherTodo = Todo::factory()->create([
            'user_id' => $otherUser->id,
            'title' => '他のユーザーのタスク'
        ]);

        // タスク一覧を取得
        $response = $this->get('/todos');
        $response->assertStatus(200);

        // 他のユーザーのタスクが含まれていないことを確認
        $todos = $response->viewData('todos');
        $this->assertEquals(0, $todos->where('user_id', $otherUser->id)->count());
    }
} 