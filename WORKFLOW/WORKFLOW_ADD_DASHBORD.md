# ダッシュボード実装ワークフロー

## 1. 画面設計

```
+----------------+------------------------------------------------+
|   サイドバー    |              タスク分析ダッシュボード           |
|                |                                                |
| ▶ タスク一覧    |  [統計カード] [統計カード] [統計カード] [統計カード]|
|                |   総タスク数   進行中      完了       期限超過   |
| ▶ ダッシュボード |     10         5          3          2        |
|                |                                                |
| ▶ 設定         |  +------------------------------------------+ |
|                |  |           タスク進捗状況グラフ             | |
|                |  |     [円グラフ: 進行中/完了/期限超過]       | |
|                |  +------------------------------------------+ |
|                |                                                |
|                |  +---------------+  +------------------------+ |
|                |  | タグ別タスク数  |  | 今後の締め切りタスク    | |
|                |  | [棒グラフ]     |  | ・タスクA (明日締切)    | |
|                |  |               |  | ・タスクB (3日後締切)   | |
|                |  +---------------+  +------------------------+ |
|                |                                                |
|                |  +------------------------------------------+ |
|                |  |         日別タスク作成数の推移            | |
|                |  |           [折れ線グラフ]                  | |
|                |  +------------------------------------------+ |
|                |                                                |
+----------------+------------------------------------------------+
```

## 2. データフローと影響範囲の考慮

### データの流れ
1. **ユーザー認証層**
   - `AuthController` → `DashboardController`へのユーザー情報の受け渡し
   - ユーザーごとのデータ分離の確保

2. **データアクセス層**
   - `TodoService` → `DashboardService`へのデータ提供
   - `TagService` → `DashboardService`へのデータ提供
   - キャッシュ戦略の検討

3. **表示層**
   - 共通レイアウト（`app.blade.php`）の拡張
   - コンポーネント間のデータ受け渡し
   - Chart.jsとのデータバインディング

### 影響範囲の分析

### 新規作成が必要なファイル
1. **コントローラ**
   - `app/Http/Controllers/DashboardController.php`
     - 既存の`TodoController`や`TagController`とのデータ共有方法の検討
     - ユーザー認証情報の利用

2. **サービス**
   - `app/Services/DashboardService.php`
     - 既存の`TodoService`との重複ロジック回避
     - パフォーマンスを考慮したデータ取得方法

3. **ビュー**
   - `resources/views/dashboard/index.blade.php`
   - `resources/views/dashboard/components/*.blade.php`
     - 既存のビューコンポーネントとの整合性確保
     - 共通スタイルの継承

4. **アセット**
   - `public/js/dashboard.js`
   - `public/css/dashboard.css`

### 修正が必要なファイル
1. **レイアウト**
   - `resources/views/layouts/app.blade.php`
     - 既存のナビゲーション構造への影響考慮
     - レスポンシブデザインの維持

2. **ルーティング**
   - `routes/web.php`
     - 既存のルート構造との整合性
     - ミドルウェアの適用

## 3. 実装における注意点

### パフォーマンスの考慮
1. **データ取得の最適化**
   - N+1問題の回避
   - 必要なデータのみを取得
   - クエリの効率化

2. **キャッシュ戦略**
   - 統計データのキャッシュ
   - グラフデータのキャッシュ
   - 適切なキャッシュ期間の設定

### コード品質の維持
1. **既存コードとの整合性**
   - 命名規則の統一
   - コーディング規約の遵守
   - 適切なコメント付与

## 4. 実装手順

### Phase 1: 基本設定とレイアウト
- [x] 1.1 Chart.jsのインストール
- [x] 1.2 共通レイアウトの修正
  - [x] サイドバーの実装
  - [x] メインコンテンツエリアの準備
- [x] 1.3 ルーティングの追加
  - [x] `routes/web.php` にダッシュボードルートを追加

### Phase 2: バックエンド実装
- [ ] 2.1 DashboardServiceの実装
  - [ ] タスク統計の計算メソッド
  - [ ] グラフデータの生成メソッド
  - [ ] タグ別統計の集計メソッド
  - [ ] 期限切れタスクの取得メソッド
  - [ ] 日別タスク作成数の集計メソッド

- [ ] 2.2 DashboardControllerの実装
  - [ ] index メソッドの実装
  - [ ] 必要なデータの取得処理

### Phase 3: フロントエンド実装
- [ ] 3.1 コンポーネントの作成
  - [ ] 統計カードコンポーネント
  - [ ] 進捗グラフコンポーネント
  - [ ] タグチャートコンポーネント
  - [ ] タイムラインチャートコンポーネント

- [ ] 3.2 ダッシュボードビューの実装
  - [ ] 基本レイアウト
  - [ ] コンポーネントの配置
  - [ ] レスポンシブデザインの適用

- [ ] 3.3 JavaScript実装
  - [ ] Chart.jsの初期化
  - [ ] データバインディング
  - [ ] イベントハンドラ

### Phase 4: スタイリングとUI改善
- [ ] 4.1 Tailwind CSSの適用
  - [ ] カラースキームの設定
  - [ ] カスタムスタイルの追加
  - [ ] アニメーションの追加

- [ ] 4.2 レスポンシブ対応
  - [ ] ブレークポイントの設定
  - [ ] モバイル表示の最適化
  - [ ] グラフサイズの調整

## 5. 注意点
1. データの集計処理は重くなる可能性があるため、キャッシュの使用を検討
2. グラフのレスポンシブ対応は慎重に実装
3. 大量のデータがある場合のパフォーマンスに注意
4. ユーザーごとのデータ分離を確実に実装

## 6. 完了条件
- [ ] すべての統計情報が正しく表示される
- [ ] グラフが適切に描画される
- [ ] レスポンシブ対応が完了している
- [ ] パフォーマンスが許容範囲内
- [ ] UIが直感的で使いやすい

## 7. 現在の進捗
- Phase 1: 完了
  - [x] 1.1 Chart.jsのインストール
  - [x] 1.2 共通レイアウトの修正
  - [x] 1.3 ルーティングの追加
- Phase 2: 未着手
- Phase 3: 未着手
- Phase 4: 未着手
