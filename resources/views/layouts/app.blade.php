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

    {{-- COOKIE CONSENT BANNER --}}
<div id="cookieBanner"
    class="fixed bottom-0 left-0 right-0 z-[9999] transition-transform duration-500"
    style="display:none;">
    <div class="max-w-screen-xl mx-auto px-4 pb-4">
        <div class="rounded-2xl p-4 flex flex-col md:flex-row items-start md:items-center gap-4 border shadow-2xl"
            id="cookieBannerBox">
            <div class="flex items-start gap-3 flex-1">
                <div class="w-9 h-9 rounded-xl bg-orange-500/10 flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="cookie" class="w-5 h-5 text-orange-500"></i>
                </div>
                <div>
                    <p class="font-bold text-sm" id="cookieTitle">Kami menggunakan Cookie</p>
                    <p class="text-xs mt-0.5 leading-relaxed" id="cookieDesc">
                        Website ini menggunakan cookie untuk meningkatkan pengalaman kamu, menyimpan preferensi tema,
                        dan membantu kami memahami cara penggunaan website. Dengan melanjutkan, kamu menyetujui
                        penggunaan cookie kami.
                        <a href="#" class="text-orange-500 font-bold hover:underline" id="cookieLearnMore">Pelajari lebih lanjut</a>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0 w-full md:w-auto">
                <button id="cookieDecline"
                    class="flex-1 md:flex-none px-4 py-2 rounded-xl text-xs font-bold border transition-all"
                    id="cookieDeclineBtn">
                    Tolak
                </button>
                <button id="cookieAccept"
                    class="flex-1 md:flex-none px-5 py-2 rounded-xl bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition-all">
                    Terima Semua
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const consent = getCookie('cookie_consent');
    const banner  = document.getElementById('cookieBanner');
    const box     = document.getElementById('cookieBannerBox');
    const title   = document.getElementById('cookieTitle');
    const desc    = document.getElementById('cookieDesc');
    const decline = document.getElementById('cookieDecline');

    // Styling berdasarkan dark mode
    const isDark = document.documentElement.classList.contains('dark') ||
        localStorage.getItem('theme') === 'dark';

    if (box) {
        box.style.background = isDark ? '#2a2a3d' : 'white';
        box.style.borderColor = isDark ? '#3a3a50' : '#e5e7eb';
    }
    if (title) title.style.color = isDark ? 'white' : '#21212e';
    if (desc)  desc.style.color  = isDark ? '#9ca3af' : '#6b7280';
    if (decline) {
        decline.style.borderColor = isDark ? '#3a3a50' : '#e5e7eb';
        decline.style.color       = isDark ? '#9ca3af' : '#6b7280';
        decline.style.background  = 'transparent';
    }

    // Tampilkan banner kalau belum ada consent
    if (!consent && banner) {
        banner.style.display = 'block';
    }

    // Terima
    document.getElementById('cookieAccept')?.addEventListener('click', function() {
        setCookie('cookie_consent', 'accepted', 365);
        setCookie('tracking_enabled', 'true', 365);
        hideBanner();
        trackPageView(); // mulai tracking
    });

    // Tolak
    document.getElementById('cookieDecline')?.addEventListener('click', function() {
        setCookie('cookie_consent', 'declined', 365);
        setCookie('tracking_enabled', 'false', 365);
        hideBanner();
    });

    function hideBanner() {
        if (banner) {
            banner.style.transform = 'translateY(100%)';
            setTimeout(() => banner.style.display = 'none', 500);
        }
    }

    // ===== ACTIVITY TRACKING =====
    function trackPageView() {
        if (getCookie('tracking_enabled') !== 'true') return;

        const data = {
            page:       window.location.pathname,
            referrer:   document.referrer,
            timestamp:  new Date().toISOString(),
            session_id: getOrCreateSessionId(),
        };

        // Simpan ke localStorage sebagai log aktivitas
        const logs = JSON.parse(localStorage.getItem('activity_log') || '[]');
        logs.push(data);

        // Simpan max 50 entri
        if (logs.length > 50) logs.splice(0, logs.length - 50);
        localStorage.setItem('activity_log', JSON.stringify(logs));

        // Update page view count per halaman
        const views = JSON.parse(localStorage.getItem('page_views') || '{}');
        views[data.page] = (views[data.page] || 0) + 1;
        localStorage.setItem('page_views', JSON.stringify(views));

        // Simpan last visited ke cookie
        setCookie('last_visited', data.page, 30);
        setCookie('last_visit_time', data.timestamp, 30);
    }

    function getOrCreateSessionId() {
        let sid = getCookie('session_track');
        if (!sid) {
            sid = 'sess_' + Math.random().toString(36).substr(2, 9);
            setCookie('session_track', sid, 1); // 1 hari
        }
        return sid;
    }

    // Jalankan tracking kalau sudah accepted sebelumnya
    if (consent === 'accepted') {
        trackPageView();
    }

    // ===== COOKIE HELPERS =====
    function setCookie(name, value, days) {
        const expires = new Date();
        expires.setDate(expires.getDate() + days);
        document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;
    }

    function getCookie(name) {
        const match = document.cookie.split('; ').find(r => r.startsWith(name + '='));
        return match ? decodeURIComponent(match.split('=')[1]) : null;
    }
})();
</script>

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