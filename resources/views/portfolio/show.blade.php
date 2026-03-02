@extends('layouts.app')
@section('title', $portfolio->title . ' — ArtSpace')

@push('styles')
<style>
    .media-main {
        border-radius: 20px; overflow: hidden;
        background: #0a0a0f;
        max-height: 600px; display: flex; align-items: center; justify-content: center;
    }
    .media-main img, .media-main video {
        width: 100%; max-height: 600px; object-fit: contain;
    }

    .thumb {
        width: 72px; height: 72px; border-radius: 12px;
        object-fit: cover; cursor: pointer; flex-shrink: 0;
        border: 2px solid transparent; transition: all 0.2s;
    }
    .thumb.active, .thumb:hover { border-color: #f97316; }
    .thumb-video {
        width: 72px; height: 72px; border-radius: 12px;
        background: #1a1a2e; display: flex; align-items: center; justify-content: center;
        cursor: pointer; flex-shrink: 0; border: 2px solid transparent; transition: all 0.2s;
    }
    .thumb-video.active, .thumb-video:hover { border-color: #f97316; }

    .tag-chip {
        display: inline-flex; padding: 4px 12px; border-radius: 99px;
        font-size: 0.72rem; font-weight: 700;
        background: rgba(249,115,22,0.12); color: #f97316;
        transition: all 0.2s; cursor: default;
    }
    .tag-chip:hover { background: rgba(249,115,22,0.25); }

    .info-row {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 0; border-bottom: 1px solid;
        font-size: 0.8rem;
    }
    .dark .info-row { border-color: #3a3a50; }
    html:not(.dark) .info-row { border-color: #f3f4f6; }

    .related-card {
        border-radius: 14px; overflow: hidden; border: 1px solid;
        transition: all 0.2s; cursor: pointer; display: block;
    }
    .dark .related-card { background: #2a2a3d; border-color: #3a3a50; }
    html:not(.dark) .related-card { background: white; border-color: #e5e7eb; }
    .related-card:hover { transform: translateY(-3px); }

    .lightbox {
        position: fixed; inset: 0; z-index: 100;
        background: rgba(0,0,0,0.95);
        display: flex; align-items: center; justify-content: center;
        padding: 20px;
    }
    .lightbox img { max-width: 100%; max-height: 90vh; object-fit: contain; border-radius: 12px; }

    .thumb-strip { display: flex; gap: 8px; overflow-x: auto; scrollbar-width: none; padding-bottom: 4px; }
    .thumb-strip::-webkit-scrollbar { display: none; }
</style>
@endpush

@section('content')
<div x-data="showPage()" x-init="init()">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs mb-6" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
        <a href="{{ route('portfolio.index') }}" class="hover:text-orange-500 flex items-center gap-1">
            <i data-lucide="image" class="w-3 h-3"></i> Portfolio
        </a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span :class="isDark ? 'text-white' : 'text-[#21212e]'" class="truncate max-w-xs">{{ $portfolio->title }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- KIRI: Media viewer --}}
        <div class="lg:col-span-2">

            {{-- Main media --}}
            <div class="media-main mb-3">
                @foreach($portfolio->media as $mi => $m)
                <div id="main-{{ $mi }}" style="{{ $mi === 0 ? '' : 'display:none;' }}" class="w-full">
                    @if($m->file_type === 'video')
                    <video src="{{ Storage::url($m->file_path) }}"
                        controls class="w-full" style="max-height:600px;"></video>
                    @else
                    <img src="{{ Storage::url($m->file_path) }}"
                        class="w-full cursor-zoom-in"
                        style="max-height:600px; object-fit:contain;"
                        @click="openLightbox('{{ Storage::url($m->file_path) }}')">
                    @endif
                </div>
                @endforeach

                @if($portfolio->media->isEmpty())
                <div class="w-full flex items-center justify-center" style="height:300px;background:linear-gradient(135deg,#f97316,#a855f7);">
                    <i data-lucide="image" class="w-16 h-16 text-white opacity-40"></i>
                </div>
                @endif
            </div>

            {{-- Thumbnail strip --}}
            @if($portfolio->media->count() > 1)
            <div class="thumb-strip mb-4">
                @foreach($portfolio->media as $mi => $m)
                @if($m->file_type === 'video')
                <div class="thumb-video {{ $mi === 0 ? 'active' : '' }}"
                    id="thumb-{{ $mi }}"
                    onclick="switchMedia({{ $mi }}, {{ $portfolio->media->count() }})">
                    <i data-lucide="play-circle" style="width:24px;height:24px;color:#f97316;"></i>
                </div>
                @else
                <img src="{{ Storage::url($m->file_path) }}"
                    class="thumb {{ $mi === 0 ? 'active' : '' }}"
                    id="thumb-{{ $mi }}"
                    onclick="switchMedia({{ $mi }}, {{ $portfolio->media->count() }})">
                @endif
                @endforeach
            </div>
            @endif

            {{-- Deskripsi --}}
            @if($portfolio->description)
            <div class="rounded-2xl p-5 border"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
                <h3 class="font-bold text-sm mb-2" :class="isDark ? 'text-white' : 'text-[#21212e]'">Tentang Karya Ini</h3>
                <p class="text-sm leading-relaxed" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
                    {{ $portfolio->description }}
                </p>
            </div>
            @endif
        </div>

        {{-- KANAN: Info --}}
        <div class="flex flex-col gap-4">

            {{-- Title & Stats --}}
            <div class="rounded-2xl p-5 border"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">

                <h1 class="text-lg font-bold mb-1" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    {{ $portfolio->title }}
                </h1>

                {{-- Views --}}
                <div class="flex items-center gap-1.5 mb-4">
                    <i data-lucide="eye" class="w-3.5 h-3.5 text-orange-500"></i>
                    <span class="text-xs font-bold text-orange-500">{{ number_format($portfolio->views) }} views</span>
                </div>

                {{-- Info rows --}}
                <div class="info-row">
                    <i data-lucide="folder" class="w-4 h-4 text-orange-500 shrink-0"></i>
                    <span :class="isDark ? 'text-gray-400' : 'text-gray-500'">Kategori</span>
                    <span class="ml-auto font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">{{ $portfolio->category }}</span>
                </div>

                @if($portfolio->software)
                <div class="info-row">
                    <i data-lucide="monitor" class="w-4 h-4 text-orange-500 shrink-0"></i>
                    <span :class="isDark ? 'text-gray-400' : 'text-gray-500'">Software</span>
                    <span class="ml-auto font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">{{ $portfolio->software }}</span>
                </div>
                @endif

                @if($portfolio->client_name)
                <div class="info-row">
                    <i data-lucide="user" class="w-4 h-4 text-orange-500 shrink-0"></i>
                    <span :class="isDark ? 'text-gray-400' : 'text-gray-500'">Client</span>
                    <span class="ml-auto font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">{{ $portfolio->client_name }}</span>
                </div>
                @endif

                @if($portfolio->created_date)
                <div class="info-row" style="border-bottom:none;">
                    <i data-lucide="calendar" class="w-4 h-4 text-orange-500 shrink-0"></i>
                    <span :class="isDark ? 'text-gray-400' : 'text-gray-500'">Dibuat</span>
                    <span class="ml-auto font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                        {{ \Carbon\Carbon::parse($portfolio->created_date)->format('d M Y') }}
                    </span>
                </div>
                @endif

                {{-- Tags --}}
                @if($portfolio->tags->isNotEmpty())
                <div class="mt-4 flex flex-wrap gap-1.5">
                    @foreach($portfolio->tags as $tag)
                    <span class="tag-chip">#{{ $tag }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- CTA --}}
            <a href="{{ route('commission.index') }}"
                class="w-full py-3 rounded-xl bg-orange-500 text-white font-bold text-sm text-center hover:bg-orange-600 transition-all flex items-center justify-center gap-2">
                <i data-lucide="brush" class="w-4 h-4"></i>
                Order Commission Serupa
            </a>

            <a href="{{ route('portfolio.index') }}"
                class="w-full py-3 rounded-xl text-sm font-bold text-center transition-all flex items-center justify-center gap-2"
                :class="isDark ? 'bg-[#2a2a3d] text-gray-300 hover:bg-[#3a3a50]' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali ke Portfolio
            </a>
        </div>
    </div>

    {{-- Related --}}
    @if($related->isNotEmpty())
    <div class="mt-10">
        <h2 class="font-bold text-sm mb-4" :class="isDark ? 'text-white' : 'text-[#21212e]'">
            Karya Lain — {{ $portfolio->category }}
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($related as $r)
            <a href="{{ route('portfolio.show', $r->id) }}" class="related-card">
                @if($r->cover)
                    @if($r->cover->file_type === 'video')
                    <div class="w-full h-36 bg-gray-800 flex items-center justify-center">
                        <i data-lucide="play-circle" class="w-8 h-8 text-orange-500"></i>
                    </div>
                    @else
                    <img src="{{ Storage::url($r->cover->file_path) }}"
                        class="w-full h-36 object-cover">
                    @endif
                @else
                <div class="w-full h-36 flex items-center justify-center"
                    style="background:linear-gradient(135deg,#f97316,#a855f7);">
                    <i data-lucide="image" class="w-8 h-8 text-white opacity-50"></i>
                </div>
                @endif
                <div class="p-3">
                    <p class="text-xs font-bold truncate" :class="isDark ? 'text-white' : 'text-[#21212e]'">{{ $r->title }}</p>
                    <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-500' : 'text-gray-400'">{{ $r->category }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Lightbox --}}
    <div x-show="lightbox" class="lightbox" @click="lightbox = false" style="display:none;">
        <button class="absolute top-4 right-4 text-white" @click="lightbox = false">
            <i data-lucide="x" class="w-6 h-6"></i>
        </button>
        <img :src="lightboxSrc" @click.stop>
    </div>

</div>
@endsection

@push('scripts')
<script>
function switchMedia(index, total) {
    for (let i = 0; i < total; i++) {
        const main  = document.getElementById('main-' + i);
        const thumb = document.getElementById('thumb-' + i);
        if (main)  main.style.display  = i === index ? '' : 'none';
        if (thumb) thumb.classList.toggle('active', i === index);
    }
}

function showPage() {
    return {
        isDark: false,
        lightbox: false,
        lightboxSrc: '',
        openLightbox(src) {
            this.lightboxSrc = src;
            this.lightbox    = true;
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