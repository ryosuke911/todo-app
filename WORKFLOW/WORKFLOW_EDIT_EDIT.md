# インライン編集機能実装ワークフロー（TDDアプローチ）

## 1. 影響範囲の分析

### 直接的な修正が必要なファイル
1. **コントローラー**
   - `app/Http/Controllers/TodoController.php`
     - 各要素の更新用エンドポイントの追加
     - バリデーション処理の追加

2. **ビュー**
   - `resources/views/todos/show.blade.php`
     - インライン編集UI実装
     - JavaScriptイベントハンドラーの追加

3. **ルーティング**
   - `routes/web.php`
     - 新規エンドポイントの追加

### 考慮すべき関連ファイル
1. **モデル**
   - `app/Models/Todo.php`
     - バリデーションルールの確認
     - 属性キャストの確認

2. **サービス**
   - `app/Services/TodoService.php`
     - 更新処理のメソッド追加または修正

3. **ポリシー**
   - `app/Policies/TodoPolicy.php`
     - 更新権限の確認

4. **レイアウト**
   - `resources/views/layouts/app.blade.php`
     - 必要なJavaScriptライブラリの読み込み
     - カレンダーピッカーのスタイル

## 2. テスト駆動開発フロー

### フェーズ1: 機能テストの作成
1. **TodoFeatureTest の作成**
   ```php
   class TodoFeatureTest extends TestCase
   {
       use RefreshDatabase;

       private $user;
       private $todo;

       protected function setUp(): void
       {
           parent::setUp();
           $this->user = User::factory()->create();
           $this->todo = Todo::factory()->create(['user_id' => $this->user->id]);
       }
   }
   ```
   進捗報告: "テストの基本セットアップ完了"

2. **タイトル編集のテスト**
   ```php
   public function test_can_update_todo_title()
   {
       $response = $this->actingAs($this->user)
           ->putJson("/todos/{$this->todo->id}/update-title", [
               'title' => '新しいタイトル',
               'last_updated' => $this->todo->updated_at
           ]);

       $response->assertStatus(200)
           ->assertJson(['title' => '新しいタイトル']);
   }
   ```
   進捗報告: "タイトル編集テスト作成完了"

3. **説明編集のテスト**
   ```php
   public function test_can_update_todo_description()
   {
       $response = $this->actingAs($this->user)
           ->putJson("/todos/{$this->todo->id}/update-description", [
               'description' => '新しい説明',
               'last_updated' => $this->todo->updated_at
           ]);

       $response->assertStatus(200)
           ->assertJson(['description' => '新しい説明']);
   }
   ```
   進捗報告: "説明編集テスト作成完了"

4. **期限編集のテスト**
   ```php
   public function test_can_update_todo_deadline()
   {
       $newDeadline = now()->addDays(7)->format('Y-m-d');
       
       $response = $this->actingAs($this->user)
           ->putJson("/todos/{$this->todo->id}/update-deadline", [
               'deadline' => $newDeadline,
               'last_updated' => $this->todo->updated_at
           ]);

       $response->assertStatus(200)
           ->assertJson(['deadline' => $newDeadline]);
   }
   ```
   進捗報告: "期限編集テスト作成完了"

### フェーズ2: ユニットテストの作成
1. **TodoServiceTest の作成**
   ```php
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
           $this->todo = Todo::factory()->create(['user_id' => $this->user->id]);
       }
   }
   ```
   進捗報告: "サービステストの基本セットアップ完了"

2. **個別更新メソッドのテスト**
   ```php
   public function test_can_update_todo_title_in_service()
   {
       $result = $this->todoService->updateTitle($this->todo, '新しいタイトル');
       $this->assertTrue($result);
       $this->assertEquals('新しいタイトル', $this->todo->fresh()->title);
   }
   ```
   進捗報告: "サービスメソッドテスト作成完了"

### フェーズ3: バックエンド実装
1. **ルート定義の追加** (`routes/web.php`)
   - テストが失敗することを確認
   - ルートを追加して再テスト
   進捗報告: "ルート定義の追加完了"

2. **サービスメソッドの実装** (`app/Services/TodoService.php`)
   - テストが失敗することを確認
   - メソッドを実装して再テスト
   進捗報告: "サービスメソッドの実装完了"

3. **コントローラーメソッドの実装** (`app/Http/Controllers/TodoController.php`)
   - テストが失敗することを確認
   - メソッドを実装して再テスト
   進捗報告: "コントローラーメソッドの実装完了"

### フェーズ4: フロントエンドテストの作成
1. **Duskテストの作成**
   ```php
   class TodoInlineEditTest extends DuskTestCase
   {
       public function test_can_edit_todo_title_inline()
       {
           $this->browse(function (Browser $browser) {
               $browser->loginAs($this->user)
                   ->visit("/todos/{$this->todo->id}")
                   ->click('.edit-title-button')
                   ->type('.title-input', '新しいタイトル')
                   ->keys('.title-input', '{enter}')
                   ->waitForText('新しいタイトル')
                   ->assertSee('新しいタイトル');
           });
       }
   }
   ```
   進捗報告: "Duskテストの作成完了"

### フェーズ5: フロントエンド実装
1. **UIコンポーネントの実装**
   - テストが失敗することを確認
   - コンポーネントを実装して再テスト
   進捗報告: "UIコンポーネントの実装完了"

2. **JavaScriptの実装**
   - テストが失敗することを確認
   - 機能を実装して再テスト
   進捗報告: "JavaScript実装完了"

### フェーズ6: エラーケースのテスト追加
1. **バリデーションエラーのテスト**
   ```php
   public function test_cannot_update_with_invalid_data()
   {
       $response = $this->actingAs($this->user)
           ->putJson("/todos/{$this->todo->id}/update-title", [
               'title' => '',
               'last_updated' => $this->todo->updated_at
           ]);

       $response->assertStatus(422);
   }
   ```
   進捗報告: "エラーケーステスト追加完了"

2. **同時編集のテスト**
   ```php
   public function test_cannot_update_with_stale_data()
   {
       $this->todo->update(['title' => '更新済み']);
       
       $response = $this->actingAs($this->user)
           ->putJson("/todos/{$this->todo->id}/update-title", [
               'title' => '新タイトル',
               'last_updated' => now()->subMinute()
           ]);

       $response->assertStatus(409);
   }
   ```
   進捗報告: "同時編集テスト追加完了"

### フェーズ7: 統合テスト
1. **E2Eテストの実行**
   - 全機能の結合テスト
   - エッジケースの確認
   進捗報告: "E2Eテスト完了"

2. **パフォーマンステスト**
   - 負荷テストの実行
   - レスポンス時間の計測
   進捗報告: "パフォーマンステスト完了"

## 3. 関連ファイル確認フロー

### 直接的な影響の確認
1. **モデル確認** (`app/Models/Todo.php`)
   - [ ] `$fillable` 配列に必要な属性が含まれているか
   - [ ] `$casts` の設定が適切か（特に `deadline` の日付キャスト）
   - [ ] バリデーションルールの整合性
   - [ ] イベントリスナーへの影響確認
   進捗報告: "モデル確認完了"

2. **サービス確認** (`app/Services/TodoService.php`)
   - [ ] 既存メソッドとの整合性
   - [ ] トランザクション処理の必要性
   - [ ] イベント発火タイミングの確認
   - [ ] キャッシュ制御の確認
   進捗報告: "サービス確認完了"

3. **ポリシー確認** (`app/Policies/TodoPolicy.php`)
   - [ ] 部分更新の権限設定
   - [ ] 同時編集の制御ポリシー
   - [ ] ユーザー権限の粒度確認
   進捗報告: "ポリシー確認完了"

### 間接的な影響の確認
1. **関連コンポーネント**
   - [ ] サイドバーの状態表示
   - [ ] ダッシュボードの統計情報
   - [ ] タグ関連の表示
   進捗報告: "関連コンポーネント確認完了"

2. **JavaScript依存関係**
   - [ ] Alpine.jsの機能競合
   - [ ] イベントバブリングの影響
   - [ ] グローバル変数の衝突
   進捗報告: "JavaScript依存関係確認完了"

3. **スタイル定義**
   - [ ] Tailwindクラスの競合
   - [ ] レスポンシブデザインへの影響
   - [ ] アニメーション定義の重複
   進捗報告: "スタイル定義確認完了"

### パフォーマンス影響の確認
1. **データベース**
   - [ ] インデックスの有効性
   - [ ] N+1問題の発生可能性
   - [ ] クエリの最適化
   進捗報告: "データベース影響確認完了"

2. **キャッシュ**
   - [ ] キャッシュ制御の整合性
   - [ ] キャッシュクリアタイミング
   - [ ] 部分キャッシュの検討
   進捗報告: "キャッシュ影響確認完了"

3. **フロントエンド**
   - [ ] バンドルサイズへの影響
   - [ ] 非同期処理の最適化
   - [ ] メモリリークの可能性
   進捗報告: "フロントエンド影響確認完了"

### セキュリティの確認
1. **入力検証**
   - [ ] XSS対策の網羅性
   - [ ] SQLインジェクション対策
   - [ ] CSRF対策の確認
   進捗報告: "入力検証確認完了"

2. **認可制御**
   - [ ] ルートミドルウェアの確認
   - [ ] ポリシー適用の確認
   - [ ] APIエンドポイントの保護
   進捗報告: "認可制御確認完了"

### ドキュメント更新の確認
1. **API仕様書**
   - [ ] 新規エンドポイントの記載
   - [ ] レスポンス形式の記載
   - [ ] エラーコードの記載
   進捗報告: "API仕様書更新確認完了"

2. **開発者ドキュメント**
   - [ ] インライン編集の実装説明
   - [ ] イベントハンドリングの説明
   - [ ] トラブルシューティングガイド
   進捗報告: "開発者ドキュメント更新確認完了"

## 4. 最終確認チェックリスト
- [ ] すべてのテストが成功しているか
- [ ] テストカバレッジは十分か
- [ ] エッジケースは網羅されているか
- [ ] パフォーマンス要件を満たしているか
- [ ] セキュリティテストは完了しているか
- [ ] ドキュメントは更新されているか

## 5. ロールバック計画
1. 各機能のロールバックテストの準備
2. データ整合性を確保するマイグレーションの準備
3. 切り戻し手順の文書化 