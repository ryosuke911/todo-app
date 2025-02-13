<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. テスト用ユーザーの作成
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 重複メールテスト用の既存メールアドレス
        User::factory()->create([
            'name' => '既存ユーザー',
            'email' => 'existing@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 2. タグの作成
        $tags = [
            Tag::factory()->create([
                'name' => 'work',
                'user_id' => $user->id,
            ]),
            Tag::factory()->create([
                'name' => 'urgent',
                'user_id' => $user->id,
            ]),
            // 重複名テスト用の既存タグ
            Tag::factory()->create([
                'name' => '既存タグ',
                'user_id' => $user->id,
            ]),
        ];

        $tagIds = array_map(fn($tag) => $tag->id, $tags);

        // 3. 異なるステータスのTodoを作成
        // 進行中のTodo
        Todo::factory()->create([
            'title' => 'テストタスク1',
            'description' => '進行中のタスク',
            'status' => 'in_progress',
            'user_id' => $user->id,
            'deadline' => now()->addDays(5),
        ])->tags()->attach($tags[0]->id);

        // 完了済みのTodo
        Todo::factory()->create([
            'title' => 'テストタスク2',
            'description' => '完了済みのタスク',
            'status' => 'completed',
            'user_id' => $user->id,
            'deadline' => now()->addDays(10),
        ])->tags()->attach($tags[1]->id);

        // 期限切れのTodo
        Todo::factory()->create([
            'title' => 'テストタスク3',
            'description' => '期限切れのタスク',
            'status' => 'pending',
            'user_id' => $user->id,
            'deadline' => now()->subDays(2),
        ])->tags()->attach([$tags[0]->id, $tags[1]->id]);

        // ダッシュボード表示用の追加Todo
        Todo::factory()->count(10)->create([
            'user_id' => $user->id,
        ])->each(function ($todo) use ($tagIds) {
            $todo->tags()->attach($tagIds[array_rand($tagIds)]);
        });
    }
} 