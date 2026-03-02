@extends('layouts.app')
@section('title', 'Portfolio — ArtSpace')

@push('styles')
<style>
    .masonry-grid { columns: 1; gap: 1rem; }
    @media (min-width: 640px)  { .masonry-grid { columns: 2; } }
    @media (min-width: 1024px) { .masonry-grid { columns: 3; } }
    @media (min-width: 1280px) { .masonry-grid { columns: 4; } }

    .masonry-item {
        break-inside: avoid;
        margin-bottom: 1rem;
        display: inline-block;
        width: 100%;
    }

    .portfolio-card {
        border-radius: 16px; overflow: hidden; border: 1px solid;
        cursor: pointer; transition: all 0.25s;
        position: relative;
    }
    .dark .portfolio-card { background: #2a2a3d; border-color: #3a3a50; }
    html:not(.dark) .portfolio-card { background: white; border-color: #e5e7eb; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
    .portfolio-card:hover { transform: translateY(-3px); }
    .dark .portfolio-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.3); }
    html:not(.dark) .portfolio-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.12); }

    /* Overlay hover */
    .card-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 50%);
        opacity: 0; transition: opacity 0.25s;
        display: flex; align-items: flex-end; padding: 16px;
    }
    .portfolio-card:hover .card-overlay { opacity: 1; }

    /* Expand panel */
    .expand-panel {
        overflow: hidden; transition: max-height 0.4s ease, opacity 0.3s ease;
        max-height: 0; opacity: 0;
    }
    .expand-panel.open { max-height: 800px; opacity: 1; }

    /* Filter pill */
    .filter-pill {
        padding: 5px 14px; border-radius: 99px;
        font-size: 0.72rem; font-weight: 700;
        cursor: pointer; transition: all 0.2s;
        border: 1px solid transparent; white-space: nowrap;
    }
    .dark .filter-pill { background: #2a2a3d; color: #9ca3af; border-color: #3a3a50; }
    html:not(.dark) .filter-pill { background: #f3f4f6; color: #6b7280; border-color: #e5e7eb; }
    .filter-pill:hover, .filter-pill.active { background: #f97316 !important; color: white !important; border-color: #f97316 !important; }

    .tag-chip {
        display: inline-flex; padding: 2px 8px; border-radius: 99px;
        font-size: 0.65rem; font-weight: 700;
        background: rgba(249,115,22,0.12); color: #f97316;
    }

    /* Thumbnail strip */
    .thumb-strip { display: flex; gap: 6px; overflow-x: auto; scrollbar-width: none; }
    .thumb-strip::-webkit-scrollbar { display: none; }
    .thumb { width: 52px; height: 52px; border-radius: 8px; object-fit: cover; cursor: pointer; border: 2px solid transparent; transition: all 0.15s; flex-shrink: 0; }
    .thumb.active, .thumb:hover { border-color: #f97316; }

    /* View counter */
    .view-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px; border-radius: 99px;
        font-size: 0.65rem; font-weight: 700;
        background: rgba(0,0,0,0.4); color: white;
        position: absolute; top: 10px; right: 10px;
    }
</style>
@endpush

@section('content')
<div x-data="portfolioPage()" x-init="init()">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Portfolio</h1>
            <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                {{ $portfolios->count() }} karya tersedia
            </p>
        </div>

        @if(session('user_role') === 'artist')
        <div class="flex gap-2">
            <a href="{{ route('portfolio.create') }}"
                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Karya
            </a>
            <a href="{{ route('portfolio.manage') }}"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all"
                :class="isDark ? 'bg-[#2a2a3d] text-white hover:bg-[#3a3a50]' : 'bg-gray-100 text-[#21212e] hover:bg-gray-200'">
                <i data-lucide="settings" class="w-4 h-4"></i> Manage
            </a>
        </div>
        @endif
    </div>

    {{-- Filter & Search Bar --}}
    <div class="rounded-2xl p-4 border mb-6 flex flex-col gap-3"
        :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">

        {{-- Search + Sort --}}
        <div class="flex gap-2">
            <div class="flex-1 flex items-center gap-2 px-3 py-2 rounded-xl"
                :class="isDark ? 'bg-[#21212e]' : 'bg-gray-100'">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 shrink-0"></i>
                <input type="text" placeholder="Cari karya..." x-model="search"
                    @input="filterPortfolios()"
                    class="bg-transparent text-xs outline-none w-full"
                    :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#21212e] placeholder-gray-400'">
            </div>
            <select x-model="sort" @change="filterPortfolios()"
                class="px-3 py-2 rounded-xl text-xs font-bold outline-none border"
                :class="isDark ? 'bg-[#21212e] border-[#3a3a50] text-white' : 'bg-gray-100 border-gray-200 text-[#21212e]'">
                <option value="newest">Terbaru</option>
                <option value="oldest">Terlama</option>
                <option value="popular">Terpopuler</option>
            </select>
        </div>

        {{-- Kategori --}}
        <div class="flex gap-2 flex-wrap">
            <button class="filter-pill" :class="activeCategory === '' ? 'active' : ''"
                @click="activeCategory = ''; filterPortfolios()">Semua</button>
            @foreach($categories as $cat)
            <button class="filter-pill" :class="activeCategory === '{{ $cat }}' ? 'active' : ''"
                @click="activeCategory = '{{ $cat }}'; filterPortfolios()">{{ $cat }}</button>
            @endforeach
        </div>

        {{-- Software --}}
        <div class="flex gap-2 flex-wrap">
            <button class="filter-pill" :class="activeSoftware === '' ? 'active' : ''"
                @click="activeSoftware = ''; filterPortfolios()">
                <i data-lucide="monitor" style="width:11px;height:11px;display:inline;"></i> All Software
            </button>
            @foreach($softwares as $sw)
            <button class="filter-pill" :class="activeSoftware === '{{ $sw }}' ? 'active' : ''"
                @click="activeSoftware = '{{ $sw }}'; filterPortfolios()">{{ $sw }}</button>
            @endforeach
        </div>
    </div>

    {{-- Grid Masonry --}}
    @if($portfolios->isEmpty())
    <div class="flex flex-col items-center justify-center py-24">
        <div class="w-16 h-16 rounded-2xl bg-orange-500/10 flex items-center justify-center mb-4">
            <i data-lucide="image" class="w-8 h-8 text-orange-500"></i>
        </div>
        <p class="font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Belum ada portfolio</p>
    </div>
    @else

<div class="masonry-grid" id="portfolioGrid">
    @foreach($portfolios as $p)
    <div class="masonry-item"
        data-category="{{ $p->category }}"
        data-software="{{ $p->software ?? '' }}"
        data-title="{{ strtolower($p->title) }}"
        data-date="{{ $p->created_at }}"
        data-views="{{ $p->views }}">

        <a href="{{ route('portfolio.show', $p->id) }}" class="portfolio-card block">

            {{-- Cover image --}}
            @if($p->cover)
                @if($p->cover->file_type === 'video')
                <video src="{{ Storage::url($p->cover->file_path) }}"
                    class="w-full object-cover" style="max-height: 400px;" muted loop></video>
                @else
                <img src="{{ Storage::url($p->cover->file_path) }}"
                    class="w-full object-cover" style="max-height: 400px;" loading="lazy">
                @endif
            @else
            <div class="w-full flex items-center justify-center"
                style="height: 200px; background: linear-gradient(135deg, #f97316 0%, #a855f7 100%);">
                <i data-lucide="image" class="w-12 h-12 text-white opacity-50"></i>
            </div>
            @endif

            {{-- View counter --}}
            <div class="view-badge">
                <i data-lucide="eye" style="width:10px;height:10px;"></i>
                {{ number_format($p->views) }}
            </div>

            {{-- Hover overlay --}}
            <div class="card-overlay">
                <div class="flex-1 min-w-0">
                    <p class="text-white font-bold text-sm truncate">{{ $p->title }}</p>
                    <p class="text-white/70 text-xs">{{ $p->category }}
                        @if($p->software) · {{ $p->software }} @endif
                    </p>
                </div>
                <i data-lucide="arrow-right" class="w-5 h-5 text-white shrink-0 ml-2"></i>
            </div>
        </a>
    </div>
    @endforeach
</div>

{{-- No result --}}
<div id="noResult" class="hidden flex flex-col items-center justify-center py-16">
    <i data-lucide="search-x" class="w-10 h-10 text-gray-400 mb-3"></i>
    <p class="font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Tidak ada hasil</p>
    <p class="text-xs mt-1" :class="isDark ? 'text-gray-500' : 'text-gray-400'">Coba filter lain</p>
</div>
@endif

</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]')?.content;
let expandedId = null;

function toggleExpand(id) {
    const panel = document.getElementById('expand-' + id);

    // Kalau sudah terbuka, tutup
    if (expandedId === id) {
        panel.classList.remove('open');
        expandedId = null;
        return;
    }

    // Tutup yang sebelumnya
    if (expandedId !== null) {
        const prev = document.getElementById('expand-' + expandedId);
        if (prev) prev.classList.remove('open');
    }

    // Buka yang baru
    panel.classList.add('open');
    expandedId = id;

    // Track view
    fetch(`/portfolio/${id}/view`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF }
    });

    // Scroll ke card
    setTimeout(() => {
        document.getElementById('pitem-' + id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
}

function switchMedia(portfolioId, index, total) {
    for (let i = 0; i < total; i++) {
        const el    = document.getElementById(`media-${portfolioId}-${i}`);
        const thumb = document.getElementById(`thumb-${portfolioId}-${i}`);
        if (el)    el.style.display    = i === index ? '' : 'none';
        if (thumb) thumb.classList.toggle('active', i === index);
    }
}

function portfolioPage() {
    return {
        isDark: false,
        search: '',
        sort: 'newest',
        activeCategory: '',
        activeSoftware: '',
        filterPortfolios() {
            const items  = document.querySelectorAll('.masonry-item');
            let visible  = 0;
            const search = this.search.toLowerCase();
            const cat    = this.activeCategory;
            const sw     = this.activeSoftware;

            const grid = document.getElementById('portfolioGrid');
            const arr  = Array.from(items);
            arr.sort((a, b) => {
                if (this.sort === 'newest')  return new Date(b.dataset.date) - new Date(a.dataset.date);
                if (this.sort === 'oldest')  return new Date(a.dataset.date) - new Date(b.dataset.date);
                if (this.sort === 'popular') return parseInt(b.dataset.views) - parseInt(a.dataset.views);
                return 0;
            });
            arr.forEach(el => grid.appendChild(el));

            items.forEach(item => {
                const matchCat    = !cat || item.dataset.category === cat;
                const matchSw     = !sw  || item.dataset.software === sw;
                const matchSearch = !search || item.dataset.title.includes(search);
                const show        = matchCat && matchSw && matchSearch;
                item.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            document.getElementById('noResult').classList.toggle('hidden', visible > 0);
        },
        init() {
            this.isDark = document.documentElement.classList.contains('dark');
            new MutationObserver(() => {
                this.isDark = document.documentElement.classList.contains('dark');
            }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            lucide.createIcons();
        }
    }
}
</script>
@endpush