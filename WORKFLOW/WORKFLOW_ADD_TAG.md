# タグ機能実装計画

## 0. YAMLファイル更新

タグ機能の実装に必要な構造は `structure.yaml` に追加します。
追加する構造の概要：

1. モデル
   - Tag.php（タグモデル）

2. コントローラー
   - TagController.php（タグ管理の制御）

3. ポリシー
   - TagPolicy.php（タグ操作の認可）

4. ビュー
   - tags/index.blade.php（一覧・管理画面）
   - tags/create.blade.php（作成画面）
   - tags/edit.blade.php（編集画面）

5. マイグレーション
   - create_tags_table.php（タグテーブル）
   - create_todo_tag_table.php（中間テーブル）

詳細な構造と依存関係については `structure.yaml` を参照してください。

## 1. 影響範囲分析

### 1.1 新規作成が必要なファイル
1. モデル
   - `app/Models/Tag.php`

2. コントローラー
   - `app/Http/Controllers/TagController.php`

3. ポリシー
   - `app/Policies/TagPolicy.php`

4. ビュー
   - `resources/views/tags/index.blade.php`
   - `resources/views/tags/create.blade.php`
   - `resources/views/tags/edit.blade.php`

5. マイグレーション
   - `database/migrations/create_tags_table.php`
   - `database/migrations/create_todo_tag_table.php`

### 1.2 修正が必要な既存ファイル
1. モデル
   - `app/Models/Todo.php`
     - タグとの多対多リレーション追加
     - タグ関連のスコープメソッド追加

2. コントローラー
   - `app/Http/Controllers/TodoController.php`
     - タグによるフィルタリング機能追加
     - タグ関連のバリデーション追加

3. ビュー
   - `resources/views/todos/index.blade.php`
     - タグ表示機能追加
     - タグフィルター追加
   - `resources/views/todos/create.blade.php`
     - タグ選択機能追加
   - `resources/views/todos/edit.blade.php`
     - タグ選択機能追加

### 1.3 依存関係の整理
1. 直接的な依存関係
   - Tag → User (所有者)
   - Tag ↔ Todo (多対多)
   - TagPolicy → Tag, User

2. 間接的な依存関係
   - TodoController → Tag (フィルタリング機能)
   - TodoService → Tag (タグ関連の処理)

## 1. データベース設計

### Tagsテーブル
```sql
CREATE TABLE tags (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    user_id bigint unsigned NOT NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Todo_Tagテーブル（中間テーブル）
```sql
CREATE TABLE todo_tag (
    todo_id bigint unsigned NOT NULL,
    tag_id bigint unsigned NOT NULL,
    PRIMARY KEY (todo_id, tag_id),
    FOREIGN KEY (todo_id) REFERENCES todos(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);
```

## 2. モデル作成

### Tagモデル
```php
class Tag extends Model
{
    protected $fillable = ['name', 'user_id'];

    public function todos()
    {
        return $this->belongsToMany(Todo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### Todoモデルの更新
```php
// 既存のTodoモデルに追加
public function tags()
{
    return $this->belongsToMany(Tag::class);
}
```

## 3. コントローラー作成

### TagController
- タグの作成（create）
- タグの一覧表示（index）
- タグの編集（edit）
- タグの削除（delete）
- タグによるTodoのフィルタリング機能

## 4. ビュー実装

### タグ管理画面
- タグの一覧表示
- タグの作成フォーム
- タグの編集・削除機能

### Todo作成・編集画面の更新
- タグ選択機能の追加（複数選択可能）
- 既存のタグからの選択
- 新規タグの作成オプション

### Todo一覧画面の更新
- タグによるフィルタリング機能
- タグの表示（各Todoに紐付いたタグを表示）

## 5. ルーティング設定

```php
Route::middleware(['auth'])->group(function () {
    Route::resource('tags', TagController::class);
    Route::get('todos/tag/{tag}', [TodoController::class, 'byTag'])->name('todos.bytag');
});
```

## 6. 実装手順

1. マイグレーションファイルの作成と実行
   ```bash
   php artisan make:migration create_tags_table
   php artisan make:migration create_todo_tag_table
   php artisan migrate
   ```

2. モデルの作成
   ```bash
   php artisan make:model Tag
   ```

3. コントローラーの作成
   ```bash
   php artisan make:controller TagController --resource
   ```

4. ビューの作成とBladeテンプレートの実装
   - `resources/views/tags/`ディレクトリの作成
   - 必要なビューファイルの作成


## 7. パフォーマンス最適化

- タグ一覧取得時のN+1問題対策
- インデックスの適切な設定

## 8. UI/UX考慮事項

- タグの視覚的な表示（色分けなど）
- タグ選択のための直感的なインターフェース
- レスポンシブデザインの対応
- タグ入力時のオートコンプリート機能

## 9. 実装確認チェックリスト

### 9.1 モデル実装確認
- [ ] Tag モデルの実装
  - [ ] 属性定義
  - [ ] リレーション定義
  - [ ] Factory実装
- [ ] Todo モデルの更新
  - [ ] タグリレーション追加
  - [ ] スコープメソッド追加

### 9.2 コントローラー実装確認
- [ ] TagController
  - [ ] CRUD操作の実装
  - [ ] バリデーション
  - [ ] ポリシーの適用
- [ ] TodoController更新
  - [ ] タグフィルタリング機能
  - [ ] タグ関連バリデーション

### 9.3 ビュー実装確認
- [ ] タグ管理画面
  - [ ] 一覧表示
  - [ ] 作成フォーム
  - [ ] 編集機能
  - [ ] 削除機能
- [ ] Todo画面の更新
  - [ ] タグ表示
  - [ ] タグ選択UI
  - [ ] フィルタリングUI

### 9.4 データベース実装確認
- [ ] マイグレーション
  - [ ] tagsテーブル
  - [ ] todo_tagテーブル
  - [ ] 外部キー制約
  - [ ] インデックス

### 9.5 セキュリティ確認
- [ ] ポリシー実装
- [ ] バリデーション
- [ ] CSRF対策
- [ ] ユーザー単位のアクセス制御