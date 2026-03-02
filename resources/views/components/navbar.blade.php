<header x-data="navApp()"
    class="sticky top-0 z-50 border-b transition-colors duration-300"
    :class="[
        isDark ? 'bg-[#21212e] border-[#3a3a50]' : 'bg-white border-gray-200',
        isDark ? 'dark-header' : 'light-header'
    ]"
>
<style>
    /* Navbar styles */
    .nav-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 13px;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 700;
        transition: all 0.2s;
        text-decoration: none;
        white-space: nowrap;
    }
    
    /* Dark mode navbar links */
    .dark .nav-link {
        color: #ffffff !important;
    }
    .dark .nav-link:hover,
    .dark .nav-link.active {
        background: rgba(249,115,22,0.18);
        color: #f97316 !important;
    }
    
    /* Light mode navbar links */
    html:not(.dark) .nav-link {
        color: #21212e !important;
    }
    html:not(.dark) .nav-link:hover,
    html:not(.dark) .nav-link.active {
        background: #21212e;
        color: #ffffff !important;
    }

    /* Dropdown menu */
    .dd-menu {
        position: absolute;
        top: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%);
        min-width: 190px;
        border-radius: 14px;
        padding: 6px;
        z-index: 200;
        display: none;
    }
    .dd-menu.open { display: block; }
    
    /* Dark mode dropdown */
    .dark .dd-menu { 
        background: #2a2a3d; 
        border: 1px solid #3a3a50; 
        box-shadow: 0 20px 40px rgba(0,0,0,0.5); 
    }
    .dark .dd-item { 
        color: #ffffff !important; 
    }
    .dark .dd-item:hover { 
        background: rgba(249,115,22,0.15); 
        color: #f97316 !important; 
    }
    
    /* Light mode dropdown */
    html:not(.dark) .dd-menu { 
        background: white; 
        border: 1px solid #e5e7eb; 
        box-shadow: 0 20px 40px rgba(0,0,0,0.1); 
    }
    html:not(.dark) .dd-item { 
        color: #21212e !important; 
    }
    html:not(.dark) .dd-item:hover { 
        background: #21212e; 
        color: white !important; 
    }

    /* Mobile menu */
    #mob-menu { 
        max-height: 0; 
        overflow: hidden; 
        transition: max-height 0.35s ease; 
    }
    #mob-menu.open { 
        max-height: 800px; 
    }
    
    /* Dark mode mobile menu links */
    .dark #mob-menu .nav-link {
        color: #ffffff !important;
    }
    .dark #mob-menu .nav-link:hover {
        background: rgba(249,115,22,0.15);
        color: #f97316 !important;
    }
    
    /* Light mode mobile menu links */
    html:not(.dark) #mob-menu .nav-link {
        color: #21212e !important;
    }
    html:not(.dark) #mob-menu .nav-link:hover {
        background: #21212e;
        color: white !important;
    }
    
    /* Badge */
    .badge { 
        background: #f97316; 
        color: white; 
        font-size: 0.6rem; 
        font-weight: 800; 
        padding: 1px 5px; 
        border-radius: 99px; 
    }

    /* Global background transition untuk seluruh halaman */
    body {
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    
    /* Dark mode body */
    .dark body {
        background-color: #21212e !important;
        color: #ffffff !important;
    }
    
    /* Light mode body */
    html:not(.dark) body {
        background-color: #f9fafb !important;
        color: #21212e !important;
    }
    
    /* Container utama jika ada */
    .main-container {
        transition: background-color 0.3s ease;
    }
    
    .dark .main-container {
        background-color: #21212e !important;
    }
    
    html:not(.dark) .main-container {
        background-color: #f9fafb !important;
    }
</style>

    <div class="max-w-screen-xl mx-auto px-4">

        {{-- TOP BAR --}}
        <div class="flex items-center justify-between h-16 relative">

            {{-- LOGO --}}
            <a href="/" class="flex items-center gap-2 shrink-0 z-10">
                <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
                    <i data-lucide="palette" class="w-4 h-4 text-white"></i>
                </div>
                <span class="font-bold text-lg" :class="isDark ? 'text-white' : 'text-[#21212e]'">ArtSpace</span>
            </a>

            {{-- DESKTOP NAV — ABSOLUTE CENTER --}}
            <nav class="hidden lg:flex items-center gap-0.5 absolute left-1/2 -translate-x-1/2">

                <a href="{{ route('artist.profile') }}"
                   class="nav-link"
                   :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="user-circle" class="w-4 h-4"></i>Profile
                </a>

                <a href="{{ route('portfolio.index') }}"
                   class="nav-link"
                   :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="image" class="w-4 h-4"></i>Portfolio
                </a>

                <a href="{{ route('commission.index') }}"
                   class="nav-link"
                   :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="brush" class="w-4 h-4"></i>Commission
                </a>

                <a href="{{ route('reviews.index') }}"
                   class="nav-link"
                   :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="star" class="w-4 h-4"></i>Reviews
                </a>

                {{-- Manage Reviews: artist only --}}
                @if(session('user_role') === 'artist')
                <a href="{{ route('reviews.manage') }}"
                   class="nav-link"
                   :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>Manage Reviews
                </a>
                @endif

                {{-- ORDERS DROPDOWN --}}
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open"
                        class="nav-link flex items-center gap-1"
                        :class="isDark ? 'text-white' : 'text-[#21212e]'">
                        <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                        <span>Orders</span>
                        <i data-lucide="chevron-down" class="w-3 h-3 transition-transform duration-200" 
                        :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    
                    {{-- Dropdown menu dengan styling yang lebih baik --}}
                    <div class="dd-menu" 
                        :class="open ? 'open' : ''"
                        style="min-width: 200px; left: 50%; transform: translateX(-50%);">
                        
                        <div class="py-1">
                            {{-- Kalau artist login --}}
                            @if(session('user_role') === 'artist')
                            <a href="{{ route('orders.artist') }}"
                                class="dd-item flex items-center gap-3 px-4 py-2.5 text-sm">
                                <i data-lucide="layers" class="w-4 h-4"></i>
                                <span>All Orders</span>
                                @if(($orderCounts['all'] ?? 0) > 0)
                                <span class="ml-auto text-xs px-1.5 py-0.5 rounded-full font-bold"
                                    style="background:rgba(249,115,22,0.15);color:#f97316;">
                                    {{ $orderCounts['all'] }}
                                </span>
                                @endif
                            </a>

                            <a href="{{ route('orders.artist', ['status' => 'pending']) }}"
                                class="dd-item flex items-center gap-3 px-4 py-2.5 text-sm">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                <span>Pending</span>
                                @if(($orderCounts['pending'] ?? 0) > 0)
                                <span class="ml-auto bg-orange-500 text-white text-xs px-1.5 py-0.5 rounded-full font-bold">
                                    {{ $orderCounts['pending'] }}
                                </span>
                                @endif
                            </a>

                            <a href="{{ route('orders.artist', ['status' => 'confirmed']) }}"
                                class="dd-item flex items-center gap-3 px-4 py-2.5 text-sm">
                                <i data-lucide="brush" class="w-4 h-4"></i>
                                <span>In Progress</span>
                                @if(($orderCounts['confirmed'] ?? 0) > 0)
                                <span class="ml-auto text-xs px-1.5 py-0.5 rounded-full font-bold"
                                    style="background:rgba(59,130,246,0.15);color:#3b82f6;">
                                    {{ $orderCounts['confirmed'] }}
                                </span>
                                @endif
                            </a>

                            <a href="{{ route('orders.artist', ['status' => 'paid']) }}"
                                class="dd-item flex items-center gap-3 px-4 py-2.5 text-sm">
                                <i data-lucide="wallet" class="w-4 h-4"></i>
                                <span>Perlu Dikonfirmasi</span>
                                @if(($orderCounts['paid'] ?? 0) > 0)
                                <span class="ml-auto bg-yellow-500 text-white text-xs px-1.5 py-0.5 rounded-full font-bold">
                                    {{ $orderCounts['paid'] }}
                                </span>
                                @endif
                            </a>

                            <a href="{{ route('orders.artist', ['status' => 'completed']) }}"
                                class="dd-item flex items-center gap-3 px-4 py-2.5 text-sm">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                <span>Completed</span>
                                @if(($orderCounts['completed'] ?? 0) > 0)
                                <span class="ml-auto text-xs px-1.5 py-0.5 rounded-full font-bold"
                                    style="background:rgba(34,197,94,0.15);color:#22c55e;">
                                    {{ $orderCounts['completed'] }}
                                </span>
                                @endif
                            </a>

                            {{-- Kalau client login --}}
                            @elseif(session('user_role') === 'client')
                            <a href="{{ route('orders.client') }}"
                                class="dd-item flex items-center gap-3 px-4 py-2.5 text-sm">
                                <i data-lucide="layers" class="w-4 h-4"></i>
                                <span>My Orders</span>
                                @if(($orderCounts['all'] ?? 0) > 0)
                                <span class="ml-auto text-xs px-1.5 py-0.5 rounded-full font-bold"
                                    style="background:rgba(249,115,22,0.15);color:#f97316;">
                                    {{ $orderCounts['all'] }}
                                </span>
                                @endif
                            </a>

                            <a href="{{ route('orders.client') }}"
                                class="dd-item flex items-center gap-3 px-4 py-2.5 text-sm">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                <span>Active</span>
                                @if(($orderCounts['active'] ?? 0) > 0)
                                <span class="ml-auto bg-orange-500 text-white text-xs px-1.5 py-0.5 rounded-full font-bold">
                                    {{ $orderCounts['active'] }}
                                </span>
                                @endif
                            </a>

                            <a href="{{ route('orders.client') }}"
                                class="dd-item flex items-center gap-3 px-4 py-2.5 text-sm">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                <span>Completed</span>
                                @if(($orderCounts['completed'] ?? 0) > 0)
                                <span class="ml-auto text-xs px-1.5 py-0.5 rounded-full font-bold"
                                    style="background:rgba(34,197,94,0.15);color:#22c55e;">
                                    {{ $orderCounts['completed'] }}
                                </span>
                                @endif
                            </a>

                            {{-- Belum login --}}
                            @else
                                <a href="{{ route('orders.client') }}" 
                                class="dd-item flex items-center gap-3 px-4 py-2.5 text-sm">
                                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                    <span>My Orders</span>
                                </a>
                                
                                <a href="{{ route('orders.client') }}?status=active" 
                                class="dd-item flex items-center gap-3 px-4 py-2.5 text-sm">
                                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                                    <span>Track Order</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- CHAT --}}
                @if(session('user_id'))
                <a href="{{ route('chat.index') }}"
                class="nav-link"
                :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>Chat
                </a>
                @else
                <button @click="openLogin = true"
                class="nav-link"
                :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>Chat
                </button>
                @endif

                <a href="{{ route('faq.index') }}"
                   class="nav-link"
                   :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="help-circle" class="w-4 h-4"></i>FAQ
                </a>

            </nav>

            {{-- RIGHT: THEME + LOGIN/AVATAR + HAMBURGER --}}
            <div class="flex items-center gap-2 z-10">

                {{-- Theme toggle --}}
                <button @click="toggleTheme()"
                    class="w-9 h-9 rounded-lg flex items-center justify-center transition-all"
                    :class="isDark
                        ? 'bg-[#2a2a3d] text-orange-400 hover:bg-orange-500 hover:text-white'
                        : 'bg-gray-100 text-[#21212e] hover:bg-[#21212e] hover:text-white'"
                >
                    <i data-lucide="sun" class="w-4 h-4" x-show="isDark"></i>
                    <i data-lucide="moon" class="w-4 h-4" x-show="!isDark"></i>
                </button>

                {{-- Sudah login: tampilkan avatar --}}
@if(session('user_id'))
<div class="relative" x-data="{ open: false }" @click.away="open = false">
    <button @click="open = !open" class="flex items-center gap-2 focus:outline-none">
        {{-- Profile foto --}}
        <img src="https://ui-avatars.com/api/?name={{ urlencode(session('user_name') ?? 'User') }}&background=f97316&color=fff"
             class="w-9 h-9 rounded-full object-cover ring-2 ring-orange-500"
             alt="{{ session('user_name') ?? 'User' }}">
        
        {{-- Dropdown arrow --}}
        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" 
           :class="open ? 'rotate-180' : ''"
           :class="isDark ? 'text-gray-400' : 'text-gray-500'"></i>
    </button>
    
    {{-- Dropdown menu --}}
    <div class="dd-menu" 
         style="left: auto; right: 0; transform: none; min-width: 180px;"
         :class="open ? 'open' : ''">
        
        {{-- User info --}}
        <div class="px-3 py-2 border-b" :class="isDark ? 'border-[#3a3a50]' : 'border-gray-100'">
            <p class="text-xs font-bold truncate" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                {{ session('user_name') ?? 'User' }}
            </p>
            <p class="text-xs capitalize mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                {{ session('user_role') ?? 'guest' }}
            </p>
        </div>
        
        {{-- Logout button --}}
        <form method="POST" action="{{ route('logout.post') }}" class="p-1">
            @csrf
            <button type="submit" 
                    class="dd-item w-full flex items-center gap-2 px-3 py-2 text-sm"
                    :class="isDark ? 'text-red-400 hover:text-white hover:bg-red-500' : 'text-red-500 hover:text-white hover:bg-red-500'">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</div>

{{-- Belum login: tampilkan tombol Login --}}
@else
<button @click="openLogin = true"
    class="nav-link bg-orange-500 text-white hover:bg-orange-600 flex items-center gap-2">
    <i data-lucide="log-in" class="w-4 h-4"></i>
    <span>Login</span>
</button>
@endif

                {{-- Hamburger --}}
                <button @click="toggleMob()"
                    class="lg:hidden w-9 h-9 rounded-lg flex items-center justify-center"
                    :class="isDark ? 'text-white hover:bg-[#2a2a3d]' : 'text-[#21212e] hover:bg-gray-100'">
                    <i data-lucide="menu" class="w-5 h-5" x-show="!mobOpen"></i>
                    <i data-lucide="x" class="w-5 h-5" x-show="mobOpen"></i>
                </button>
            </div>
        </div>

        {{-- MOBILE MENU --}}
        <div id="mob-menu">
            <nav class="pb-4 flex flex-col gap-0.5">
                <a href="{{ route('artist.profile') }}" class="nav-link" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="user-circle" class="w-4 h-4"></i>Profile Artist
                </a>
                <a href="{{ route('portfolio.index') }}" class="nav-link" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="image" class="w-4 h-4"></i>Portfolio
                </a>
                <a href="{{ route('commission.index') }}" class="nav-link" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="brush" class="w-4 h-4"></i>Commission
                </a>
                <a href="{{ route('reviews.index') }}" class="nav-link" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="star" class="w-4 h-4"></i>Rating & Reviews
                </a>

                @if(session('user_role') === 'artist')
                <a href="{{ route('reviews.manage') }}" class="nav-link" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>Manage Reviews
                </a>
                <a href="{{ route('orders.artist') }}" class="nav-link" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="layers" class="w-4 h-4"></i>Manage Orders
                </a>
                @else
                <a href="{{ route('orders.client') }}" class="nav-link" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>My Orders
                </a>
                <a href="{{ route('orders.client') }}?status=active" class="nav-link" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>Track Order
                </a>
                @endif

                @if(session('user_id'))
                <a href="{{ route('chat.index') }}" class="nav-link" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>Chat
                </a>
                @else
                <button @click="openLogin = true; mobOpen = false; document.getElementById('mob-menu').classList.remove('open')"
                    class="nav-link w-full text-left"
                    :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>Chat
                </button>
                @endif
                <a href="{{ route('faq.index') }}" class="nav-link" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="help-circle" class="w-4 h-4"></i>FAQ
                </a>
            </nav>
        </div>

    </div>

    {{-- MODAL LOGIN / REGISTER --}}
<div x-show="openLogin"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[999] flex items-center justify-center px-4"
    style="display:none;">

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="openLogin = false"></div>

    {{-- Modal Box --}}
    <div class="relative w-full max-w-sm rounded-2xl z-10 overflow-hidden"
        :class="isDark ? 'bg-[#2a2a3d] border border-[#3a3a50]' : 'bg-white border border-gray-200'">

        {{-- Close --}}
        <button @click="openLogin = false"
            class="absolute top-4 right-4 w-7 h-7 rounded-lg flex items-center justify-center transition-all z-10"
            :class="isDark ? 'text-gray-400 hover:bg-[#21212e] hover:text-white' : 'text-gray-400 hover:bg-gray-100'">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>

        {{-- Tab switcher --}}
        <div class="flex border-b" :class="isDark ? 'border-[#3a3a50]' : 'border-gray-100'">
            <button @click="authTab = 'login'"
                class="flex-1 py-3.5 text-xs font-bold transition-all"
                :class="authTab === 'login'
                    ? 'text-orange-500 border-b-2 border-orange-500'
                    : (isDark ? 'text-gray-500 hover:text-gray-300' : 'text-gray-400 hover:text-gray-600')">
                Login
            </button>
            <button @click="authTab = 'register'"
                class="flex-1 py-3.5 text-xs font-bold transition-all"
                :class="authTab === 'register'
                    ? 'text-orange-500 border-b-2 border-orange-500'
                    : (isDark ? 'text-gray-500 hover:text-gray-300' : 'text-gray-400 hover:text-gray-600')">
                Daftar
            </button>
        </div>

        <div class="p-6">

            {{-- ===== TAB LOGIN ===== --}}
            <div x-show="authTab === 'login'">
                <h2 class="text-base font-bold mb-0.5" :class="isDark ? 'text-white' : 'text-[#21212e]'">Selamat Datang!</h2>
                <p class="text-xs mb-4" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Masuk ke akun ArtSpace kamu</p>

                @if(session('login_error'))
                <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs font-bold">
                    ⚠️ {{ session('login_error') }}
                </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="text-xs font-bold mb-1.5 block" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Email</label>
                        <div class="flex items-center gap-2 rounded-xl border px-3 transition-all"
                            :class="isDark ? 'bg-[#21212e] border-[#3a3a50] focus-within:border-orange-500' : 'bg-gray-50 border-gray-200 focus-within:border-[#21212e]'">
                            <i data-lucide="mail" class="w-4 h-4 text-gray-400 shrink-0"></i>
                            <input type="email" name="email" required placeholder="email@example.com"
                                class="bg-transparent py-2.5 text-sm outline-none w-full"
                                :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#21212e] placeholder-gray-400'">
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="text-xs font-bold mb-1.5 block" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Password</label>
                        <div class="flex items-center gap-2 rounded-xl border px-3 transition-all"
                            :class="isDark ? 'bg-[#21212e] border-[#3a3a50] focus-within:border-orange-500' : 'bg-gray-50 border-gray-200 focus-within:border-[#21212e]'">
                            <i data-lucide="lock" class="w-4 h-4 text-gray-400 shrink-0"></i>
                            <input type="password" name="password" required placeholder="••••••••"
                                class="bg-transparent py-2.5 text-sm outline-none w-full"
                                :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#21212e] placeholder-gray-400'">
                        </div>
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center gap-2 mb-5">
                        <input type="checkbox" name="remember" id="rememberMe"
                            class="w-4 h-4 rounded accent-orange-500 cursor-pointer">
                        <label for="rememberMe" class="text-xs cursor-pointer"
                            :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                            Ingat saya selama 30 hari
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full py-2.5 rounded-xl bg-orange-500 text-white font-bold text-sm hover:bg-orange-600 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="log-in" class="w-4 h-4"></i> Login
                    </button>
                </form>

                <p class="text-center text-xs mt-4" :class="isDark ? 'text-gray-500' : 'text-gray-400'">
                    Belum punya akun?
                    <button @click="authTab = 'register'" class="text-orange-500 font-bold hover:underline">Daftar sekarang</button>
                </p>
            </div>

            {{-- ===== TAB REGISTER ===== --}}
            <div x-show="authTab === 'register'">
                <h2 class="text-base font-bold mb-0.5" :class="isDark ? 'text-white' : 'text-[#21212e]'">Buat Akun</h2>
                <p class="text-xs mb-4" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Daftar sebagai client ArtSpace</p>

                @if($errors->any())
                <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs">
                    @foreach($errors->all() as $err)
                    <p>⚠️ {{ $err }}</p>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('register.post') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="text-xs font-bold mb-1.5 block" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Nama Lengkap</label>
                        <div class="flex items-center gap-2 rounded-xl border px-3 transition-all"
                            :class="isDark ? 'bg-[#21212e] border-[#3a3a50] focus-within:border-orange-500' : 'bg-gray-50 border-gray-200 focus-within:border-[#21212e]'">
                            <i data-lucide="user" class="w-4 h-4 text-gray-400 shrink-0"></i>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                placeholder="Nama kamu"
                                class="bg-transparent py-2.5 text-sm outline-none w-full"
                                :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#21212e] placeholder-gray-400'">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-xs font-bold mb-1.5 block" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Email</label>
                        <div class="flex items-center gap-2 rounded-xl border px-3 transition-all"
                            :class="isDark ? 'bg-[#21212e] border-[#3a3a50] focus-within:border-orange-500' : 'bg-gray-50 border-gray-200 focus-within:border-[#21212e]'">
                            <i data-lucide="mail" class="w-4 h-4 text-gray-400 shrink-0"></i>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                placeholder="email@example.com"
                                class="bg-transparent py-2.5 text-sm outline-none w-full"
                                :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#21212e] placeholder-gray-400'">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-xs font-bold mb-1.5 block" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Password</label>
                        <div class="flex items-center gap-2 rounded-xl border px-3 transition-all"
                            :class="isDark ? 'bg-[#21212e] border-[#3a3a50] focus-within:border-orange-500' : 'bg-gray-50 border-gray-200 focus-within:border-[#21212e]'">
                            <i data-lucide="lock" class="w-4 h-4 text-gray-400 shrink-0"></i>
                            <input type="password" name="password" required placeholder="Min. 6 karakter"
                                class="bg-transparent py-2.5 text-sm outline-none w-full"
                                :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#21212e] placeholder-gray-400'">
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="text-xs font-bold mb-1.5 block" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Konfirmasi Password</label>
                        <div class="flex items-center gap-2 rounded-xl border px-3 transition-all"
                            :class="isDark ? 'bg-[#21212e] border-[#3a3a50] focus-within:border-orange-500' : 'bg-gray-50 border-gray-200 focus-within:border-[#21212e]'">
                            <i data-lucide="lock" class="w-4 h-4 text-gray-400 shrink-0"></i>
                            <input type="password" name="password_confirmation" required placeholder="Ulangi password"
                                class="bg-transparent py-2.5 text-sm outline-none w-full"
                                :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#21212e] placeholder-gray-400'">
                        </div>
                    </div>

                    {{-- Role info --}}
                    <div class="flex items-center gap-2 p-3 rounded-xl mb-4"
                        :class="isDark ? 'bg-[#21212e]' : 'bg-orange-50'">
                        <i data-lucide="info" class="w-4 h-4 text-orange-500 shrink-0"></i>
                        <p class="text-xs" :class="isDark ? 'text-gray-400' : 'text-gray-600'">
                            Akun baru otomatis terdaftar sebagai <span class="font-bold text-orange-500">Client</span>
                        </p>
                    </div>

                    <button type="submit"
                        class="w-full py-2.5 rounded-xl bg-orange-500 text-white font-bold text-sm hover:bg-orange-600 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="user-plus" class="w-4 h-4"></i> Buat Akun
                    </button>
                </form>

                <p class="text-center text-xs mt-4" :class="isDark ? 'text-gray-500' : 'text-gray-400'">
                    Sudah punya akun?
                    <button @click="authTab = 'login'" class="text-orange-500 font-bold hover:underline">Login di sini</button>
                </p>
            </div>

        </div>
    </div>
</div>

    <script>
        function navApp() {
            return {
                isDark: false,
                mobOpen: false,
                openLogin: {{ session('show_login_modal') || $errors->any() ? 'true' : 'false' }},
                authTab: '{{ $errors->any() ? "register" : "login" }}',
                
                init() {
                // Prioritas: localStorage > cookie > system preference
                const savedTheme  = localStorage.getItem('theme');
                const cookieTheme = document.cookie.split('; ')
                    .find(r => r.startsWith('theme='))?.split('=')[1];
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                this.isDark = savedTheme
                    ? savedTheme === 'dark'
                    : cookieTheme
                        ? cookieTheme === 'dark'
                        : prefersDark;

                // Sync keduanya
                localStorage.setItem('theme', this.isDark ? 'dark' : 'light');

                document.documentElement.classList.toggle('dark', this.isDark);
                this.applyBodyBackground();

                // Observer
                const observer = new MutationObserver(() => {
                    const newIsDark = document.documentElement.classList.contains('dark');
                    if (this.isDark !== newIsDark) {
                        this.isDark = newIsDark;
                        this.applyBodyBackground();
                    }
                });
                observer.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            },
                
                applyBodyBackground() {
                    // Langsung apply background ke body
                    if (this.isDark) {
                        document.body.style.backgroundColor = '#21212e';
                        document.body.style.color = '#ffffff';
                    } else {
                        document.body.style.backgroundColor = '#f9fafb';
                        document.body.style.color = '#21212e';
                    }
                },
                
                toggleTheme() {
                    this.isDark = !this.isDark;
                    const theme = this.isDark ? 'dark' : 'light';

                    // Simpan ke localStorage
                    localStorage.setItem('theme', theme);

                    // Simpan ke cookie sebagai backup (365 hari)
                    const expires = new Date();
                    expires.setFullYear(expires.getFullYear() + 1);
                    document.cookie = `theme=${theme}; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;

                    document.documentElement.classList.toggle('dark', this.isDark);
                    this.applyBodyBackground();
                },
                                
                toggleMob() {
                    this.mobOpen = !this.mobOpen;
                    const mobMenu = document.getElementById('mob-menu');
                    if (mobMenu) {
                        mobMenu.classList.toggle('open', this.mobOpen);
                    }
                }
            }
        }

        // Jalankan saat halaman pertama kali load
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi theme
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = savedTheme ? savedTheme === 'dark' : prefersDark;
            
            // Apply ke HTML dan body
            document.documentElement.classList.toggle('dark', isDark);
            
            if (isDark) {
                document.body.style.backgroundColor = '#21212e';
                document.body.style.color = '#ffffff';
            } else {
                document.body.style.backgroundColor = '#f9fafb';
                document.body.style.color = '#21212e';
            }
        });
    </script>
</header>