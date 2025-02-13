<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_registration()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/todos');

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test2@example.com',
        ]);

        $this->assertTrue(auth()->check());
    }

    public function test_duplicate_email_registration()
    {
        // 既存のユーザーを作成
        User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $response = $this->post('/register', [
            'name' => 'テストユーザー2',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
        
        $this->assertDatabaseCount('users', 1);
        $this->assertFalse(auth()->check());
    }
} 