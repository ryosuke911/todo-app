<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TagController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * タグ一覧を表示
     */
    public function index(): View
    {
        $tags = Auth::user()->tags()->withCount('todos')->get();
        return view('tags.index', compact('tags'));
    }

    /**
     * タグ作成フォームを表示
     */
    public function create(): View
    {
        return view('tags.create');
    }

    /**
     * 新規タグを保存
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,NULL,id,user_id,' . Auth::id(),
        ]);
        
        $tag = Auth::user()->tags()->create($validated);

        // リダイレクト先の指定がある場合はそこにリダイレクト
        if ($request->has('redirect')) {
            return redirect()->route($request->input('redirect'))
                ->with('success', 'タグを作成しました。');
        }

        return redirect()->route('tags.index')
            ->with('success', 'タグを作成しました。');
    }

    /**
     * タグの編集フォームを表示
     */
    public function edit(Tag $tag): View
    {
        return view('tags.edit', compact('tag'));
    }

    /**
     * タグを更新
     */
    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,' . $tag->id . ',id,user_id,' . Auth::id(),
        ]);

        $tag->update($validated);

        return redirect()->route('tags.index')
            ->with('success', 'タグを更新しました。');
    }

    /**
     * タグを削除
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()->route('tags.index')
            ->with('success', 'タグを削除しました。');
    }

    /**
     * 特定のタグが付いているTodoの一覧を表示
     */
    public function todos(Tag $tag): View
    {
        $this->authorize('view', $tag);
        
        $todos = $tag->todos()
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('todos.index', compact('todos', 'tag'));
    }
}
