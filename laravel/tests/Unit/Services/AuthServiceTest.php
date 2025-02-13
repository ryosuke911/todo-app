<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    /**
     * @test
     * @group auth_service_register
     */
    public function register_creates_new_user_with_valid_data()
    {
        // 動作記録に基づくテストデータ
        $userData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        // 実行
        $user = $this->authService->register($userData);

        // アサーション
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertTrue(Hash::check($userData['password'], $user->password));
        
        // データベースの検証
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email']
        ]);
    }

    /**
     * @test
     * @group auth_service_register
     */
    public function register_creates_user_with_correct_fillable_attributes()
    {
        // テストデータ（不正なデータを含む）
        $userData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'admin', // fillableではない属性
            'status' => 'active' // fillableではない属性
        ];

        // 実行
        $user = $this->authService->register($userData);

        // アサーション
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertNull($user->role); // fillableではない属性は保存されないことを確認
        $this->assertNull($user->status); // fillableではない属性は保存されないことを確認
    }

    /**
     * @test
     * @group auth_service_register
     */
    public function register_hashes_password_correctly()
    {
        // テストデータ
        $userData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        // 実行
        $user = $this->authService->register($userData);

        // パスワードがハッシュ化されていることを確認
        $this->assertNotEquals($userData['password'], $user->password);
        $this->assertTrue(Hash::check($userData['password'], $user->password));
    }
} 