<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group auth_register_basic
     */
    public function user_can_view_registration_form()
    {
        // 動作記録: GETリクエストで/registerにアクセス
        $response = $this->get('/register');

        // 動作記録の検証: status: 200
        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    /**
     * @test
     * @group auth_register_basic
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

        // 動作記録の検証: response
        $response->assertStatus(302);
        $response->assertRedirect('/todos');

        // 動作記録の検証: database.users.exists
        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com'
        ]);

        // パスワードがハッシュ化されていることを確認
        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));

        // 動作記録の検証: session
        $this->assertTrue(auth()->check());
        $this->assertEquals($user->id, auth()->id());
    }

    /**
     * @test
     * @group auth_register_basic
     */
    public function user_cannot_register_with_invalid_data()
    {
        // 必須項目の欠如
        $response = $this->post('/register', []);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'email', 'password']);

        // 無効なメールアドレス
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);

        // パスワード確認の不一致
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password'
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
    }

    /**
     * @test
     * @group auth_register_basic
     */
    public function user_cannot_register_with_duplicate_email()
    {
        // 既存のユーザーを作成
        User::factory()->create([
            'email' => 'test@example.com'
        ]);

        // 同じメールアドレスで登録を試行
        $response = $this->post('/register', [
            'name' => 'テストユーザー2',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        // バリデーションエラーの検証
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);

        // データベースに保存されていないことを確認
        $this->assertDatabaseMissing('users', [
            'name' => 'テストユーザー2'
        ]);
    }
} 