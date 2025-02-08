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
                    <select name="filter" class="form-select pl-10 pr-4 py-2 rounded-md shadow-sm border-gray-300 focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="all" {{ request('filter') === 'all' ? 'selected' : '' }}>全てのステータス</option>
                        <option value="not_completed" {{ !request()->has('filter') || request('filter') === 'not_completed' ? 'selected' : '' }}>完了以外</option>
                        <option value="pending" {{ request('filter') === 'pending' ? 'selected' : '' }}>未着手</option>
                        <option value="in_progress" {{ request('filter') === 'in_progress' ? 'selected' : '' }}>進行中</option>
                        <option value="completed" {{ request('filter') === 'completed' ? 'selected' : '' }}>完了</option>
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
                                {{ $tag->name }}
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

                @if(request('filter') || request('tag_id') || request('search'))
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
        <div class="relative">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タイトル</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">期限</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タグ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($todos as $todo)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('todos.show', $todo) }}" class="group inline-flex items-center">
                                    <span class="text-gray-900 group-hover:text-indigo-600 transition-colors duration-200">{{ $todo->title }}</span>
                                    <div class="relative w-4 h-4 ml-1.5 transform transition-all duration-200 group-hover:translate-x-0.5">
                                        <svg class="absolute inset-0 text-gray-400 opacity-0 group-hover:opacity-100 transition-all duration-200" 
                                             fill="none" 
                                             stroke="currentColor" 
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" 
                                                  stroke-linejoin="round" 
                                                  stroke-width="1.5" 
                                                  d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($todo->deadline)
                                    {{ \Carbon\Carbon::parse($todo->deadline)->format('Y/m/d') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div x-data="todoStatus({{ $todo->id }}, '{{ $todo->status }}', '{{ $todo->updated_at->toISOString() }}')" 
                                     class="relative inline-block text-left">
                                    <button
                                        type="button"
                                        @click.stop="toggleDropdown"
                                        :class="getStatusClasses(status)"
                                        class="status-button px-3 py-1 rounded text-sm font-semibold cursor-pointer inline-flex items-center justify-between w-32">
                                        <span x-text="getStatusText(status)"></span>
                                        <svg class="w-4 h-4 ml-1" 
                                             :class="{ 'transform rotate-180': open }"
                                             fill="none" 
                                             stroke="currentColor" 
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" 
                                                  stroke-linejoin="round" 
                                                  stroke-width="2" 
                                                  d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div x-show="open"
                                         x-cloak
                                         @click.away="open = false"
                                         @keydown.escape.window="open = false"
                                         class="fixed mt-1 w-32 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         x-init="$watch('open', value => {
                                             if (value) {
                                                 const button = $el.previousElementSibling;
                                                 const rect = button.getBoundingClientRect();
                                                 const tableContainer = button.closest('.overflow-x-auto');
                                                 const tableRect = tableContainer.getBoundingClientRect();
                                                 
                                                 // ドロップダウンの位置を計算
                                                 let top = rect.bottom + window.scrollY;
                                                 let left = rect.left;
                                                 
                                                 // テーブルの右端を超えないように調整
                                                 const dropdownWidth = 128; // w-32 = 8rem = 128px
                                                 if (left + dropdownWidth > tableRect.right) {
                                                     left = rect.right - dropdownWidth;
                                                 }
                                                 
                                                 // スタイルを適用
                                                 $el.style.top = `${top}px`;
                                                 $el.style.left = `${left}px`;
                                             }
                                         })">
                                        <div class="py-1" role="menu" aria-orientation="vertical">
                                            <button
                                                @click="changeStatus('pending')"
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                :class="{ 'bg-gray-100': status === 'pending' }"
                                                role="menuitem">
                                                未対応
                                            </button>
                                            <button
                                                @click="changeStatus('in_progress')"
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                :class="{ 'bg-gray-100': status === 'in_progress' }"
                                                role="menuitem">
                                                進行中
                                            </button>
                                            <button
                                                @click="changeStatus('completed')"
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                :class="{ 'bg-gray-100': status === 'completed' }"
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
                                <form action="{{ route('todos.destroy', $todo) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            onclick="return confirm('本当に削除しますか？')"
                                            class="group inline-flex items-center px-2 py-1 text-gray-500 hover:text-red-600 transition-colors duration-200">
                                        <svg class="w-5 h-5 group-hover:text-red-600 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
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
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('todoStatus', (todoId, initialStatus, initialTimestamp) => ({
            open: false,
            status: initialStatus,
            lastUpdated: initialTimestamp,
            
            toggleDropdown() {
                this.open = !this.open;
            },
            
            getStatusClasses(status) {
                const classes = {
                    completed: 'bg-green-100 text-green-800 hover:bg-green-200',
                    in_progress: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
                    pending: 'bg-gray-100 text-gray-800 hover:bg-gray-200'
                };
                return classes[status] || classes.pending;
            },
            
            getStatusText(status) {
                const texts = {
                    completed: '完了',
                    in_progress: '進行中',
                    pending: '未対応'
                };
                return texts[status] || texts.pending;
            },
            
            async changeStatus(newStatus) {
                if (this.status === newStatus) {
                    this.open = false;
                    return;
                }
                
                try {
                    const response = await fetch(`/todos/${todoId}/status`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            status: newStatus,
                            last_updated: this.lastUpdated
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (response.status === 409) {
                            this.lastUpdated = data.updated_at;
                            throw new Error(data.message || 'データが古くなっています。');
                        }
                        throw new Error(data.message || 'ステータスの更新に失敗しました。');
                    }

                    this.status = data.status;
                    this.lastUpdated = data.updated_at;
                    this.open = false;

                    this.$dispatch('notify', {
                        type: 'success',
                        message: 'ステータスを更新しました'
                    });
                } catch (error) {
                    console.error('Error:', error);
                    
                    this.$dispatch('notify', {
                        type: 'error',
                        message: error.message
                    });

                    if (error.message.includes('データが古くなっています')) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                }
            }
        }));
    });
</script>
@endpush

@endsection