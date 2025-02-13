<?php

namespace Tests\Unit\Policies;

use App\Models\Todo;
use App\Models\User;
use App\Policies\TodoPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoPolicyTest extends TestCase
{
    use RefreshDatabase;

    private TodoPolicy $policy;
    private User $user;
    private Todo $todo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TodoPolicy();

        // テストユーザーの作成
        $this->user = User::factory()->create();

        // テスト用のTodoを作成
        $this->todo = Todo::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'テストタスク',
            'description' => 'これはテストタスクです',
            'status' => 'pending',
            'deadline' => '2024-03-21'
        ]);
    }

    /**
     * @test
     * @group todo_policy_update
     */
    public function update_allows_todo_owner()
    {
        $result = $this->policy->update($this->user, $this->todo);

        $this->assertTrue($result);
    }

    /**
     * @test
     * @group todo_policy_update
     */
    public function update_denies_non_owner()
    {
        // 別のユーザーを作成
        $otherUser = User::factory()->create();

        $result = $this->policy->update($otherUser, $this->todo);

        $this->assertFalse($result);
    }

    /**
     * @test
     * @group todo_policy_update
     */
    public function view_allows_todo_owner()
    {
        $result = $this->policy->view($this->user, $this->todo);

        $this->assertTrue($result);
    }

    /**
     * @test
     * @group todo_policy_update
     */
    public function view_denies_non_owner()
    {
        // 別のユーザーを作成
        $otherUser = User::factory()->create();

        $result = $this->policy->view($otherUser, $this->todo);

        $this->assertFalse($result);
    }

    /**
     * @test
     * @group todo_policy_update
     */
    public function delete_allows_todo_owner()
    {
        $result = $this->policy->delete($this->user, $this->todo);

        $this->assertTrue($result);
    }

    /**
     * @test
     * @group todo_policy_update
     */
    public function delete_denies_non_owner()
    {
        // 別のユーザーを作成
        $otherUser = User::factory()->create();

        $result = $this->policy->delete($otherUser, $this->todo);

        $this->assertFalse($result);
    }
} 