<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Todo App')</title>
    
    <!-- x-cloak directive style -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- ナビゲーションバー -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('todos.index') }}" class="text-xl font-bold text-gray-800">
                            Todo App
                        </a>
                    </div>
                </div>
                
                @auth
                <div class="flex items-center">
                    <span class="text-gray-600 mr-4">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-gray-800">
                            ログアウト
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- メインコンテンツ -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- 通知コンポーネント -->
        <div x-data="notifications"
             class="fixed top-4 right-4 z-50"
             @notify.window="add($event.detail)">
            <template x-for="notification in notifications" :key="notification.id">
                <div x-show="notification.show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform translate-x-8"
                     x-transition:enter-end="opacity-100 transform translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform translate-x-0"
                     x-transition:leave-end="opacity-0 transform translate-x-8"
                     class="flex items-center p-4 mb-4 rounded-lg shadow-lg"
                     :class="{
                         'bg-green-100 text-green-800': notification.type === 'success',
                         'bg-red-100 text-red-800': notification.type === 'error'
                     }">
                    <div class="flex-shrink-0 mr-3">
                        <template x-if="notification.type === 'success'">
                            <i class="fas fa-check-circle"></i>
                        </template>
                        <template x-if="notification.type === 'error'">
                            <i class="fas fa-exclamation-circle"></i>
                        </template>
                    </div>
                    <div x-text="notification.message"></div>
                    <button @click="remove(notification.id)" class="ml-4 text-current hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </template>
        </div>

        @yield('content')
    </main>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('notifications', () => ({
                notifications: [],
                add(notification) {
                    const id = Date.now();
                    this.notifications.push({
                        id,
                        type: notification.type,
                        message: notification.message,
                        show: true
                    });
                    setTimeout(() => this.remove(id), 3000);
                },
                remove(id) {
                    const index = this.notifications.findIndex(n => n.id === id);
                    if (index > -1) {
                        this.notifications[index].show = false;
                        setTimeout(() => {
                            this.notifications = this.notifications.filter(n => n.id !== id);
                        }, 300);
                    }
                }
            }));
        });
    </script>

    @stack('scripts')
</body>
</html> 