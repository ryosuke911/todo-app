<?php

namespace Tests\Feature\Tag;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tag;
use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TagManagementTest extends TestCase
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
     * @group tag
     * @group medium-priority
     */
    public function test_tag_deletion_maintains_recorded_behavior()
    {
        $this->actingAs($this->user);

        // 1. タグとTodoの準備
        $tag = Tag::factory()->create([
            'user_id' => $this->user->id,
            'name' => '削除予定のタグ'
        ]);
        
        $todo = Todo::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);
        $todo->tags()->attach($tag->id);

        // 2. タグ削除の実行
        $response = $this->delete("/tags/{$tag->id}");

        // 3. 削除の検証
        $response->assertRedirect('/tags');
        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id
        ]);

        // 4. 関連するTodoのタグ関連が削除されていることを確認
        $this->assertDatabaseMissing('tag_todo', [
            'tag_id' => $tag->id,
            'todo_id' => $todo->id
        ]);

        // 5. パフォーマンス検証
        $this->assertResponseTime($response, 800); // SLA: 800ms以内
    }

    /**
     * @test
     * @group tag
     * @group medium-priority
     */
    public function test_tag_update_maintains_consistency()
    {
        $this->actingAs($this->user);

        // 1. タグとTodoの準備
        $tag = Tag::factory()->create([
            'user_id' => $this->user->id,
            'name' => '更新前のタグ名'
        ]);
        
        $todo = Todo::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'title' => 'テストTodo'
        ]);
        $todo->tags()->attach($tag->id);

        // 2. タグ更新の実行
        $response = $this->patch("/tags/{$tag->id}", [
            'name' => '更新後のタグ名'
        ]);

        // 3. 更新の検証
        $response->assertRedirect('/tags');
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '更新後のタグ名'
        ]);

        // 4. タグ一覧で更新を確認
        $listResponse = $this->get('/tags');
        $listResponse->assertSee('更新後のタグ名');

        // 5. パフォーマンス検証
        $this->assertResponseTime($response, 800); // SLA: 800ms以内
    }

    /**
     * レスポンスタイムがSLA内であることを確認するヘルパーメソッド
     */
    private function assertResponseTime($response, $maxTime)
    {
        $actualTime = $response->headers->get('X-Runtime') * 1000; // 秒からミリ秒に変換
        $this->assertLessThanOrEqual(
            $maxTime,
            $actualTime,
            "Response time {$actualTime}ms exceeds SLA of {$maxTime}ms"
        );
    }
} 