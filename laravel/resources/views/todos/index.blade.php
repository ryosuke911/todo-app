@extends('layouts.app')

@section('title', 'ToDo一覧')

@section('header', 'ToDo一覧')

@section('content')
<div class="bg-white shadow rounded-lg">
    <div class="p-6">
        <!-- 新規作成ボタン -->
        <div class="mb-4">
            <a href="{{ route('todos.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                新規作成
            </a>
        </div>

        <!-- タスク一覧 -->
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タイトル</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">説明</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">期限</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タグ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($todos as $todo)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $todo->title }}</td>
                        <td class="px-6 py-4">{{ $todo->description }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($todo->deadline)
                                {{ \Carbon\Carbon::parse($todo->deadline)->format('Y/m/d') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form action="{{ route('todos.toggle', $todo) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1 rounded text-sm font-semibold
                                    @if($todo->status === 'completed')
                                        bg-green-100 text-green-800 hover:bg-green-200
                                    @else
                                        bg-yellow-100 text-yellow-800 hover:bg-yellow-200
                                    @endif">
                                    {{ $todo->status === 'completed' ? '完了' : '進行中' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4">
                            @foreach($todo->tags as $tag)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('todos.edit', $todo) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">編集</a>
                            <form action="{{ route('todos.destroy', $todo) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('本当に削除しますか？')">削除</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection