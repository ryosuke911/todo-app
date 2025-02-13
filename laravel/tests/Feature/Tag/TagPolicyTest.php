<?php

namespace Tests\Feature\Tag;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagPolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tag $tag;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザーの作成
        $this->user = User::factory()->create();

        // テスト用のタグを作成
        $this->tag = Tag::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'テストタグ'
        ]);
    }

    /**
     * @test
     * @group tag_policy_base
     */
    public function test_owner_can_create_tag()
    {
        $this->actingAs($this->user);

        // データベースをクリーンな状態にする
        Tag::where('name', 'テストタグ')->delete();

        // テストの前に同名のタグが存在しないことを確認
        $this->assertDatabaseMissing('tags', [
            'name' => 'テストタグ',
            'user_id' => $this->user->id
        ]);

        $response = $this->post('/tags', [
            'name' => 'テストタグ'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/tags');

        $this->assertDatabaseHas('tags', [
            'name' => 'テストタグ',
            'user_id' => $this->user->id
        ]);
    }

    /**
     * @test
     * @group tag_policy_base
     */
    public function owner_can_update_tag()
    {
        // ユーザーとして認証
        $this->actingAs($this->user);

        // 動作記録: PUTリクエストで/tags/{id}にタグを更新
        $response = $this->put('/tags/' . $this->tag->id, [
            'name' => '仕事'
        ]);

        // レスポンスの検証
        $response->assertStatus(302);
        $response->assertRedirect('/tags');

        // データベースの検証
        $this->assertDatabaseHas('tags', [
            'id' => $this->tag->id,
            'name' => '仕事',
            'user_id' => $this->user->id
        ]);
    }

    /**
     * @test
     * @group tag_policy_base
     */
    public function owner_can_delete_tag()
    {
        // ユーザーとして認証
        $this->actingAs($this->user);

        // 動作記録: DELETEリクエストで/tags/{id}にタグを削除
        $response = $this->delete('/tags/' . $this->tag->id);

        // レスポンスの検証
        $response->assertStatus(302);
        $response->assertRedirect('/tags');

        // データベースの検証
        $this->assertDatabaseMissing('tags', [
            'id' => $this->tag->id
        ]);
    }

    /**
     * @test
     * @group tag_policy_base
     */
    public function unauthorized_user_cannot_access_tags()
    {
        // 未認証状態でのアクセス試行
        $responses = [
            $this->get('/tags/create'),
            $this->post('/tags', ['name' => 'テストタグ']),
            $this->put('/tags/' . $this->tag->id, ['name' => '仕事']),
            $this->delete('/tags/' . $this->tag->id)
        ];

        // すべてのレスポンスがログインページにリダイレクトされることを確認
        foreach ($responses as $response) {
            $response->assertStatus(302);
            $response->assertRedirect('/login');
        }

        // データベースが変更されていないことを確認
        $this->assertDatabaseHas('tags', [
            'id' => $this->tag->id,
            'name' => 'テストタグ'
        ]);
    }
} 