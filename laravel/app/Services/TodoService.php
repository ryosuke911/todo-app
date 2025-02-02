<?php

namespace App\Services;

use App\Models\Todo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TodoService
{
    public function createTodo(array $data): Todo
    {
        return Todo::create([
            'user_id' => auth()->id(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'deadline' => $data['deadline'] ?? null,
        ]);
    }

    public function updateTodo(Todo $todo, array $data): bool
    {
        return $todo->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? $todo->description,
            'status' => $data['status'] ?? $todo->status,
            'deadline' => $data['deadline'] ?? $todo->deadline,
        ]);
    }

    public function deleteTodo(Todo $todo): bool
    {
        return $todo->delete();
    }

    public function getTodos(array $filters = []): LengthAwarePaginator
    {
        $query = Todo::query()
            ->where('user_id', auth()->id())
            ->with('tags');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['tag_id'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('tags.id', $filters['tag_id']);
            });
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['deadline'])) {
            $query->whereDate('deadline', '<=', $filters['deadline']);
        }

        return $query->orderBy('deadline', 'asc')
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
    }

    public function getOverdueTodos(): Collection
    {
        return Todo::where('user_id', auth()->id())
                   ->where('status', '!=', 'completed')
                   ->whereDate('deadline', '<', now())
                   ->get();
    }

    public function toggleStatus(Todo $todo): bool
    {
        $newStatus = $todo->status === 'completed' ? 'pending' : 'completed';
        return $todo->update(['status' => $newStatus]);
    }
}