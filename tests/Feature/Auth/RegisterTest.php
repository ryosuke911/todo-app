<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_register_with_duplicate_email()
    {
        // 既存ユーザーを作成
        User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $userData = [
            'name' => 'テストユーザー2',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        // APIリクエストとしてテスト
        $response = $this->withHeaders([
            'Accept' => 'application/json'
        ])->post('/register', $userData);

        // 動作記録の検証
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);

        // データベース検証
        $this->assertDatabaseMissing('users', [
            'name' => 'テストユーザー2',
            'email' => 'test@example.com'
        ]);
    }
}