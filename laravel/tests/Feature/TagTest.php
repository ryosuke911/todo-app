<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tag;
use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザーを作成
        $this->user = User::factory()->create();

        // テストユーザーでログイン
        $this->actingAs($this->user);
    }

    public function test_basic_creation()
    {
        $response = $this->post('/tags', [
            'name' => 'テストタグ'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/tags');
        $response->assertSessionHas('success', 'タグを作成しました。');

        $this->assertDatabaseHas('tags', [
            'name' => 'テストタグ',
            'user_id' => $this->user->id
        ]);
    }

    public function test_duplicate_name()
    {
        // 既存のタグを作成
        Tag::factory()->create([
            'name' => 'テストタグ',
            'user_id' => $this->user->id
        ]);

        $response = $this->post('/tags', [
            'name' => 'テストタグ'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name']);

        $this->assertDatabaseCount('tags', 1);
    }

    public function test_todo_tag_association()
    {
        // タグとTodoを作成
        $tag = Tag::factory()->create([
            'user_id' => $this->user->id
        ]);

        $todo = Todo::factory()->create([
            'user_id' => $this->user->id
        ]);

        // TodoとTagを関連付け
        $response = $this->patchJson("/todos/{$todo->id}/tags", [
            'tags' => [$tag->id],
            'last_updated' => $todo->updated_at->toISOString()
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tag_todo', [
            'todo_id' => $todo->id,
            'tag_id' => $tag->id
        ]);
    }
} 