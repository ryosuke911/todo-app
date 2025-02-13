<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Todo;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $tag;
    private $todo;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザーを作成
        $this->user = User::factory()->create();

        // テストユーザーでログイン
        $this->actingAs($this->user);

        // テストタグを作成
        $this->tag = Tag::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'work'
        ]);

        // テストTodoを作成
        $this->todo = Todo::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'テストタスク1',
            'description' => 'テスト説明1',
            'status' => 'pending',
            'deadline' => '2024-03-25'
        ]);

        // TodoとTagを関連付け
        $this->todo->tags()->attach($this->tag->id);
    }

    public function test_filtered_list()
    {
        $response = $this->get('/todos?' . http_build_query([
            'filter' => 'not_completed',
            'search' => 'テスト',
            'tag_id' => $this->tag->id
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('todos.index');
        
        $response->assertViewHas('todos', function ($todos) {
            return $todos->total() === 1 &&
                   $todos->perPage() === 10 &&
                   $todos->first()->title === 'テストタスク1' &&
                   $todos->first()->tags->contains($this->tag);
        });
    }

    public function test_status_update()
    {
        $lastUpdated = $this->todo->updated_at->toISOString();

        $response = $this->patchJson("/todos/{$this->todo->id}/status", [
            'status' => 'in_progress',
            'last_updated' => $lastUpdated
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'in_progress'
        ]);

        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'status' => 'in_progress'
        ]);
    }

    public function test_version_conflict()
    {
        // 古いタイムスタンプを用意
        $oldTimestamp = now()->subMinute()->toISOString();
        
        // 一度ステータスを更新して、updated_atを変更
        $this->todo->update(['status' => 'in_progress']);

        $response = $this->patchJson("/todos/{$this->todo->id}/status", [
            'status' => 'completed',
            'last_updated' => $oldTimestamp
        ]);

        $response->assertStatus(409);
        $response->assertJsonStructure([
            'message',
            'updated_at'
        ]);
    }
} 