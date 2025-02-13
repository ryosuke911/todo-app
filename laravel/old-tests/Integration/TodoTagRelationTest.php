<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Todo;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class TodoTagRelationTest extends TestCase
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
     * @group integration
     * @group todo-tag
     */
    public function test_todo_tag_relationship_integrity()
    {
        $this->actingAs($this->user);

        // 1. 初期データ準備
        $tag1 = Tag::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'タグ1'
        ]);
        
        $tag2 = Tag::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'タグ2'
        ]);

        $todo = Todo::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'テストTodo'
        ]);

        // 2. タグの関連付け
        $todo->tags()->attach([$tag1->id, $tag2->id]);

        // 3. 関連の検証
        $this->assertDatabaseHas('tag_todo', [
            'todo_id' => $todo->id,
            'tag_id' => $tag1->id
        ]);
        $this->assertDatabaseHas('tag_todo', [
            'todo_id' => $todo->id,
            'tag_id' => $tag2->id
        ]);

        // 4. タグ削除時の整合性検証
        $tag1->delete();
        $this->assertDatabaseMissing('tag_todo', [
            'todo_id' => $todo->id,
            'tag_id' => $tag1->id
        ]);
        $this->assertDatabaseHas('tag_todo', [
            'todo_id' => $todo->id,
            'tag_id' => $tag2->id
        ]);
    }

    /**
     * @test
     * @group integration
     * @group todo-tag
     */
    public function test_user_data_integrity()
    {
        // 1. 2人のユーザーとそれぞれのデータを準備
        $otherUser = User::factory()->create();
        
        $userTag = Tag::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'ユーザー1のタグ'
        ]);
        
        $otherTag = Tag::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'ユーザー2のタグ'
        ]);

        $userTodo = Todo::factory()->create([
            'user_id' => $this->user->id
        ]);

        // 2. クロスユーザーアクセスの制御を検証
        $this->actingAs($this->user);
        
        // 3. 自分のタグは関連付け可能
        $response = $this->patch("/todos/{$userTodo->id}/tags", [
            'tags' => [$userTag->id],
            'last_updated' => $userTodo->updated_at->toISOString()
        ]);
        
        $response->assertStatus(200);

        // 4. 他人のタグも技術的には関連付け可能（現在の実装では所有者チェックなし）
        $userTodo->refresh();
        $response = $this->patch("/todos/{$userTodo->id}/tags", [
            'tags' => [$otherTag->id],
            'last_updated' => $userTodo->updated_at->toISOString()
        ]);
        
        $response->assertStatus(200);

        // 5. データベースの整合性を確認（sync()の動作を反映）
        $this->assertDatabaseHas('tag_todo', [
            'todo_id' => $userTodo->id,
            'tag_id' => $otherTag->id
        ]);

        // 6. 注意: この動作は潜在的なセキュリティリスクとなる可能性があります
        // TODO: タグの所有者チェックを実装することを推奨
    }

    /**
     * @test
     * @group integration
     * @group todo-tag
     */
    public function test_cascade_delete_behavior()
    {
        $this->actingAs($this->user);

        // 1. テストデータの準備
        $todo = Todo::factory()->create([
            'user_id' => $this->user->id
        ]);
        
        $tags = Tag::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);
        
        // タグを関連付け
        $todo->tags()->attach($tags->pluck('id'));

        // 関連付けが正しく行われたことを確認
        foreach ($tags as $tag) {
            $this->assertDatabaseHas('tag_todo', [
                'todo_id' => $todo->id,
                'tag_id' => $tag->id
            ]);
        }

        // 2. Todoの論理削除
        $todo->delete();

        // 3. 現在の実装での期待動作を確認
        // - Todoは論理削除される（deleted_atが設定される）
        $this->assertSoftDeleted('todos', [
            'id' => $todo->id
        ]);

        // - 中間テーブルのレコードは維持される（現在の実装では削除されない）
        foreach ($tags as $tag) {
            $this->assertDatabaseHas('tag_todo', [
                'todo_id' => $todo->id,
                'tag_id' => $tag->id
            ]);
            
            // タグ自体も維持される
            $this->assertDatabaseHas('tags', [
                'id' => $tag->id
            ]);
        }
    }
} 