<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TodoFeatureTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $todo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->todo = Todo::factory()->create([
            'user_id' => $this->user->id,
            'title' => '元のタイトル',
            'description' => '元の説明',
            'deadline' => now()->addDays(1),
            'status' => 'pending'
        ]);
    }

    public function test_can_update_todo_title()
    {
        $response = $this->actingAs($this->user)
            ->patchJson("/todos/{$this->todo->id}/title", [
                'title' => '新しいタイトル',
                'last_updated' => $this->todo->updated_at->toISOString()
            ]);

        $response->assertStatus(200)
            ->assertJson(['title' => '新しいタイトル']);

        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'title' => '新しいタイトル'
        ]);
    }

    public function test_can_update_todo_description()
    {
        $response = $this->actingAs($this->user)
            ->patchJson("/todos/{$this->todo->id}/description", [
                'description' => '新しい説明',
                'last_updated' => $this->todo->updated_at->toISOString()
            ]);

        $response->assertStatus(200)
            ->assertJson(['description' => '新しい説明']);

        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'description' => '新しい説明'
        ]);
    }

    public function test_can_update_todo_deadline()
    {
        $newDeadline = now()->addDays(7)->format('Y-m-d');
        
        $response = $this->actingAs($this->user)
            ->patchJson("/todos/{$this->todo->id}/deadline", [
                'deadline' => $newDeadline,
                'last_updated' => $this->todo->updated_at->toISOString()
            ]);

        $response->assertStatus(200);
        $responseData = $response->json();
        $this->assertEquals(
            now()->addDays(7)->format('Y-m-d'),
            \Carbon\Carbon::parse($responseData['deadline'])->format('Y-m-d')
        );

        $this->assertDatabaseHas('todos', [
            'id' => $this->todo->id,
            'deadline' => $newDeadline
        ]);
    }

    public function test_cannot_update_todo_of_other_user()
    {
        $otherUser = User::factory()->create();
        
        $response = $this->actingAs($otherUser)
            ->patchJson("/todos/{$this->todo->id}/title", [
                'title' => '新しいタイトル',
                'last_updated' => $this->todo->updated_at->toISOString()
            ]);

        $response->assertStatus(403);
    }

    public function test_cannot_update_with_invalid_data()
    {
        $response = $this->actingAs($this->user)
            ->patchJson("/todos/{$this->todo->id}/title", [
                'title' => '',
                'last_updated' => $this->todo->updated_at->toISOString()
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_update_with_stale_data()
    {
        $this->todo->update(['title' => '更新済み']);
        
        $response = $this->actingAs($this->user)
            ->patchJson("/todos/{$this->todo->id}/title", [
                'title' => '新タイトル',
                'last_updated' => now()->subMinute()->toISOString()
            ]);

        $response->assertStatus(409);
    }
} 