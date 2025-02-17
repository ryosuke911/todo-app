<?php

namespace Tests\Feature\Todo;

use App\Models\User;
use App\Models\Todo;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class TodoControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tag $workTag;
    private Tag $personalTag;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザーを作成
        $this->user = User::factory()->create();

        // タグを作成
        $this->workTag = Tag::factory()->create([
            'name' => 'work',
            'user_id' => $this->user->id
        ]);

        $this->personalTag = Tag::factory()->create([
            'name' => 'personal',
            'user_id' => $this->user->id
        ]);
    }

    public function test_filtered_list()
    {
        // テストタスクを作成
        $todo = Todo::factory()->create([
            'title' => 'テストタスク1',
            'description' => 'テスト説明1',
            'status' => 'pending',
            'deadline' => '2024-03-25',
            'user_id' => $this->user->id
        ]);

        // タグを関連付け
        $todo->tags()->attach($this->workTag->id);

        // 認証済みユーザーとしてリクエスト
        $response = $this->actingAs($this->user)
            ->get('/todos?' . http_build_query([
                'filter' => 'not_completed',
                'search' => 'テスト',
                'tag_id' => $this->workTag->id
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('todos.index');
        
        // Todoデータの検証
        $response->assertViewHas('todos', function ($todos) use ($todo) {
            return $todos->total() === 1 &&
                   $todos->perPage() === 10 &&
                   $todos->first()->id === $todo->id &&
                   $todos->first()->title === 'テストタスク1';
        });

        // タグデータの検証
        $response->assertViewHas('tags', function ($tags) {
            return $tags->count() === 2 &&
                   $tags->contains('name', 'work') &&
                   $tags->contains('name', 'personal');
        });
    }

    public function test_unauthorized()
    {
        $response = $this->get('/todos');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_status_update()
    {
        // テストタスクを作成
        $todo = Todo::factory()->create([
            'title' => 'テストタスク1',
            'status' => 'pending',
            'user_id' => $this->user->id,
            'updated_at' => now()->subMinute() // 1分前の時刻を設定
        ]);

        // 現在のタイムスタンプを取得
        $currentTimestamp = $todo->updated_at->toISOString();

        // 認証済みユーザーとしてリクエスト
        $response = $this->actingAs($this->user)
            ->patchJson("/todos/{$todo->id}/status", [
                'status' => 'in_progress',
                'last_updated' => $currentTimestamp
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'updated_at'
        ]);

        $responseData = $response->json();
        
        // レスポンスの内容を検証
        $this->assertEquals('in_progress', $responseData['status']);
        $this->assertNotEquals($currentTimestamp, $responseData['updated_at']);

        // データベースの更新を確認
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'status' => 'in_progress'
        ]);

        // データベースのタイムスタンプが更新されていることを確認
        $updatedTodo = $todo->fresh();
        $this->assertEquals($responseData['updated_at'], $updatedTodo->updated_at->toISOString());
    }

    public function test_version_conflict()
    {
        // テストタスクを作成
        $todo = Todo::factory()->create([
            'title' => 'テストタスク1',
            'status' => 'pending',
            'user_id' => $this->user->id,
            'updated_at' => now() // 現在時刻を設定
        ]);

        // 古いタイムスタンプを設定（1時間前）
        $oldTimestamp = $todo->updated_at->copy()->subHour()->toISOString();

        // 認証済みユーザーとしてリクエスト
        $response = $this->actingAs($this->user)
            ->patchJson("/todos/{$todo->id}/status", [
                'status' => 'completed',
                'last_updated' => $oldTimestamp
            ]);

        $response->assertStatus(409);
        $response->assertJsonStructure([
            'message',
            'updated_at'
        ]);

        // データベースが更新されていないことを確認
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'status' => 'pending'
        ]);

        // エラーメッセージの確認
        $response->assertJson([
            'message' => 'データが古くなっています。再度読み込んでください。'
        ]);
    }
} 