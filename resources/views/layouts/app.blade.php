<!DOCTYPE html>
<html lang="en" x-data="themeManager()" :class="{ 'dark': isDark }" x-init="init()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ArtSpace')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>

    <style>
        * { font-family: "Comic Sans MS", "Comic Sans", cursive !important; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #f97316; border-radius: 99px; }
    </style>

    @stack('styles')
</head>

<body class="min-h-screen transition-colors duration-300"
    :class="isDark ? 'bg-[#21212e] text-white' : 'bg-white text-[#21212e]'"
>
    @include('components.navbar')

    <main class="max-w-screen-xl mx-auto px-4 py-8">
        @if(session('success'))
            <div class="mb-4 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 text-sm font-bold">
                {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </main>

    <script>
        function themeManager() {
            return {
                isDark: false,
                init() {
                    const s = localStorage.getItem('theme');
                    this.isDark = s ? s === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
                    document.documentElement.classList.toggle('dark', this.isDark);
                },
                toggleTheme() {
                    this.isDark = !this.isDark;
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                    document.documentElement.classList.toggle('dark', this.isDark);
                }
            }
        }
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
        document.addEventListener('alpine:initialized', () => lucide.createIcons());
    </script>

    @stack('scripts')
</body>
</html>