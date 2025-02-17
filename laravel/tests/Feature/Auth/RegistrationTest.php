<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
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

    public function test_duplicate_email()
    {
        // 既存のユーザーを作成
        User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/register', [
            'name' => 'テストユーザー2',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);

        $this->assertDatabaseMissing('users', [
            'name' => 'テストユーザー2',
            'email' => 'test@example.com',
        ]);
    }
} 