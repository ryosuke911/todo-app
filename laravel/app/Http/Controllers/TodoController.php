<?php

namespace App\Http\Controllers;

use App\Services\TodoService;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TodoController extends Controller
{
    private $todoService;

    public function __construct(TodoService $todoService)
    {
        $this->todoService = $todoService;
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'search', 'deadline', 'tag_id']);
        $todos = $this->todoService->getTodos($filters);
        $tags = auth()->user()->tags()->withCount('todos')->get();
        return view('todos.index', compact('todos', 'tags'));
    }

    public function create()
    {
        $tags = auth()->user()->tags()->get();
        return view('todos.create', compact('tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date|after_or_equal:today',
            'status' => 'required|string|in:pending,in_progress,completed',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ]);

        $validated['user_id'] = $request->user()->id;

        $todo = $this->todoService->createTodo($validated);
        
        if (isset($validated['tags'])) {
            $todo->tags()->attach($validated['tags']);
        }

        return redirect()->route('todos.index')->with('success', 'タスクを作成しました');
    }

    public function edit(Todo $todo)
    {
        $this->authorize('update', $todo);
        $tags = auth()->user()->tags()->get();
        return view('todos.edit', compact('todo', 'tags'));
    }

    public function update(Request $request, Todo $todo)
    {
        $this->authorize('update', $todo);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date|after_or_equal:today',
            'status' => 'required|string|in:pending,in_progress,completed',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ]);

        $this->todoService->updateTodo($todo, $validated);

        // タグの更新
        if (isset($validated['tags'])) {
            $todo->tags()->sync($validated['tags']);
        } else {
            $todo->tags()->detach();
        }

        return redirect()->route('todos.index')->with('success', 'タスクを更新しました');
    }

    public function destroy(Todo $todo)
    {
        $this->authorize('delete', $todo);
        
        $this->todoService->deleteTodo($todo);
        return redirect()->route('todos.index')->with('success', 'Task deleted successfully');
    }

    public function toggleStatus(Todo $todo)
    {
        $this->authorize('update', $todo);
        
        $this->todoService->toggleTodoStatus($todo);
        return response()->json(['status' => $todo->status]);
    }
}