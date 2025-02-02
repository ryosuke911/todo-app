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

        <!-- フィルター -->
        <div class="mb-6">
            <form action="{{ route('todos.index') }}" method="GET" class="flex flex-wrap gap-4">
                <select name="status" class="form-select rounded-md shadow-sm border-gray-300">
                    <option value="">全てのステータス</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>未着手</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>進行中</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>完了</option>
                </select>

                <select name="tag_id" class="form-select rounded-md shadow-sm border-gray-300">
                    <option value="">全てのタグ</option>
                    @foreach($tags as $tag)
                        <option value="{{ $tag->id }}" {{ request('tag_id') == $tag->id ? 'selected' : '' }}>
                            {{ $tag->name }} ({{ $tag->todos_count }})
                        </option>
                    @endforeach
                </select>

                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="タイトルまたは説明で検索" 
                    class="form-input rounded-md shadow-sm border-gray-300">

                <button type="submit" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    フィルター
                </button>

                @if(request('status') || request('tag_id') || request('search'))
                    <a href="{{ route('todos.index') }}" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                        リセット
                    </a>
                @endif
            </form>
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
                            <div class="relative" x-data="{ open: false }">
                                <button
                                    @click="open = !open"
                                    type="button"
                                    data-todo-id="{{ $todo->id }}"
                                    class="status-button px-3 py-1 rounded text-sm font-semibold cursor-pointer inline-flex items-center justify-between w-32
                                        @if($todo->status === 'completed')
                                            bg-green-100 text-green-800 hover:bg-green-200
                                        @elseif($todo->status === 'in_progress')
                                            bg-yellow-100 text-yellow-800 hover:bg-yellow-200
                                        @else
                                            bg-gray-100 text-gray-800 hover:bg-gray-200
                                        @endif">
                                    <span>{{ $todo->status === 'completed' ? '完了' : ($todo->status === 'in_progress' ? '進行中' : '未対応') }}</span>
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div
                                    x-show="open"
                                    x-cloak
                                    @click.away="open = false"
                                    class="absolute z-50 mt-1 w-32 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                    <div class="py-1" role="menu" aria-orientation="vertical">
                                        <button
                                            @click="open = false"
                                            onclick="updateStatus({{ $todo->id }}, 'pending')"
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            role="menuitem">
                                            未対応
                                        </button>
                                        <button
                                            @click="open = false"
                                            onclick="updateStatus({{ $todo->id }}, 'in_progress')"
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            role="menuitem">
                                            進行中
                                        </button>
                                        <button
                                            @click="open = false"
                                            onclick="updateStatus({{ $todo->id }}, 'completed')"
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            role="menuitem">
                                            完了
                                        </button>
                                    </div>
                                </div>
                            </div>
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

@push('scripts')
<script>
    function updateStatus(todoId, newStatus) {
        fetch(`/todos/${todoId}/update-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const button = document.querySelector(`button[data-todo-id="${todoId}"]`);
            const statusText = button.querySelector('span');
            
            // Remove all existing status classes
            button.classList.remove(
                'bg-green-100', 'text-green-800', 'hover:bg-green-200',
                'bg-yellow-100', 'text-yellow-800', 'hover:bg-yellow-200',
                'bg-gray-100', 'text-gray-800', 'hover:bg-gray-200'
            );
            
            // Add new status classes
            if (data.status === 'completed') {
                button.classList.add('bg-green-100', 'text-green-800', 'hover:bg-green-200');
                statusText.textContent = '完了';
            } else if (data.status === 'in_progress') {
                button.classList.add('bg-yellow-100', 'text-yellow-800', 'hover:bg-yellow-200');
                statusText.textContent = '進行中';
            } else {
                button.classList.add('bg-gray-100', 'text-gray-800', 'hover:bg-gray-200');
                statusText.textContent = '未対応';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('ステータスの更新に失敗しました。');
        });
    }
</script>
@endpush

@endsection