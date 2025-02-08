<?php

namespace App\Http\Controllers;

use App\Services\TodoService;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

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
        $filters = [];
        
        // filterパラメータを常に取得。存在しない場合は 'not_completed' をセット
        $filters['filter'] = $request->input('filter', 'not_completed');
        if ($request->has('search')) {
            $filters['search'] = $request->input('search');
        }
        if ($request->has('deadline')) {
            $filters['deadline'] = $request->input('deadline');
        }
        if ($request->has('tag_id')) {
            $filters['tag_id'] = $request->input('tag_id');
        }

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

    public function show(Todo $todo)
    {
        $this->authorize('view', $todo);
        return view('todos.show', compact('todo'));
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

    public function updateStatus(Request $request, Todo $todo)
    {
        $this->authorize('update', $todo);

        $validated = $request->validate([
            'status' => 'required|string|in:pending,in_progress,completed',
            'last_updated' => 'required|date_format:Y-m-d\TH:i:s.u\Z'
        ]);

        try {
            return DB::transaction(function () use ($request, $todo, $validated) {
                // バージョンチェック
                $currentTimestamp = $todo->updated_at->toISOString();
                if ($currentTimestamp !== $validated['last_updated']) {
                    \Log::info('Version mismatch', [
                        'current' => $currentTimestamp,
                        'received' => $validated['last_updated']
                    ]);
                    return response()->json([
                        'message' => 'データが古くなっています。再度読み込んでください。',
                        'updated_at' => $currentTimestamp
                    ], 409);
                }

                // タイムスタンプを明示的に更新
                $todo->timestamps = true;
                $todo->status = $validated['status'];
                $todo->save();

                // 更新後のタイムスタンプを取得
                $todo->refresh();
                $newTimestamp = $todo->updated_at->toISOString();

                \Log::info('Status updated successfully', [
                    'todo_id' => $todo->id,
                    'old_timestamp' => $currentTimestamp,
                    'new_timestamp' => $newTimestamp
                ]);

                return response()->json([
                    'status' => $todo->status,
                    'updated_at' => $newTimestamp
                ]);
            });
        } catch (\Exception $e) {
            \Log::error('Status update failed: ' . $e->getMessage(), [
                'todo_id' => $todo->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'ステータスの更新に失敗しました。'], 500);
        }
    }

    /**
     * フィールド更新の共通処理
     */
    protected function updateField(Request $request, Todo $todo, string $field, $value)
    {
        return DB::transaction(function () use ($request, $todo, $field, $value) {
            // バージョンチェック
            if ($todo->updated_at->toISOString() !== $request->input('last_updated')) {
                return response()->json([
                    'message' => 'データが古くなっています。再度読み込んでください。',
                    'updated_at' => $todo->updated_at->toISOString()
                ], 409);
            }

            // 入力値のサニタイズ
            $sanitizedValue = $this->sanitizeInput($value);

            // タイムスタンプを明示的に更新
            $todo->timestamps = true;
            $todo->$field = $sanitizedValue;
            $todo->save();

            // 更新後のタイムスタンプを含めてレスポンスを返す
            return response()->json([
                $field => $todo->$field,
                'updated_at' => $todo->updated_at->toISOString()
            ]);
        });
    }

    /**
     * 入力値のサニタイズ
     */
    protected function sanitizeInput($value)
    {
        if (is_string($value)) {
            return strip_tags($value);
        }
        return $value;
    }

    public function updateTitle(Request $request, Todo $todo)
    {
        $this->authorize('update', $todo);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'last_updated' => 'required|date_format:Y-m-d\TH:i:s.u\Z'
        ]);

        return $this->updateField($request, $todo, 'title', $validated['title']);
    }

    public function updateDescription(Request $request, Todo $todo)
    {
        $this->authorize('update', $todo);

        $validated = $request->validate([
            'description' => 'nullable|string',
            'last_updated' => 'required|date_format:Y-m-d\TH:i:s.u\Z'
        ]);

        return $this->updateField($request, $todo, 'description', $validated['description']);
    }

    public function updateDeadline(Request $request, Todo $todo)
    {
        $this->authorize('update', $todo);

        $validated = $request->validate([
            'deadline' => 'nullable|date|after_or_equal:today',
            'last_updated' => 'required|date_format:Y-m-d\TH:i:s.u\Z'
        ]);

        return $this->updateField($request, $todo, 'deadline', $validated['deadline']);
    }

    public function updateTags(Request $request, Todo $todo)
    {
        $this->authorize('update', $todo);

        $validated = $request->validate([
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'last_updated' => 'required|date_format:Y-m-d\TH:i:s.u\Z'
        ]);

        try {
            return DB::transaction(function () use ($request, $todo, $validated) {
                // バージョンチェック
                $currentTimestamp = $todo->updated_at->toISOString();
                if ($currentTimestamp !== $validated['last_updated']) {
                    \Log::info('Version mismatch', [
                        'current' => $currentTimestamp,
                        'received' => $validated['last_updated']
                    ]);
                    return response()->json([
                        'message' => 'データが古くなっています。再度読み込んでください。',
                        'updated_at' => $currentTimestamp
                    ], 409);
                }

                // タグを同期
                $todo->tags()->sync($validated['tags'] ?? []);
                $todo->touch(); // updated_atを更新

                // 更新後のデータを取得
                $todo->refresh();
                $newTimestamp = $todo->updated_at->toISOString();

                \Log::info('Tags updated successfully', [
                    'todo_id' => $todo->id,
                    'old_timestamp' => $currentTimestamp,
                    'new_timestamp' => $newTimestamp
                ]);

                return response()->json([
                    'tags' => $todo->tags,
                    'updated_at' => $newTimestamp
                ]);
            });
        } catch (\Exception $e) {
            \Log::error('Tags update failed: ' . $e->getMessage(), [
                'todo_id' => $todo->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'タグの更新に失敗しました。'], 500);
        }
    }
}