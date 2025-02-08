@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6" x-data="todoEdit">
        <!-- ヘッダー部分 -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex-1">
                <!-- タイトル編集 -->
                <div x-show="!isEditing.title" @click="startEdit('title')" class="group cursor-pointer">
                    <h1 class="text-2xl font-bold text-gray-800 group-hover:bg-gray-50 p-2 rounded">
                        <span x-ref="titleDisplay">{{ $todo->title }}</span>
                        <span class="text-gray-400 text-sm ml-2 opacity-0 group-hover:opacity-100">
                            <i class="fas fa-pencil-alt"></i> クリックして編集
                        </span>
                    </h1>
                </div>
                <div x-show="isEditing.title" class="relative">
                    <input type="text" 
                           x-model="editData.title" 
                           @keydown.enter="updateField('title')"
                           @keydown.escape="cancelEdit('title')"
                           class="w-full text-2xl font-bold border-2 border-blue-300 rounded p-2 focus:outline-none focus:border-blue-500"
                           :class="{'border-red-500': errors.title}"
                    >
                    <div x-show="errors.title" class="text-red-500 text-sm mt-1" x-text="errors.title"></div>
                </div>
            </div>
            <div class="flex space-x-2">
                <form action="{{ route('todos.destroy', $todo) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded" onclick="return confirm('本当に削除しますか？')">
                        削除
                    </button>
                </form>
            </div>
        </div>

        <!-- ステータスと期限 -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded">
                <h2 class="text-sm font-semibold text-gray-600 mb-2">ステータス</h2>
                <div class="relative group cursor-pointer hover:bg-gray-100 p-2 rounded transition-colors duration-200">
                    <span x-ref="statusBadge" class="px-3 py-1 rounded-full text-sm
                        @if($todo->status === 'completed') bg-green-100 text-green-800
                        @elseif($todo->status === 'in_progress') bg-yellow-100 text-yellow-800
                        @else bg-gray-100 text-gray-800
                        @endif hover:opacity-75">
                        <span x-ref="statusDisplay">{{ $todo->status === 'completed' ? '完了' : ($todo->status === 'in_progress' ? '進行中' : '未対応') }}</span>
                        <span class="text-gray-400 text-sm ml-2 opacity-0 group-hover:opacity-100">
                            <i class="fas fa-pencil-alt"></i>
                        </span>
                    </span>
                    <select x-model="editData.status"
                            @change="updateField('status')"
                            class="absolute inset-0 opacity-0 cursor-pointer"
                            :class="{'border-red-500': errors.status}">
                        <option value="pending">未対応</option>
                        <option value="in_progress">進行中</option>
                        <option value="completed">完了</option>
                    </select>
                    <div x-show="errors.status" class="text-red-500 text-sm mt-1" x-text="errors.status"></div>
                </div>
            </div>
            <!-- 期限編集 -->
            <div class="bg-gray-50 p-4 rounded">
                <h2 class="text-sm font-semibold text-gray-600 mb-2">期限</h2>
                <div class="relative group cursor-pointer hover:bg-gray-100 p-2 rounded transition-colors duration-200" @click="$refs.deadlineInput.showPicker()">
                    <span class="text-gray-800">
                        <span x-ref="deadlineDisplay">{{ $todo->deadline ? $todo->deadline->format('Y年m月d日') : '設定なし' }}</span>
                        <span class="text-gray-400 text-sm ml-2 opacity-0 group-hover:opacity-100">
                            <i class="fas fa-pencil-alt"></i>
                        </span>
                    </span>
                    <input type="date" 
                           x-ref="deadlineInput"
                           x-model="editData.deadline"
                           @change="updateField('deadline')"
                           class="absolute inset-0 opacity-0 cursor-pointer"
                           :class="{'border-red-500': errors.deadline}">
                    <div x-show="errors.deadline" class="text-red-500 text-sm mt-1" x-text="errors.deadline"></div>
                </div>
            </div>
        </div>

        <!-- 説明 -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">説明</h2>
            <div class="bg-gray-50 p-4 rounded">
                <!-- 説明編集 -->
                <div x-show="!isEditing.description" @click="startEdit('description')" class="group cursor-pointer">
                    <p class="text-gray-700 whitespace-pre-wrap group-hover:bg-gray-100 p-2 rounded">
                        <span x-ref="descriptionDisplay">{{ $todo->description ?? '説明はありません' }}</span>
                        <span class="text-gray-400 text-sm ml-2 opacity-0 group-hover:opacity-100">
                            <i class="fas fa-pencil-alt"></i> クリックして編集
                        </span>
                    </p>
                </div>
                <div x-show="isEditing.description" class="relative">
                    <textarea x-model="editData.description"
                              @keydown.enter.prevent="updateField('description'); isEditing.description = false;"
                              @keydown.escape="cancelEdit('description')"
                              class="w-full h-32 border-2 border-blue-300 rounded p-2 focus:outline-none focus:border-blue-500"
                              :class="{'border-red-500': errors.description}"
                    ></textarea>
                    <div class="flex justify-end mt-2 space-x-2">
                        <button @click="updateField('description'); isEditing.description = false;"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                            保存
                        </button>
                        <button @click="cancelEdit('description')"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm">
                            キャンセル
                        </button>
                    </div>
                    <div x-show="errors.description" class="text-red-500 text-sm mt-1" x-text="errors.description"></div>
                </div>
            </div>
        </div>

        <!-- タグ -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">タグ</h2>
            <div class="bg-gray-50 p-4 rounded">
                <!-- タグ編集 -->
                <div x-show="!isEditing.tags" @click="startEdit('tags')" class="group cursor-pointer">
                    <div class="flex flex-wrap gap-2 group-hover:bg-gray-100 p-2 rounded min-h-[2.5rem]">
                        <template x-if="editData.tags.length > 0">
                            <template x-for="tag in editData.tags" :key="tag.id">
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                    <span x-text="tag.name"></span>
                                </span>
                            </template>
                        </template>
                        <template x-if="editData.tags.length === 0">
                            <span class="text-gray-500">タグはありません</span>
                        </template>
                        <span class="text-gray-400 text-sm ml-2 opacity-0 group-hover:opacity-100">
                            <i class="fas fa-pencil-alt"></i> クリックして編集
                        </span>
                    </div>
                </div>
                <div x-show="isEditing.tags" class="relative">
                    <div class="space-y-2">
                        <template x-for="tag in availableTags" :key="tag.id">
                            <div class="flex items-center">
                                <input type="checkbox"
                                       :id="'tag-' + tag.id"
                                       :value="tag.id"
                                       x-model="editData.selectedTagIds"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <label :for="'tag-' + tag.id" class="ml-2 text-gray-700" x-text="tag.name"></label>
                            </div>
                        </template>
                    </div>
                    <div class="flex justify-end mt-2 space-x-2">
                        <button @click="updateField('tags')"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                            保存
                        </button>
                        <button @click="cancelEdit('tags')"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm">
                            キャンセル
                        </button>
                    </div>
                    <div x-show="errors.tags" class="text-red-500 text-sm mt-1" x-text="errors.tags"></div>
                </div>
            </div>
        </div>

        <!-- 作成日時と更新日時 -->
        <div class="border-t pt-4 mt-6">
            <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                <div>
                    <span class="font-semibold">作成日時:</span>
                    {{ $todo->created_at->format('Y年m月d日 H:i') }}
                </div>
                <div>
                    <span class="font-semibold">最終更新:</span>
                    <span x-text="formatDateTime(lastUpdated || '{{ $todo->updated_at->toISOString() }}')"></span>
                </div>
            </div>
        </div>

        <!-- 同時編集防止のための隠しフィールド -->
        <input type="hidden" x-model="lastUpdated" value="{{ $todo->updated_at->toISOString() }}">
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('todoEdit', () => ({
        isEditing: {
            title: false,
            description: false,
            deadline: false,
            status: false,
            tags: false
        },
        editData: {
            title: '{{ $todo->title }}',
            description: '{{ $todo->description ?? "" }}',
            deadline: '{{ $todo->deadline ? $todo->deadline->format("Y-m-d") : "" }}',
            status: '{{ $todo->status }}',
            tags: @json($todo->tags),
            selectedTagIds: @json($todo->tags->pluck('id'))
        },
        availableTags: @json(auth()->user()->tags),
        errors: {},
        lastUpdated: '{{ $todo->updated_at->toISOString() }}',
        updateTimeouts: {},

        startEdit(field) {
            this.isEditing[field] = true;
            this.errors = {};
            this.$nextTick(() => {
                const element = this.$el.querySelector(`[x-model="editData.${field}"]`);
                if (element) {
                    element.focus();
                }
            });
        },

        cancelEdit(field) {
            this.isEditing[field] = false;
            this.errors = {};
            this.editData[field] = this.getOriginalValue(field);
        },

        getOriginalValue(field) {
            const values = {
                title: '{{ $todo->title }}',
                description: '{{ $todo->description ?? "" }}',
                deadline: '{{ $todo->deadline ? $todo->deadline->format("Y-m-d") : "" }}',
                status: '{{ $todo->status }}',
                selectedTagIds: @json($todo->tags->pluck('id'))
            };
            return values[field] || '';
        },

        getStatusClasses(status) {
            const classes = {
                completed: 'bg-green-100 text-green-800 hover:bg-green-200',
                in_progress: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
                pending: 'bg-gray-100 text-gray-800 hover:bg-gray-200'
            };
            return classes[status] || classes.pending;
        },

        async updateField(field) {
            if (this.updateTimeouts[field]) {
                clearTimeout(this.updateTimeouts[field]);
            }

            this.updateTimeouts[field] = setTimeout(async () => {
                try {
                    const response = await fetch(`/todos/{{ $todo->id }}/${field}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            [field]: field === 'tags' ? this.editData.selectedTagIds : this.editData[field],
                            last_updated: this.lastUpdated
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.isEditing[field] = false;
                        this.errors = {};
                        
                        if (field === 'tags') {
                            this.editData.tags = data.tags;
                        } else if (field === 'status') {
                            const statusDisplay = {
                                'pending': '未対応',
                                'in_progress': '進行中',
                                'completed': '完了'
                            };
                            this.editData.status = data.status;
                            this.$refs.statusDisplay.textContent = statusDisplay[data.status];
                            
                            this.$refs.statusBadge.className = `px-3 py-1 rounded-full text-sm ${
                                data.status === 'completed' ? 'bg-green-100 text-green-800' :
                                data.status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' :
                                'bg-gray-100 text-gray-800'
                            } hover:opacity-75`;
                        } else if (field === 'deadline') {
                            const date = data.deadline ? new Date(data.deadline) : null;
                            this.$refs.deadlineDisplay.textContent = date 
                                ? date.toLocaleDateString('ja-JP', { year: 'numeric', month: 'long', day: 'numeric' }) 
                                : '設定なし';
                        } else {
                            this.$refs[`${field}Display`].textContent = data[field] || '説明はありません';
                        }
                        
                        this.lastUpdated = data.updated_at;
                        
                        this.$dispatch('notify', {
                            type: 'success',
                            message: '更新しました'
                        });
                    } else if (response.status === 409) {
                        this.errors[field] = data.message;
                        setTimeout(() => window.location.reload(), 1000);
                    } else if (response.status === 422) {
                        this.errors = data.errors || {};
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.errors[field] = 'エラーが発生しました。もう一度お試しください。';
                }
            }, 500);
        },

        formatDateTime(isoString) {
            return new Date(isoString).toLocaleString('ja-JP', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            }).replace(/\//g, '年').replace(' ', '日 ');
        }
    }));
});
</script>
@endpush
@endsection 