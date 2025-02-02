<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>タグ管理</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">タグ管理</h1>
                <a href="{{ route('tags.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    新規作成
                </a>
            </div>

            <div class="bg-white shadow-md rounded-lg">
                @if($tags->isEmpty())
                    <p class="p-4 text-gray-500">タグがありません</p>
                @else
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-6 py-3 text-left">タグ名</th>
                                <th class="px-6 py-3 text-left">使用数</th>
                                <th class="px-6 py-3 text-left">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tags as $tag)
                                <tr class="border-b">
                                    <td class="px-6 py-4">
                                        <a href="{{ route('tags.todos', $tag) }}" class="text-blue-600 hover:text-blue-800">
                                            {{ $tag->name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $tag->todos_count }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2">
                                            <a href="{{ route('tags.edit', $tag) }}" class="text-blue-600 hover:text-blue-800">編集</a>
                                            <form action="{{ route('tags.destroy', $tag) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('このタグを削除してもよろしいですか？')">
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
                <a href="{{ route('todos.index') }}" class="text-blue-600 hover:text-blue-800">
                    ← ToDo一覧に戻る
                </a>
            </div>
        </div>
    </div>
</body>
</html> 