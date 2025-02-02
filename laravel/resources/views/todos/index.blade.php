<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ToDo一覧</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- ナビゲーションバーを追加 -->
    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold">{{ auth()->user()->name }}さんのToDo</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('tags.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        タグ管理
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            ログアウト
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">ToDo一覧</h1>
            <a href="{{ route('todos.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                新規作成
            </a>
        </div>

        <div class="mb-6">
            <form action="{{ route('todos.index') }}" method="GET" class="flex gap-4">
                <select name="status" class="form-select rounded-md shadow-sm">
                    <option value="">全てのステータス</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>未着手</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>進行中</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>完了</option>
                </select>

                <select name="tag_id" class="form-select rounded-md shadow-sm">
                    <option value="">全てのタグ</option>
                    @foreach($tags as $tag)
                        <option value="{{ $tag->id }}" {{ request('tag_id') == $tag->id ? 'selected' : '' }}>
                            {{ $tag->name }} ({{ $tag->todos_count }})
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    フィルター
                </button>

                @if(request('status') || request('tag_id'))
                    <a href="{{ route('todos.index') }}" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        リセット
                    </a>
                @endif
            </form>
        </div>

        <div class="bg-white shadow-md rounded-lg">
            @if($todos->isEmpty())
                <p class="p-4 text-gray-500">タスクがありません</p>
            @else
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-6 py-3 text-left">タイトル</th>
                            <th class="px-6 py-3 text-left">タグ</th>
                            <th class="px-6 py-3 text-left">ステータス</th>
                            <th class="px-6 py-3 text-left">期限</th>
                            <th class="px-6 py-3 text-left">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($todos as $todo)
                            <tr class="border-b">
                                <td class="px-6 py-4">{{ $todo->title }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($todo->tags as $tag)
                                            <a href="{{ route('tags.todos', $tag) }}" class="px-2 py-1 text-sm bg-blue-100 text-blue-800 rounded-full hover:bg-blue-200">
                                                {{ $tag->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-sm
                                        @if($todo->status === 'completed') bg-green-200 text-green-800
                                        @elseif($todo->status === 'in_progress') bg-yellow-200 text-yellow-800
                                        @else bg-gray-200 text-gray-800
                                        @endif">
                                        @switch($todo->status)
                                            @case('completed')
                                                完了
                                                @break
                                            @case('in_progress')
                                                進行中
                                                @break
                                            @default
                                                未着手
                                        @endswitch
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    {{ $todo->deadline ? $todo->deadline->format('Y-m-d') : '期限なし' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <a href="{{ route('todos.edit', $todo) }}" class="text-blue-600 hover:text-blue-800">編集</a>
                                        <form action="{{ route('todos.destroy', $todo) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('本当に削除しますか？')">
                                                削除
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="mt-4">
            {{ $todos->links() }}
        </div>
    </div>
</body>
</html>