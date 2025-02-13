<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Todo;
use App\Services\TodoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TodoServiceTest extends TestCase
{
    use RefreshDatabase;

    private $todoService;
    private $user;
    private $todo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->todoService = app(TodoService::class);
        $this->user = User::factory()->create();
        $this->todo = Todo::factory()->create([
            'user_id' => $this->user->id,
            'title' => '元のタイトル',
            'description' => '元の説明',
            'deadline' => now()->addDays(1),
            'status' => 'pending'
        ]);
    }

    public function test_can_update_todo_title_in_service()
    {
        $result = $this->todoService->updateTitle($this->todo, '新しいタイトル');
        
        $this->assertTrue($result);
        $this->assertEquals('新しいタイトル', $this->todo->fresh()->title);
    }

    public function test_can_update_todo_description_in_service()
    {
        $result = $this->todoService->updateDescription($this->todo, '新しい説明');
        
        $this->assertTrue($result);
        $this->assertEquals('新しい説明', $this->todo->fresh()->description);
    }

    public function test_can_update_todo_deadline_in_service()
    {
        $newDeadline = now()->addDays(7)->format('Y-m-d');
        $result = $this->todoService->updateDeadline($this->todo, $newDeadline);
        
        $this->assertTrue($result);
        $this->assertEquals($newDeadline, $this->todo->fresh()->deadline->format('Y-m-d'));
    }

    public function test_cannot_update_with_invalid_title()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->todoService->updateTitle($this->todo, '');
    }

    public function test_cannot_update_with_invalid_deadline()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->todoService->updateDeadline($this->todo, now()->subDay()->format('Y-m-d'));
    }
} 