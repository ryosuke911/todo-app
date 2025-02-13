<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group auth_service_register
     */
    public function user_can_view_registration_form()
    {
        // 動作記録: GETリクエストで/registerにアクセス
        $response = $this->get('/register');

        // 動作記録の検証: status: 200
        $response->assertStatus(200);
    }

    /**
     * @test
     * @group auth_service_register
     */
    public function user_can_register_with_valid_data()
    {
        // 動作記録に基づくテストデータ
        $userData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        // 動作記録: POSTリクエストで/registerにフォーム送信
        $response = $this->post('/register', $userData);

        // 動作記録の検証
        // - response.status: 302
        // - response.redirect: "/todos"
        $response->assertStatus(302);
        $response->assertRedirect('/todos');

        // 動作記録の検証: database.users.exists
        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com'
        ]);

        // 動作記録の検証: session.authenticated: true
        $this->assertTrue(auth()->check());
    }

    /**
     * @test
     * @group auth_service_register
     */
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