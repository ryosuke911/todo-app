@extends('layouts.app')

@section('title', 'ToDo一覧')

@section('header', 'ToDo一覧')

@section('content')
<div class="bg-white shadow rounded-lg">
    <div class="p-6">
        <!-- フィルターと新規作成ボタン -->
        <div class="mb-6 flex justify-between items-center">
            <form action="{{ route('todos.index') }}" method="GET" class="flex flex-1 gap-4 mr-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <select name="status" class="form-select pl-10 pr-4 py-2 rounded-md shadow-sm border-gray-300 focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">全てのステータス</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>未着手</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>進行中</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>完了</option>
                    </select>
                </div>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <select name="tag_id" class="form-select pl-10 pr-4 py-2 rounded-md shadow-sm border-gray-300 focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">全てのタグ</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}" {{ request('tag_id') == $tag->id ? 'selected' : '' }}>
                                {{ $tag->name }} ({{ $tag->todos_count }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="フリーワード検索" 
                        class="form-input block w-full pl-10 pr-4 py-2 rounded-md shadow-sm border-gray-300 focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                </div>

                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    フィルター
                </button>

                @if(request('status') || request('tag_id') || request('search'))
                    <a href="{{ route('todos.index') }}" class="inline-flex items-center px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        リセット
                    </a>
                @endif
            </form>

            <a href="{{ route('todos.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="{{ route('todos.edit', $todo) }}" 
                               class="inline-flex items-center px-3 py-1.5 bg-indigo-500 text-white rounded hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                編集
                            </a>
                            <form action="{{ route('todos.destroy', $todo) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        onclick="return confirm('本当に削除しますか？')"
                                        class="inline-flex items-center px-3 py-1.5 bg-red-500 text-white rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-200">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    削除
                                </button>
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