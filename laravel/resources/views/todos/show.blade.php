@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-5">
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md overflow-hidden" x-data="todoEdit">
        <!-- ヘッダー部分 -->
        <div class="relative bg-gradient-to-r from-blue-500/90 to-blue-600/90 p-5">
            <div class="relative flex justify-between items-center">
                <div class="flex-1">
                    <!-- タイトル編集 -->
                    <div x-show="!isEditing.title" @click="startEdit('title')" class="group cursor-pointer">
                        <h1 class="text-xl font-medium text-white group-hover:bg-white/10 p-2.5 -ml-2.5 rounded-lg transition-all duration-200">
                            <span x-ref="titleDisplay">{{ $todo->title }}</span>
                            <span class="text-white/60 text-sm ml-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <i class="fas fa-pencil-alt"></i>
                            </span>
                        </h1>
                    </div>
                    <div x-show="isEditing.title" class="relative">
                        <input type="text" 
                               x-model="editData.title" 
                               @keydown.enter="updateField('title')"
                               @keydown.escape="cancelEdit('title')"
                               class="w-full text-lg font-medium bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg p-2.5 text-white placeholder-white/50 focus:outline-none focus:border-white/30 focus:bg-white/15 transition-all duration-200"
                               :class="{'border-red-300': errors.title}"
                        >
                        <div x-show="errors.title" class="text-red-200 text-sm mt-1.5" x-text="errors.title"></div>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <form action="{{ route('todos.destroy', $todo) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500/70 hover:bg-red-600 text-white px-3.5 py-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-blue-600/90 text-sm" onclick="return confirm('本当に削除しますか？')">
                            <i class="fas fa-trash-alt mr-1.5"></i>削除
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="p-5 space-y-5 bg-gray-50/50">
            <!-- ステータスと期限 -->
            <div class="grid grid-cols-12 gap-5">
                <div class="col-span-12 md:col-span-6 lg:col-span-3">
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <h2 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-tasks mr-2 text-gray-400"></i>ステータス
                        </h2>
                        <div class="relative group cursor-pointer">
                            <span x-ref="statusBadge" class="inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-medium shadow-sm transition-all duration-200
                                @if($todo->status === 'completed') bg-emerald-50 text-emerald-700 border border-emerald-200
                                @elseif($todo->status === 'in_progress') bg-amber-50 text-amber-700 border border-amber-200
                                @else bg-gray-50 text-gray-700 border border-gray-200
                                @endif hover:shadow-md transform hover:scale-[1.01]">
                                <span x-ref="statusDisplay">{{ $todo->status === 'completed' ? '完了' : ($todo->status === 'in_progress' ? '進行中' : '未対応') }}</span>
                                <i class="fas fa-chevron-down ml-2 text-gray-400"></i>
                            </span>
                            <select x-model="editData.status"
                                    @change="updateField('status')"
                                    class="absolute inset-0 opacity-0 cursor-pointer">
                                <option value="pending">未対応</option>
                                <option value="in_progress">進行中</option>
                                <option value="completed">完了</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="col-span-12 md:col-span-6 lg:col-span-3">
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <h2 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-calendar-alt mr-2 text-gray-400"></i>期限
                        </h2>
                        <div class="relative group cursor-pointer" @click="$refs.deadlineInput.showPicker()">
                            <div class="inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-medium bg-white border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200">
                                <span x-ref="deadlineDisplay">{{ $todo->deadline ? $todo->deadline->format('Y年m月d日') : '設定なし' }}</span>
                                <i class="fas fa-calendar ml-2 text-gray-400"></i>
                            </div>
                            <input type="date" 
                                   x-ref="deadlineInput"
                                   x-model="editData.deadline"
                                   @change="updateField('deadline')"
                                   class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>
                    </div>
                </div>
            </div>

            <!-- タグ -->
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h2 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-tags mr-2 text-gray-400"></i>タグ
                </h2>
                <div x-show="!isEditing.tags" @click="startEdit('tags')" class="group cursor-pointer">
                    <div class="flex flex-wrap gap-2 min-h-[2.25rem]">
                        <template x-if="editData.tags.length > 0">
                            <template x-for="tag in editData.tags" :key="tag.id">
                                <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-sm bg-gray-700 text-white border border-gray-600">
                                    <i class="fas fa-tag mr-1.5 text-white/80"></i>
                                    <span x-text="tag.name"></span>
                                </span>
                            </template>
                        </template>
                        <template x-if="editData.tags.length === 0">
                            <span class="text-gray-500 text-sm">タグはありません</span>
                        </template>
                    </div>
                </div>
                <div x-show="isEditing.tags" class="relative">
                    <div class="flex flex-wrap gap-2">
                        <template x-for="tag in availableTags" :key="tag.id">
                            <div>
                                <input type="checkbox"
                                       :id="'tag-' + tag.id"
                                       :value="tag.id"
                                       x-model="editData.selectedTagIds"
                                       class="hidden">
                                <label :for="'tag-' + tag.id" 
                                       @click.prevent="toggleTag(tag.id)"
                                       class="inline-flex items-center px-2.5 py-1.5 rounded-full text-sm cursor-pointer transition-all duration-200"
                                       :class="editData.selectedTagIds.includes(tag.id) ? 'bg-gray-700 text-white border border-gray-600' : 'bg-gray-100 text-gray-600 border border-gray-200 hover:bg-gray-200'">
                                    <i class="fas fa-tag mr-1.5" :class="editData.selectedTagIds.includes(tag.id) ? 'text-white/80' : 'text-gray-400'"></i>
                                    <span x-text="tag.name"></span>
                                </label>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- 説明 -->
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h2 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-align-left mr-2 text-gray-400"></i>説明
                </h2>
                <div x-show="!isEditing.description" @click="startEdit('description')" class="group cursor-pointer">
                    <div class="bg-gray-50 rounded-lg p-4 min-h-[6rem] max-h-[16rem] overflow-y-auto group-hover:bg-gray-100/80 transition-all duration-200">
                        <p class="text-gray-600 whitespace-pre-wrap text-sm">
                            <span x-ref="descriptionDisplay">{{ $todo->description ?? '説明はありません' }}</span>
                        </p>
                    </div>
                </div>
                <div x-show="isEditing.description" class="relative">
                    <textarea x-model="editData.description"
                              @keydown.enter.prevent="updateField('description'); isEditing.description = false;"
                              @keydown.escape="cancelEdit('description')"
                              class="w-full h-32 border border-gray-200 rounded-lg p-4 text-sm focus:outline-none focus:border-gray-400 focus:ring-1 focus:ring-gray-400 transition-all duration-200 bg-gray-50/50"
                              placeholder="説明を入力してください..."
                    ></textarea>
                    <div class="flex justify-end mt-3 space-x-2">
                        <button @click="cancelEdit('description')"
                                class="px-3.5 py-1.5 text-sm text-gray-600 hover:text-gray-800">
                            キャンセル
                        </button>
                        <button @click="updateField('description'); isEditing.description = false;"
                                class="px-3.5 py-1.5 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-sm">
                            保存
                        </button>
                    </div>
                </div>
            </div>

            <!-- 作成日時と更新日時 -->
            <div class="grid grid-cols-2 gap-4 text-xs text-gray-500 pt-1">
                <div class="flex items-center">
                    <i class="fas fa-clock mr-1.5 text-gray-400"></i>
                    <span class="font-medium mr-1.5">作成:</span>
                    <span class="text-gray-600">{{ $todo->created_at->format('Y/m/d H:i') }}</span>
                </div>
                <div class="flex items-center justify-end">
                    <i class="fas fa-history mr-1.5 text-gray-400"></i>
                    <span class="font-medium mr-1.5">更新:</span>
                    <span class="text-gray-600" x-text="formatDateTime(lastUpdated || '{{ $todo->updated_at->toISOString() }}')"></span>
                </div>
            </div>
        </div>

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
                            
                            this.$refs.statusBadge.className = `inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 ${
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
            if (!isoString) return '';
            const date = new Date(isoString);
            return date.toLocaleString('ja-JP', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        toggleTag(tagId) {
            const index = this.editData.selectedTagIds.indexOf(tagId);
            if (index === -1) {
                this.editData.selectedTagIds.push(tagId);
            } else {
                this.editData.selectedTagIds.splice(index, 1);
            }
            this.updateField('tags');
        }
    }));
});
</script>
@endpush
@endsection 