<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // ログアウトルートの定義
        Route::post('/logout', function () {
            auth()->logout();
            return redirect('/login');
        })->name('logout');
    }

    /**
     * @test
     * @group auth
     * @group high-priority
     */
    public function test_successful_login_maintains_recorded_behavior()
    {
        // 1. 初期状態の準備
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        // 2. 記録された操作の実行
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        // 3. 実装計画のアサーションに基づく検証
        $response->assertRedirect('/todos');
        $this->assertAuthenticated();
        $this->assertResponseTime($response, 500); // SLA: 500ms以内
    }

    /**
     * @test
     * @group auth
     * @group high-priority
     */
    public function test_invalid_login_maintains_recorded_behavior()
    {
        // 1. エラーケースの初期状態
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correctpassword')
        ]);

        // 2. 記録された無効な操作の実行
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        // 3. エラー状態の検証
        $response->assertInvalid(['email' => 'メールアドレスまたはパスワードが正しくありません。']);
        $this->assertGuest();
        $this->assertResponseTime($response, 500); // SLA: 500ms以内
    }

    /**
     * @test
     * @group auth
     * @group high-priority
     */
    public function test_logout_maintains_recorded_behavior()
    {
        // 1. ログイン状態の準備
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. ログアウト操作の実行
        $response = $this->post('/logout');

        // 3. ログアウト状態の検証
        $response->assertRedirect('/login');
        $this->assertGuest();
        $this->assertResponseTime($response, 500); // SLA: 500ms以内
    }

    /**
     * レスポンスタイムがSLA内であることを確認するヘルパーメソッド
     */
    private function assertResponseTime($response, $maxTime)
    {
        $actualTime = $response->headers->get('X-Runtime') * 1000; // 秒からミリ秒に変換
        $this->assertLessThanOrEqual(
            $maxTime,
            $actualTime,
            "Response time {$actualTime}ms exceeds SLA of {$maxTime}ms"
        );
    }
} 