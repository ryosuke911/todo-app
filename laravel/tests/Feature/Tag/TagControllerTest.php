<?php

namespace Tests\Feature\Tag;

use App\Models\User;
use App\Models\Tag;
use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function user_can_create_tag()
    {
        // 動作記録: basic
        $response = $this->actingAs($this->user)
            ->post('/tags', [
                'name' => 'テストタグ'
            ]);

        // レスポンスの検証
        $response->assertStatus(302);
        $response->assertRedirect('/tags');

        // データベースの検証
        $this->assertDatabaseHas('tags', [
            'name' => 'テストタグ',
            'user_id' => $this->user->id
        ]);
    }

    #[Test]
    public function user_cannot_create_duplicate_tag()
    {
        // 既存のタグを作成
        Tag::factory()->create([
            'name' => 'テストタグ',
            'user_id' => $this->user->id
        ]);

        // 動作記録: duplicate_name
        $response = $this->actingAs($this->user)
            ->postJson('/tags', [  // JSONリクエストに変更
                'name' => 'テストタグ'  // 既存のタグ名
            ]);

        // レスポンスの検証（動作記録通り）
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'name' => 'The name has already been taken.'
        ]);

        // データベースの検証（レコード数が変わっていないことを確認）
        $this->assertDatabaseCount('tags', 1);
    }

    #[Test]
    public function todo_can_be_associated_with_tags()
    {
        // テストデータの準備
        $todo = Todo::factory()->create([
            'user_id' => $this->user->id
        ]);
        $tag = Tag::factory()->create([
            'user_id' => $this->user->id
        ]);

        // タグの関連付けリクエスト
        $response = $this->actingAs($this->user)
            ->patchJson("/todos/{$todo->id}/tags", [
                'tags' => [$tag->id],
                'last_updated' => $todo->updated_at->format('Y-m-d\TH:i:s.u\Z')
            ]);

        // レスポンスの検証
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'tags',
            'updated_at'
        ]);

        // データベースの検証
        $this->assertDatabaseHas('tag_todo', [
            'todo_id' => $todo->id,
            'tag_id' => $tag->id
        ]);
    }
} 