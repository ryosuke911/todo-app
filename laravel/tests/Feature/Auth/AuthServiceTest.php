<?php

namespace Tests\Feature\Auth;

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

    /**
     * @test
     * @group auth_login_basic
     */
    public function login_authenticates_user_with_valid_credentials()
    {
        // テストユーザーの作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        // 動作記録に基づくテストデータ
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        // 実行
        $result = $this->authService->login($credentials);

        // アサーション
        $this->assertTrue($result);
        $this->assertTrue(auth()->check());
        $this->assertEquals($user->id, auth()->id());
    }

    /**
     * @test
     * @group auth_login_basic
     */
    public function login_fails_with_invalid_credentials()
    {
        // テストユーザーの作成
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        // 無効なパスワードでテスト
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'wrong_password'
        ];

        // 実行
        $result = $this->authService->login($credentials);

        // アサーション
        $this->assertFalse($result);
        $this->assertFalse(auth()->check());
    }

    /**
     * @test
     * @group auth_login_basic
     */
    public function login_fails_with_non_existent_user()
    {
        // 存在しないユーザーでテスト
        $credentials = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ];

        // 実行
        $result = $this->authService->login($credentials);

        // アサーション
        $this->assertFalse($result);
        $this->assertFalse(auth()->check());
    }

    /**
     * @test
     * @group auth_logout_basic
     */
    public function logout_terminates_user_session()
    {
        // テストユーザーの作成と認証
        $user = User::factory()->create();
        auth()->login($user);
        $this->assertTrue(auth()->check());

        // 実行
        $this->authService->logout();

        // アサーション
        $this->assertFalse(auth()->check());
        $this->assertNull(auth()->user());
    }

    /**
     * @test
     * @group auth_logout_basic
     */
    public function logout_works_for_unauthenticated_user()
    {
        // 未認証状態を確認
        $this->assertFalse(auth()->check());

        // 実行
        $this->authService->logout();

        // アサーション（エラーが発生しないことを確認）
        $this->assertFalse(auth()->check());
        $this->assertNull(auth()->user());
    }
} 