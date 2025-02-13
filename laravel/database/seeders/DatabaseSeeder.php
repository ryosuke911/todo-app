<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Todo;
use App\Models\Tag;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // テストユーザーの作成
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // タグの作成
        $workTag = Tag::create([
            'name' => 'work',
            'user_id' => $user->id,
        ]);

        $personalTag = Tag::create([
            'name' => 'personal',
            'user_id' => $user->id,
        ]);

        // タスクの作成
        $todo1 = Todo::create([
            'user_id' => $user->id,
            'title' => 'テストタスク1',
            'description' => 'テスト説明1',
            'status' => 'pending',
            'deadline' => '2024-03-25',
        ]);

        $todo2 = Todo::create([
            'user_id' => $user->id,
            'title' => 'テストタスク2',
            'description' => 'テスト説明2',
            'status' => 'in_progress',
            'deadline' => '2024-03-26',
        ]);

        $todo3 = Todo::create([
            'user_id' => $user->id,
            'title' => 'テストタスク3',
            'description' => 'テスト説明3',
            'status' => 'completed',
            'deadline' => '2024-03-27',
        ]);

        $todo4 = Todo::create([
            'user_id' => $user->id,
            'title' => 'テストタスク4',
            'description' => 'テスト説明4',
            'status' => 'pending',
            'deadline' => '2024-03-20',
        ]);

        // タグとタスクの関連付け
        $todo1->tags()->attach($workTag->id);
        $todo2->tags()->attach($personalTag->id);
    }
} 