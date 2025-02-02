@extends('layouts.app')

@section('title', 'タスク作成')

@section('header', 'タスク作成')

@section('content')
<div class="bg-white shadow rounded-lg">
    <div class="p-6">
        <form action="{{ route('todos.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <!-- タイトル -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">タイトル</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 説明 -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">説明</label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 期限 -->
                <div>
                    <label for="deadline" class="block text-sm font-medium text-gray-700">期限</label>
                    <input type="date" name="deadline" id="deadline" value="{{ old('deadline') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('deadline')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- ステータス -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">ステータス</label>
                    <select name="status" id="status" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>未対応</option>
                        <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>進行中</option>
                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>完了</option>
                    </select>
                    @error('status')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- タグ -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">タグ</label>
                    <div class="mt-2 space-y-2">
                        @foreach($tags as $tag)
                            <div class="flex items-center">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" id="tag-{{ $tag->id }}"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}>
                                <label for="tag-{{ $tag->id }}" class="ml-2 text-sm text-gray-700">{{ $tag->name }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('tags')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- ボタン -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('todos.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        キャンセル
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        作成
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection