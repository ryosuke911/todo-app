<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\DashboardController;

// トップページ
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('todos.index');
    }
    return redirect()->route('login');
});

// 認証関連のルート
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// パスワードリセット関連のルート
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// ToDo管理のルート（認証必須）
Route::middleware(['auth'])->group(function () {
    // ダッシュボード
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Todoの基本CRUD操作
    Route::get('/todos', [TodoController::class, 'index'])->name('todos.index');
    Route::get('/todos/create', [TodoController::class, 'create'])->name('todos.create');
    Route::post('/todos', [TodoController::class, 'store'])->name('todos.store');
    Route::get('/todos/{todo}', [TodoController::class, 'show'])->name('todos.show');
    Route::delete('/todos/{todo}', [TodoController::class, 'destroy'])->name('todos.destroy');

    // Todoの個別フィールド更新用のルート
    Route::patch('/todos/{todo}/title', [TodoController::class, 'updateTitle'])->name('todos.update-title');
    Route::patch('/todos/{todo}/description', [TodoController::class, 'updateDescription'])->name('todos.update-description');
    Route::patch('/todos/{todo}/deadline', [TodoController::class, 'updateDeadline'])->name('todos.update-deadline');
    Route::patch('/todos/{todo}/status', [TodoController::class, 'updateStatus'])->name('todos.update-status');
    Route::patch('/todos/{todo}/tags', [TodoController::class, 'updateTags'])->name('todos.update-tags');

    // タグ関連のルート
    Route::resource('tags', TagController::class)->except(['show']);
    Route::get('tags/{tag}/todos', [TagController::class, 'todos'])->name('tags.todos');
});
