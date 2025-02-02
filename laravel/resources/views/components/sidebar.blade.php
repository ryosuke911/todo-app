<!-- サイドバー -->
<aside class="w-64 bg-white shadow-lg h-screen fixed">
    <div class="flex flex-col h-full">
        <!-- ユーザー情報 -->
        <div class="p-4 border-b">
            <div class="font-semibold text-lg">{{ auth()->user()->name }}</div>
            <div class="text-sm text-gray-500">{{ auth()->user()->email }}</div>
        </div>

        <!-- ナビゲーション -->
        <nav class="flex-1 p-4">
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('todos.index') }}" 
                       class="flex items-center p-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('todos.index') ? 'bg-blue-100 text-blue-800' : 'text-gray-700' }}">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span>タスク一覧</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center p-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('dashboard') ? 'bg-blue-100 text-blue-800' : 'text-gray-700' }}">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        <span>ダッシュボード</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('tags.index') }}" 
                       class="flex items-center p-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('tags.*') ? 'bg-blue-100 text-blue-800' : 'text-gray-700' }}">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span>タグ管理</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- ログアウト -->
        <div class="p-4 border-t mt-auto">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition duration-200">
                    <span>ログアウト</span>
                </button>
            </form>
        </div>
    </div>
</aside> 