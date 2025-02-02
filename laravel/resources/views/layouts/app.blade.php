<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @yield('styles')
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- サイドバー -->
        @includeFirst(['components.sidebar', 'layouts.empty'])

        <!-- メインコンテンツ -->
        <div class="flex-1">
            <!-- ヘッダー -->
            <header class="bg-white shadow">
                <div class="px-8 py-6">
                    <h1 class="text-2xl font-semibold text-gray-900">
                        @yield('header')
                    </h1>
                </div>
            </header>

            <!-- メインコンテンツエリア -->
            <main class="p-8">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html> 