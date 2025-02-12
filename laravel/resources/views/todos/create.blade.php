<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>タスク作成</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8" x-data="{ 
        selectedTags: [], 
        toggleTag(tagId) {
            if (this.selectedTags.includes(tagId)) {
                this.selectedTags = this.selectedTags.filter(id => id !== tagId);
            } else {
                this.selectedTags.push(tagId);
            }
        }
    }">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-2xl font-bold mb-8">タスク作成</h1>
            
            <form action="{{ route('todos.store') }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
                @csrf
                
                <div class="mb-6">
                    <label for="title" class="block text-gray-700 text-sm font-bold mb-2">
                        タイトル
                    </label>
                    <input type="text" 
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror"
                        id="title" name="title" value="{{ old('title') }}" required>
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="description" class="block text-gray-700 text-sm font-bold mb-2">
                        詳細
                    </label>
                    <textarea 
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror"
                        id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="status" class="block text-gray-700 text-sm font-bold mb-2">
                        ステータス
                    </label>
                    <select name="status" id="status" 
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>未着手</option>
                        <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>進行中</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>完了</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        タグ
                    </label>
                    <div class="flex items-center gap-2">
                        <div class="flex-1 flex flex-wrap gap-2">
                            @forelse($tags as $tag)
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="tags[]" value="{{ $tag->id }}" 
                                        class="hidden"
                                        {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}
                                        x-model="selectedTags">
                                    <span @click.prevent="toggleTag('{{ $tag->id }}')"
                                          class="px-3 py-1 rounded-full text-sm cursor-pointer transition-all duration-200"
                                          :class="selectedTags.includes('{{ $tag->id }}') ? 'bg-blue-100 text-blue-800' : 'bg-transparent border border-gray-300 text-gray-600 hover:bg-gray-50'">
                                        {{ $tag->name }}
                                    </span>
                                </label>
                            @empty
                                <p class="text-gray-500 text-sm">タグがありません。<a href="{{ route('tags.create', ['redirect' => 'todos.create']) }}" class="text-blue-600 hover:text-blue-800">タグを作成</a>してください。</p>
                            @endforelse
                        </div>
                        <a href="{{ route('tags.create', ['redirect' => 'todos.create']) }}" 
                           class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-500 hover:bg-blue-600 text-white transition-colors duration-200 flex-shrink-0"
                           title="新しいタグを作成">
                            <i class="fas fa-plus text-sm"></i>
                        </a>
                    </div>
                    @error('tags')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="deadline" class="block text-gray-700 text-sm font-bold mb-2">
                        期限
                    </label>
                    <input type="datetime-local" 
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('deadline') border-red-500 @enderror"
                        id="deadline" name="deadline" value="{{ old('deadline') }}">
                    @error('deadline')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-4">
                    <a href="{{ route('todos.index') }}" 
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        キャンセル
                    </a>
                    <button type="submit" 
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        保存
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>