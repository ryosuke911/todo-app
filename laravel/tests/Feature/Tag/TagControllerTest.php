<?php

namespace Tests\Feature\Tag;

use App\Models\User;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザーを作成
        $this->user = User::factory()->create();
    }

    public function test_basic_creation()
    {
        // 認証済みユーザーとしてリクエスト
        $response = $this->actingAs($this->user)
            ->post('/tags', [
                'name' => 'テストタグ'
            ]);

        $response->assertStatus(302);
        $response->assertRedirect('/tags');
        $response->assertSessionHas('success', 'タグを作成しました。');

        // データベースの変更を確認
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

        // 同じ名前でタグを作成
        $response = $this->actingAs($this->user)
            ->post('/tags', [
                'name' => 'テストタグ'
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name']);

        // データベースに重複レコードが作成されていないことを確認
        $this->assertDatabaseCount('tags', 1);
    }

    public function test_unauthorized()
    {
        $response = $this->post('/tags', [
            'name' => 'テストタグ'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_redirect_after_creation()
    {
        // リダイレクト先を指定してタグを作成
        $response = $this->actingAs($this->user)
            ->post('/tags', [
                'name' => 'テストタグ',
                'redirect' => 'todos.create'
            ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('todos.create'));
        $response->assertSessionHas('success', 'タグを作成しました。');

        // データベースの変更を確認
        $this->assertDatabaseHas('tags', [
            'name' => 'テストタグ',
            'user_id' => $this->user->id
        ]);
    }
} 