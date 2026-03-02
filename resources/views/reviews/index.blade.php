@extends('layouts.app')
@section('title', 'Reviews — ArtSpace')

@push('styles')
<style>
    .masonry-grid {
    columns: 1;
    gap: 1rem;
    }
    @media (min-width: 768px) {
        .masonry-grid { columns: 2; }
    }
    @media (min-width: 1280px) {
        .masonry-grid { columns: 3; }
    }
    .masonry-item {
        break-inside: avoid;
        margin-bottom: 1rem;
        display: inline-block;
        width: 100%;
    }
    .review-card {
        border-radius: 18px; border: 1px solid;
        transition: all 0.2s; overflow: hidden;
    }
    .review-card:hover { transform: translateY(-2px); }
    .dark .review-card { background: #2a2a3d; border-color: #3a3a50; }
    html:not(.dark) .review-card { background: white; border-color: #e5e7eb; box-shadow: 0 2px 12px rgba(0,0,0,0.05); }

    .star-filled { color: #f97316; }
    .star-empty  { color: #4b5563; }

    .tag-chip {
        display: inline-flex; align-items: center;
        padding: 3px 10px; border-radius: 99px;
        font-size: 0.68rem; font-weight: 700;
    }
    .tag-good { background: rgba(34,197,94,0.12); color: #22c55e; }
    .tag-bad  { background: rgba(239,68,68,0.12);  color: #ef4444; }

    .rating-bar { height: 6px; border-radius: 99px; overflow: hidden; }
    .dark .rating-bar { background: #3a3a50; }
    html:not(.dark) .rating-bar { background: #f3f4f6; }
    .rating-fill { height: 100%; border-radius: 99px; background: #f97316; transition: width 0.5s; }
</style>
@endpush

@section('content')
@php
    $badTags = ['Komunikasi kurang','Pengerjaan lambat','Kurang sesuai request','Revisi tidak ditangani','Kurang responsif','Tidak sesuai ekspektasi'];
@endphp

<div x-data="reviewsPage()" x-init="init()">

    {{-- Header & Stats --}}
    <div class="flex flex-col lg:flex-row gap-6 mb-8">

        {{-- Title --}}
        <div class="flex-1">
            <h1 class="text-xl font-bold mb-1" :class="isDark ? 'text-white' : 'text-[#21212e]'">Reviews</h1>
            <p class="text-xs" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                Apa kata client tentang karya kami
            </p>
        </div>

        {{-- Rating summary --}}
        <div class="rounded-2xl p-5 border min-w-[280px]"
            :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
            <div class="flex items-center gap-4 mb-4">
                <div class="text-center">
                    <p class="text-4xl font-bold text-orange-500">{{ number_format($avgRating, 1) }}</p>
                    <div class="flex gap-0.5 mt-1 justify-center">
                        @for($i = 1; $i <= 5; $i++)
                        <i data-lucide="star" class="w-4 h-4 {{ $i <= round($avgRating) ? 'star-filled' : 'star-empty' }}"
                            style="{{ $i <= round($avgRating) ? 'fill:#f97316' : '' }}"></i>
                        @endfor
                    </div>
                    <p class="text-xs mt-1" :class="isDark ? 'text-gray-400' : 'text-gray-500'">{{ $total }} ulasan</p>
                </div>
                <div class="flex-1">
                    @for($i = 5; $i >= 1; $i--)
                    @php $pct = $total > 0 ? ($ratingCounts[$i] / $total) * 100 : 0; @endphp
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs w-3 shrink-0" :class="isDark ? 'text-gray-400' : 'text-gray-500'">{{ $i }}</span>
                        <i data-lucide="star" class="w-3 h-3 star-filled shrink-0" style="fill:#f97316"></i>
                        <div class="rating-bar flex-1">
                            <div class="rating-fill" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="text-xs w-4 text-right shrink-0" :class="isDark ? 'text-gray-400' : 'text-gray-500'">{{ $ratingCounts[$i] }}</span>
                    </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    {{-- Filter rating --}}
    <div class="flex gap-2 flex-wrap mb-6">
        <button class="px-4 py-1.5 rounded-full text-xs font-bold transition-all"
            :class="filterRating === 0
                ? 'bg-orange-500 text-white'
                : (isDark ? 'bg-[#2a2a3d] text-gray-400 hover:text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200')"
            @click="filterRating = 0">Semua</button>
        @for($i = 5; $i >= 1; $i--)
        <button class="px-4 py-1.5 rounded-full text-xs font-bold transition-all flex items-center gap-1"
            :class="filterRating === {{ $i }}
                ? 'bg-orange-500 text-white'
                : (isDark ? 'bg-[#2a2a3d] text-gray-400 hover:text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200')"
            @click="filterRating = {{ $i }}">
            {{ $i }} <i data-lucide="star" style="width:11px;height:11px;fill:currentColor;"></i>
        </button>
        @endfor
    </div>

    @if($reviews->isEmpty())
    <div class="flex flex-col items-center justify-center py-24">
        <div class="w-16 h-16 rounded-2xl bg-orange-500/10 flex items-center justify-center mb-4">
            <i data-lucide="star" class="w-8 h-8 text-orange-500"></i>
        </div>
        <p class="font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Belum ada review</p>
    </div>
    @else
    <div class="masonry-grid">
        @foreach($reviews as $r)
        <div class="masonry-item review-card"
            x-show="filterRating === 0 || filterRating === {{ $r->rating }}">

            {{-- Hasil karya --}}
            @if($r->result_image)
            <div class="w-full h-48 overflow-hidden">
                <img src="{{ Storage::url($r->result_image) }}"
                    class="w-full h-full object-cover cursor-pointer"
                    onclick="window.open(this.src)">
            </div>
            @endif

            <div class="p-4">
                {{-- Header: avatar + nama + rating --}}
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div class="flex items-center gap-2">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($r->client_name) }}&background=7c3aed&color=fff"
                            class="w-8 h-8 rounded-full object-cover shrink-0">
                        <div>
                            <p class="text-xs font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">{{ $r->client_name }}</p>
                            <p class="text-xs" :class="isDark ? 'text-gray-500' : 'text-gray-400'">
                                {{ \Carbon\Carbon::parse($r->created_at)->format('d M Y') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-0.5 shrink-0">
                        @for($i = 1; $i <= 5; $i++)
                        <i data-lucide="star" class="w-3.5 h-3.5 {{ $i <= $r->rating ? 'star-filled' : 'star-empty' }}"
                            style="{{ $i <= $r->rating ? 'fill:#f97316' : '' }}"></i>
                        @endfor
                    </div>
                </div>

                {{-- Commission title --}}
                <p class="text-xs px-2 py-1 rounded-lg inline-block mb-2 font-bold"
                    style="background:rgba(249,115,22,0.1);color:#f97316;">
                    {{ $r->commission_title }}
                </p>

                {{-- Komentar --}}
                @if($r->comment)
                <p class="text-xs leading-relaxed mb-3" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
                    "{{ $r->comment }}"
                </p>
                @endif

                {{-- Quick tags --}}
                @if(!empty($r->quick_tags))
                <div class="flex flex-wrap gap-1">
                    @foreach($r->quick_tags as $tag)
                    @php $isBad = in_array($tag, $badTags); @endphp
                    <span class="tag-chip {{ $isBad ? 'tag-bad' : 'tag-good' }}">
                        {{ $isBad ? '👎' : '👍' }} {{ $tag }}
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Client: tombol buat review dari order completed --}}
    @if(session('user_role') === 'client')
    <div class="mt-8 p-4 rounded-2xl border text-center"
        :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-gray-50 border-gray-200'">
        <p class="text-sm font-bold mb-2" :class="isDark ? 'text-white' : 'text-[#21212e]'">
            Punya pengalaman dengan kami?
        </p>
        <a href="{{ route('orders.client') }}"
            class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition-all">
            <i data-lucide="star" class="w-4 h-4"></i> Tulis Review
        </a>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function reviewsPage() {
    return {
        isDark: false,
        filterRating: 0,
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