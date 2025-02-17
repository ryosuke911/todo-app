<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_view_registration_form()
    {
        // 動作記録: GETリクエストで/registerにアクセス
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    #[Test]
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

        // レスポンスの検証
        $response->assertStatus(302);
        $response->assertRedirect('/todos');

        // データベースの検証
        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com'
        ]);

        // セッションの検証
        $this->assertTrue(auth()->check());
    }

    #[Test]
    public function user_cannot_register_with_duplicate_email()
    {
        // 既存のユーザーを作成
        User::factory()->create([
            'email' => 'test@example.com'
        ]);

        // 動作記録に基づくテストデータ
        $userData = [
            'name' => 'テストユーザー2',
            'email' => 'test@example.com', // 重複するメールアドレス
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        // 登録を試行
        $response = $this->post('/register', $userData);

        // バリデーションエラーの検証
        $response->assertSessionHasErrors(['email']);
        
        // データベースに保存されていないことを確認
        $this->assertDatabaseMissing('users', [
            'name' => 'テストユーザー2'
        ]);
    }
} 