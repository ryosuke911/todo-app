<?php

namespace Tests\Unit\Services;

use App\Models\Todo;
use App\Models\User;
use App\Models\Tag;
use App\Services\TodoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class TodoServiceTest extends TestCase
{
    use RefreshDatabase;

    private TodoService $todoService;
    private User $user;
    private array $todos;
    private array $tags;

    protected function setUp(): void
    {
        parent::setUp();
        $this->todoService = new TodoService();

        // テストユーザーの作成
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
            'status' => 'pending',
            'deadline' => Carbon::now()->addDays(1)
        ]);
        // 完了タスク
        $this->todos[] = Todo::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'テストタスク2',
            'status' => 'completed',
            'deadline' => Carbon::now()->addDays(2)
        ]);
        // タグ付きタスク
        $todo = Todo::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'テストタスク3',
            'status' => 'pending',
            'deadline' => Carbon::now()->addDays(3)
        ]);
        $todo->tags()->attach($this->tags[0]['id']);
        $this->todos[] = $todo;
    }

    /**
     * @test
     * @group todo_service_get_todos
     */
    public function get_todos_returns_paginated_todos()
    {
        $result = $this->todoService->getTodos();

        $this->assertEquals(10, $result->perPage());
        $this->assertCount(3, $result->items());
    }

    /**
     * @test
     * @group todo_service_get_todos
     */
    public function get_todos_filters_by_not_completed_status()
    {
        $result = $this->todoService->getTodos(['filter' => 'not_completed']);

        $this->assertCount(2, $result->items());
        foreach ($result as $todo) {
            $this->assertNotEquals('completed', $todo->status);
        }
    }

    /**
     * @test
     * @group todo_service_get_todos
     */
    public function get_todos_filters_by_tag()
    {
        $result = $this->todoService->getTodos(['tag_id' => $this->tags[0]['id']]);

        $this->assertCount(1, $result->items());
        $this->assertTrue($result[0]->tags->contains('id', $this->tags[0]['id']));
    }

    /**
     * @test
     * @group todo_service_get_todos
     */
    public function get_todos_filters_by_search_term()
    {
        // タイトルで検索
        $result = $this->todoService->getTodos(['search' => 'タスク1']);
        $this->assertCount(1, $result->items());
        $this->assertEquals('テストタスク1', $result[0]->title);

        // 説明文で検索
        Todo::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'その他のタスク',
            'description' => '検索用テキスト',
            'status' => 'pending'
        ]);
        $result = $this->todoService->getTodos(['search' => '検索用']);
        $this->assertCount(1, $result->items());
        $this->assertEquals('その他のタスク', $result[0]->title);
    }

    /**
     * @test
     * @group todo_service_get_todos
     */
    public function get_todos_filters_by_deadline()
    {
        $deadline = Carbon::now()->addDays(2)->format('Y-m-d');
        $result = $this->todoService->getTodos(['deadline' => $deadline]);

        foreach ($result as $todo) {
            $this->assertTrue($todo->deadline->lte(Carbon::parse($deadline)));
        }
    }

    /**
     * @test
     * @group todo_service_get_todos
     */
    public function get_todos_sorts_by_deadline_and_created_at()
    {
        $result = $this->todoService->getTodos();
        $items = $result->items();

        // 期限でソート（昇順）されていることを確認
        for ($i = 0; $i < count($items) - 1; $i++) {
            $this->assertTrue(
                $items[$i]->deadline->lte($items[$i + 1]->deadline) ||
                ($items[$i]->deadline->eq($items[$i + 1]->deadline) &&
                 $items[$i]->created_at->gte($items[$i + 1]->created_at))
            );
        }
    }

    /**
     * @test
     * @group todo_service_get_todos
     */
    public function get_todos_loads_tags_relation()
    {
        $result = $this->todoService->getTodos();

        foreach ($result as $todo) {
            $this->assertTrue($todo->relationLoaded('tags'));
        }
    }
} 