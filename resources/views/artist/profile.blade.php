@extends('layouts.app')
@section('title', 'Profile — ArtSpace')

@push('styles')
<style>
    .profile-avatar {
        width: 100px; height: 100px; border-radius: 99px;
        object-fit: cover; border: 3px solid #f97316;
    }
    .avatar-placeholder {
        width: 100px; height: 100px; border-radius: 99px;
        display: flex; align-items: center; justify-content: center;
        font-size: 2.5rem; font-weight: 800; color: white;
        border: 3px solid #f97316;
        background: linear-gradient(135deg, #f97316, #a855f7);
    }

    .stat-card {
        border-radius: 14px; padding: 14px 16px;
        text-align: center; border: 1px solid;
    }
    .dark .stat-card { background: #21212e; border-color: #3a3a50; }
    html:not(.dark) .stat-card { background: #f9fafb; border-color: #e5e7eb; }

    .social-btn {
        display: flex; align-items: center; gap: 8px;
        padding: 8px 14px; border-radius: 12px;
        font-size: 0.75rem; font-weight: 700;
        transition: all 0.2s; text-decoration: none;
        border: 1px solid;
    }
    .dark .social-btn { border-color: #3a3a50; color: #9ca3af; }
    html:not(.dark) .social-btn { border-color: #e5e7eb; color: #6b7280; }
    .social-btn:hover { border-color: #f97316; color: #f97316; }

    .section-title {
        font-size: 0.875rem; font-weight: 800;
        margin-bottom: 12px; display: flex; align-items: center; gap: 8px;
    }

    .portfolio-thumb {
        border-radius: 12px; overflow: hidden;
        aspect-ratio: 1; transition: all 0.2s;
        display: block; position: relative;
    }
    .portfolio-thumb:hover { transform: scale(1.03); }
    .portfolio-thumb img { width: 100%; height: 100%; object-fit: cover; }

    .review-mini {
        border-radius: 14px; padding: 14px; border: 1px solid;
    }
    .dark .review-mini { background: #21212e; border-color: #3a3a50; }
    html:not(.dark) .review-mini { background: #f9fafb; border-color: #e5e7eb; }

    .commission-mini {
        border-radius: 14px; padding: 14px; border: 1px solid;
        transition: all 0.2s; display: block; text-decoration: none;
    }
    .dark .commission-mini { background: #21212e; border-color: #3a3a50; }
    html:not(.dark) .commission-mini { background: #f9fafb; border-color: #e5e7eb; }
    .commission-mini:hover { border-color: #f97316; }

    .form-input {
        width: 100%; padding: 10px 14px; border-radius: 12px;
        font-size: 0.8rem; outline: none; border: 1px solid; transition: all 0.2s;
    }
    .dark .form-input { background: #21212e; border-color: #3a3a50; color: white; }
    html:not(.dark) .form-input { background: #f9fafb; border-color: #e5e7eb; color: #21212e; }
    .dark .form-input:focus { border-color: #f97316; }
    html:not(.dark) .form-input:focus { border-color: #21212e; }
    .form-label { font-size: 0.7rem; font-weight: 700; margin-bottom: 5px; display: block; }

    .tag-chip { display: inline-flex; padding: 2px 8px; border-radius: 99px; font-size: 0.65rem; font-weight: 700; background: rgba(34,197,94,0.12); color: #22c55e; }
    .tag-bad   { background: rgba(239,68,68,0.12); color: #ef4444; }
</style>
@endpush

@section('content')
@php
    $badTags = ['Komunikasi kurang','Pengerjaan lambat','Kurang sesuai request','Revisi tidak ditangani','Kurang responsif','Tidak sesuai ekspektasi','Harga terlalu mahal'];
    $isArtist = session('user_role') === 'artist';
@endphp

<div x-data="profilePage()" x-init="init()">

    @if(session('success'))
    <div class="mb-4 p-3 rounded-xl text-sm font-bold text-green-400 border border-green-500/30 bg-green-500/10">
        ✅ {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ===== KIRI: Info Artist ===== --}}
        <div class="flex flex-col gap-4">

            {{-- Profile Card --}}
            <div class="rounded-2xl p-6 border text-center"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">

                {{-- Avatar --}}
                <div class="flex justify-center mb-4 relative">
                    @if($artist->avatar)
                    <img src="{{ Storage::url($artist->avatar) }}"
                        class="profile-avatar cursor-pointer hover:opacity-90 transition-opacity"
                        id="avatarImg"
                        onclick="window.open('{{ Storage::url($artist->avatar) }}', '_blank')"
                        title="Klik untuk lihat foto">
                    @else
                    <div class="avatar-placeholder" id="avatarPlaceholder">
                        {{ strtoupper(substr($artist->name, 0, 1)) }}
                    </div>
                    @endif

                    {{-- Edit avatar btn (artist only) --}}
                    @if($isArtist)
                    <button onclick="document.getElementById('avatarInput').click()"
                        class="absolute bottom-0 right-1/2 translate-x-8 w-7 h-7 rounded-full bg-orange-500 text-white flex items-center justify-center hover:bg-orange-600 transition-all">
                        <i data-lucide="camera" style="width:13px;height:13px;"></i>
                    </button>
                    @endif
                </div>

                {{-- Nama & username --}}
                <h1 class="text-lg font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    {{ $artist->name }}
                </h1>
{{-- JADI --}}
@if($artist->username)
<p class="text-xs text-orange-500 font-bold">&#64;{{ $artist->username }}</p>
@endif

                {{-- Commission status --}}
                <div class="mt-2 mb-3">
                    <span class="text-xs px-3 py-1 rounded-full font-bold"
                        style="{{ $artist->commission_status === 'open'
                            ? 'background:rgba(34,197,94,0.12);color:#22c55e;'
                            : 'background:rgba(239,68,68,0.12);color:#ef4444;' }}">
                        ● Commission {{ $artist->commission_status === 'open' ? 'Open' : 'Closed' }}
                    </span>
                </div>

                {{-- Bio --}}
                @if($artist->bio)
                <p class="text-xs leading-relaxed mb-4" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
                    {{ $artist->bio }}
                </p>
                @else
                <p class="text-xs mb-4 italic" :class="isDark ? 'text-gray-600' : 'text-gray-400'">
                    Belum ada bio
                </p>
                @endif

                {{-- Lokasi & bahasa --}}
                <div class="flex flex-col gap-1 mb-4">
                    @if($artist->location)
                    <p class="text-xs flex items-center justify-center gap-1.5"
                        :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-orange-500"></i>
                        {{ $artist->location }}
                    </p>
                    @endif
                    @if($artist->language)
                    <p class="text-xs flex items-center justify-center gap-1.5"
                        :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                        <i data-lucide="globe" class="w-3.5 h-3.5 text-orange-500"></i>
                        {{ $artist->language }}
                    </p>
                    @endif
                </div>

                {{-- Sosial media --}}
                <div class="flex flex-wrap justify-center gap-2 mb-5">
                    @if($artist->instagram)
                    <a href="https://instagram.com/{{ ltrim($artist->instagram, '@') }}" target="_blank" class="social-btn">
                        <i data-lucide="instagram" class="w-3.5 h-3.5"></i> IG
                    </a>
                    @endif
                    @if($artist->twitter)
                    <a href="https://twitter.com/{{ ltrim($artist->twitter, '@') }}" target="_blank" class="social-btn">
                        <i data-lucide="twitter" class="w-3.5 h-3.5"></i> X
                    </a>
                    @endif
                    @if($artist->tiktok)
                    <a href="https://tiktok.com/@{{ ltrim($artist->tiktok, '@') }}" target="_blank" class="social-btn">
                        <i data-lucide="video" class="w-3.5 h-3.5"></i> TikTok
                    </a>
                    @endif
                    @if($artist->website)
                    <a href="{{ $artist->website }}" target="_blank" class="social-btn">
                        <i data-lucide="link" class="w-3.5 h-3.5"></i> Web
                    </a>
                    @endif
                </div>

                {{-- CTA buttons --}}
                <div class="flex flex-col gap-2">
                    <a href="{{ route('commission.index') }}"
                        class="w-full py-2.5 rounded-xl bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="brush" class="w-4 h-4"></i> Lihat Commission
                    </a>
                    @if(!$isArtist && session('user_id'))
                    <a href="{{ route('chat.index') }}"
                        class="w-full py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2"
                        :class="isDark ? 'bg-[#21212e] text-gray-300 hover:text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                        <i data-lucide="message-circle" class="w-4 h-4"></i> Chat Artist
                    </a>
                    @endif

                    {{-- Edit profil (artist only) --}}
                    @if($isArtist)
                    <button @click="editModal = true"
                        class="w-full py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2"
                        :class="isDark ? 'bg-[#21212e] text-orange-400 hover:bg-orange-500/10' : 'bg-orange-50 text-orange-500 hover:bg-orange-100'">
                        <i data-lucide="pencil" class="w-4 h-4"></i> Edit Profil
                    </button>
                    @endif
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="stat-card">
                    <p class="text-xl font-bold text-orange-500">{{ $stats['portfolio'] }}</p>
                    <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Karya</p>
                </div>
                <div class="stat-card">
                    <p class="text-xl font-bold text-orange-500">{{ $stats['orders'] }}</p>
                    <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Order Selesai</p>
                </div>
                <div class="stat-card">
                    <p class="text-xl font-bold text-orange-500">{{ number_format($stats['rating'], 1) }}</p>
                    <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Rating</p>
                </div>
                <div class="stat-card">
                    <p class="text-xl font-bold text-orange-500">{{ $stats['reviews'] }}</p>
                    <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Ulasan</p>
                </div>
                <div class="stat-card col-span-2">
                    <p class="text-xl font-bold text-orange-500">{{ number_format($stats['views']) }}</p>
                    <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Total Views Portfolio</p>
                </div>
            </div>
        </div>

        {{-- ===== KANAN: Konten ===== --}}
        <div class="lg:col-span-2 flex flex-col gap-6">

            {{-- Portfolio terbaru --}}
            <div class="rounded-2xl p-5 border"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
                <div class="section-title" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="image" class="w-4 h-4 text-orange-500"></i>
                    Karya Terbaru
                    <a href="{{ route('portfolio.index') }}" class="ml-auto text-xs text-orange-500 font-bold hover:underline">
                        Lihat semua →
                    </a>
                </div>

                @if($portfolios->isEmpty())
                <p class="text-xs text-center py-6" :class="isDark ? 'text-gray-500' : 'text-gray-400'">
                    Belum ada karya
                </p>
                @else
                <div class="grid grid-cols-3 gap-2">
                    @foreach($portfolios as $p)
                    <a href="{{ route('portfolio.show', $p->id) }}" class="portfolio-thumb">
                        @if($p->cover)
                        @if($p->cover->file_type === 'video')
                        <div class="w-full h-full relative" style="min-height:100px;">
                            <video 
                                src="{{ Storage::url($p->cover->file_path) }}"
                                class="w-full h-full object-cover"
                                preload="metadata"
                                muted
                                playsinline
                                style="pointer-events:none;"
                                onloadedmetadata="this.currentTime=1">
                            </video>
                            {{-- Play overlay --}}
                            <div class="absolute inset-0 flex items-center justify-center bg-black/30">
                                <i data-lucide="play-circle" class="w-8 h-8 text-white"></i>
                            </div>
                        </div>
                            @else
                            <img src="{{ Storage::url($p->cover->file_path) }}" alt="{{ $p->title }}" loading="lazy">
                            @endif
                        @else
                        <div class="w-full h-full flex items-center justify-center" style="min-height:100px;background:linear-gradient(135deg,#f97316,#a855f7);">
                            <i data-lucide="image" class="w-6 h-6 text-white opacity-50"></i>
                        </div>
                        @endif

                        {{-- Hover overlay --}}
                        <div class="absolute inset-0 bg-black/60 opacity-0 hover:opacity-100 transition-opacity flex items-end p-2">
                            <p class="text-white text-xs font-bold truncate">{{ $p->title }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Commission terbuka --}}
            @if($commissions->isNotEmpty())
            <div class="rounded-2xl p-5 border"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
                <div class="section-title" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="brush" class="w-4 h-4 text-orange-500"></i>
                    Commission Tersedia
                    <a href="{{ route('commission.index') }}" class="ml-auto text-xs text-orange-500 font-bold hover:underline">
                        Lihat semua →
                    </a>
                </div>
                <div class="flex flex-col gap-2">
                    @foreach($commissions as $c)
                    @php
                        $minPrice = min($c->tier_basic_price, $c->tier_standard_price, $c->tier_premium_price);
                    @endphp
                    <a href="{{ route('commission.show', $c->id) }}" class="commission-mini">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-bold text-sm" :class="isDark ? 'text-white' : 'text-[#21212e]'">{{ $c->title }}</p>
                                <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                                    {{ $c->category }} · {{ $c->estimated_days }} hari
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-bold text-orange-500">
                                    Mulai Rp{{ number_format($minPrice, 0, ',', '.') }}
                                </p>
                                <p class="text-xs" :class="isDark ? 'text-gray-500' : 'text-gray-400'">
                                    {{ $c->used_slots }}/{{ $c->max_slots }} slot
                                </p>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Review terbaru --}}
            <div class="rounded-2xl p-5 border"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
                <div class="section-title" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="star" class="w-4 h-4 text-orange-500"></i>
                    Review Terbaru
                    <a href="{{ route('reviews.index') }}" class="ml-auto text-xs text-orange-500 font-bold hover:underline">
                        Lihat semua →
                    </a>
                </div>

                @if($reviews->isEmpty())
                <p class="text-xs text-center py-6" :class="isDark ? 'text-gray-500' : 'text-gray-400'">
                    Belum ada review
                </p>
                @else
                <div class="flex flex-col gap-3">
                    @foreach($reviews as $r)
                    <div class="review-mini">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($r->client_name) }}&background=7c3aed&color=fff"
                                    class="w-7 h-7 rounded-full shrink-0">
                                <div>
                                    <p class="text-xs font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                                        {{ $r->client_name }}
                                    </p>
                                    <p class="text-xs" :class="isDark ? 'text-gray-500' : 'text-gray-400'">
                                        {{ \Carbon\Carbon::parse($r->created_at)->format('d M Y') }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-0.5 shrink-0">
                                @for($i = 1; $i <= 5; $i++)
                                <i data-lucide="star" class="w-3 h-3"
                                    style="{{ $i <= $r->rating ? 'color:#f97316;fill:#f97316;' : 'color:#4b5563;' }}"></i>
                                @endfor
                            </div>
                        </div>

                        @if($r->comment)
                        <p class="text-xs leading-relaxed mb-2" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
                            "{{ Str::limit($r->comment, 120) }}"
                        </p>
                        @endif

                        @if(!empty($r->quick_tags))
                        <div class="flex flex-wrap gap-1">
                            @foreach(array_slice($r->quick_tags, 0, 3) as $tag)
                            @php $isBad = in_array($tag, $badTags); @endphp
                            <span class="tag-chip {{ $isBad ? 'tag-bad' : '' }}">
                                {{ $isBad ? '👎' : '👍' }} {{ $tag }}
                            </span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== MODAL EDIT PROFIL (artist only) ===== --}}
    @if($isArtist)
    <div x-show="editModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-8 overflow-y-auto" style="display:none;">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="editModal = false"></div>

        <div class="relative w-full max-w-2xl rounded-2xl z-10 my-auto"
            :class="isDark ? 'bg-[#2a2a3d] border border-[#3a3a50]' : 'bg-white border border-gray-200'">

            {{-- Modal header --}}
            <div class="flex items-center justify-between p-5 border-b"
                :class="isDark ? 'border-[#3a3a50]' : 'border-gray-100'">
                <h2 class="font-bold text-sm" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                    <i data-lucide="pencil" class="w-4 h-4 text-orange-500 inline mr-1"></i>
                    Edit Profil
                </h2>
                <button @click="editModal = false"
                    class="w-7 h-7 rounded-lg flex items-center justify-center transition-all"
                    :class="isDark ? 'hover:bg-[#3a3a50] text-gray-400' : 'hover:bg-gray-100 text-gray-400'">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('artist.update') }}"
                enctype="multipart/form-data" class="p-5 overflow-y-auto" style="max-height: 75vh;">
                @csrf

                {{-- Avatar upload (hidden, trigger dari tombol di foto profil) --}}
                <input type="file" name="avatar" id="avatarInput" accept="image/*"
                    class="hidden" @change="previewAvatar($event)">

                {{-- Avatar preview di modal --}}
                <div class="flex justify-center mb-5">
                    <div class="relative">
                        <img x-show="avatarPreview" :src="avatarPreview"
                            class="w-20 h-20 rounded-full object-cover border-2 border-orange-500">
                        @if($artist->avatar)
                        <img x-show="!avatarPreview" src="{{ Storage::url($artist->avatar) }}"
                            class="w-20 h-20 rounded-full object-cover border-2 border-orange-500">
                        @else
                        <div x-show="!avatarPreview"
                            class="w-20 h-20 rounded-full flex items-center justify-center text-2xl font-bold text-white border-2 border-orange-500"
                            style="background:linear-gradient(135deg,#f97316,#a855f7);">
                            {{ strtoupper(substr($artist->name, 0, 1)) }}
                        </div>
                        @endif
                        <button type="button"
                            onclick="document.getElementById('avatarInput').click()"
                            class="absolute bottom-0 right-0 w-6 h-6 rounded-full bg-orange-500 text-white flex items-center justify-center">
                            <i data-lucide="camera" style="width:11px;height:11px;"></i>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Nama --}}
                    <div>
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Nama *</label>
                        <input type="text" name="name" value="{{ old('name', $artist->name) }}" required class="form-input">
                    </div>

                    {{-- Username --}}
                    <div>
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Username</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-orange-500 font-bold text-sm">@</span>
                            <input type="text" name="username"
                                value="{{ old('username', $artist->username) }}"
                                class="form-input" style="padding-left: 28px;"
                                placeholder="yourusername">
                        </div>
                    </div>

                    {{-- Bio --}}
                    <div class="md:col-span-2">
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Bio</label>
                        <textarea name="bio" rows="3" maxlength="500"
                            placeholder="Ceritakan tentang dirimu sebagai artist..."
                            class="form-input resize-none"
                            :class="isDark ? 'placeholder-gray-600' : 'placeholder-gray-400'">{{ old('bio', $artist->bio) }}</textarea>
                    </div>

                    {{-- Lokasi --}}
                    <div>
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Lokasi</label>
                        <input type="text" name="location" value="{{ old('location', $artist->location) }}"
                            placeholder="Jakarta, Indonesia" class="form-input"
                            :class="isDark ? 'placeholder-gray-600' : 'placeholder-gray-400'">
                    </div>

                    {{-- Bahasa --}}
                    <div>
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Bahasa</label>
                        <select name="language" class="form-input">
                            <option value="">Pilih bahasa</option>
                            @foreach(['Indonesia', 'English', 'Indonesia & English', 'Lainnya'] as $lang)
                            <option value="{{ $lang }}" {{ old('language', $artist->language) === $lang ? 'selected' : '' }}>
                                {{ $lang }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Commission Status --}}
                    <div class="md:col-span-2">
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Status Commission</label>
                        <div class="flex gap-3">
                            <label class="flex-1 flex items-center gap-2 p-3 rounded-xl cursor-pointer border-2 transition-all"
                                :class="commStatus === 'open'
                                    ? 'border-green-500 bg-green-500/10'
                                    : (isDark ? 'border-[#3a3a50]' : 'border-gray-200')">
                                <input type="radio" name="commission_status" value="open"
                                    {{ old('commission_status', $artist->commission_status) === 'open' ? 'checked' : '' }}
                                    x-model="commStatus" class="hidden">
                                <div class="w-3 h-3 rounded-full"
                                    :class="commStatus === 'open' ? 'bg-green-500' : 'bg-gray-400'"></div>
                                <span class="text-xs font-bold"
                                    :class="commStatus === 'open' ? 'text-green-500' : (isDark ? 'text-gray-400' : 'text-gray-500')">
                                    Open
                                </span>
                            </label>
                            <label class="flex-1 flex items-center gap-2 p-3 rounded-xl cursor-pointer border-2 transition-all"
                                :class="commStatus === 'closed'
                                    ? 'border-red-500 bg-red-500/10'
                                    : (isDark ? 'border-[#3a3a50]' : 'border-gray-200')">
                                <input type="radio" name="commission_status" value="closed"
                                    {{ old('commission_status', $artist->commission_status) === 'closed' ? 'checked' : '' }}
                                    x-model="commStatus" class="hidden">
                                <div class="w-3 h-3 rounded-full"
                                    :class="commStatus === 'closed' ? 'bg-red-500' : 'bg-gray-400'"></div>
                                <span class="text-xs font-bold"
                                    :class="commStatus === 'closed' ? 'text-red-500' : (isDark ? 'text-gray-400' : 'text-gray-500')">
                                    Closed
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Sosial media --}}
                    <div class="md:col-span-2">
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Sosial Media</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="flex items-center gap-2 rounded-xl border px-3"
                                :class="isDark ? 'bg-[#21212e] border-[#3a3a50]' : 'bg-gray-50 border-gray-200'">
                                <i data-lucide="instagram" class="w-4 h-4 text-pink-500 shrink-0"></i>
                                <input type="text" name="instagram"
                                    value="{{ old('instagram', $artist->instagram) }}"
                                    placeholder="@username" class="bg-transparent text-xs outline-none py-2.5 w-full"
                                    :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#21212e] placeholder-gray-400'">
                            </div>
                            <div class="flex items-center gap-2 rounded-xl border px-3"
                                :class="isDark ? 'bg-[#21212e] border-[#3a3a50]' : 'bg-gray-50 border-gray-200'">
                                <i data-lucide="twitter" class="w-4 h-4 text-sky-500 shrink-0"></i>
                                <input type="text" name="twitter"
                                    value="{{ old('twitter', $artist->twitter) }}"
                                    placeholder="@username" class="bg-transparent text-xs outline-none py-2.5 w-full"
                                    :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#21212e] placeholder-gray-400'">
                            </div>
                            <div class="flex items-center gap-2 rounded-xl border px-3"
                                :class="isDark ? 'bg-[#21212e] border-[#3a3a50]' : 'bg-gray-50 border-gray-200'">
                                <i data-lucide="video" class="w-4 h-4 text-black dark:text-white shrink-0"></i>
                                <input type="text" name="tiktok"
                                    value="{{ old('tiktok', $artist->tiktok) }}"
                                    placeholder="@username" class="bg-transparent text-xs outline-none py-2.5 w-full"
                                    :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#21212e] placeholder-gray-400'">
                            </div>
                            <div class="flex items-center gap-2 rounded-xl border px-3"
                                :class="isDark ? 'bg-[#21212e] border-[#3a3a50]' : 'bg-gray-50 border-gray-200'">
                                <i data-lucide="link" class="w-4 h-4 text-orange-500 shrink-0"></i>
                                <input type="text" name="website"
                                    value="{{ old('website', $artist->website) }}"
                                    placeholder="https://yourwebsite.com" class="bg-transparent text-xs outline-none py-2.5 w-full"
                                    :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#21212e] placeholder-gray-400'">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Errors --}}
                @if($errors->any())
                <div class="mt-4 p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs">
                    @foreach($errors->all() as $err)<p>• {{ $err }}</p>@endforeach
                </div>
                @endif

                {{-- Footer --}}
                <div class="flex gap-3 mt-5 pt-4 border-t"
                    :class="isDark ? 'border-[#3a3a50]' : 'border-gray-100'">
                    <button type="button" @click="editModal = false"
                        class="flex-1 py-2.5 rounded-xl text-sm font-bold transition-all"
                        :class="isDark ? 'bg-[#21212e] text-gray-300 hover:bg-[#3a3a50]' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 rounded-xl bg-orange-500 text-white font-bold text-sm hover:bg-orange-600 transition-all">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function profilePage() {
    return {
        isDark: false,
        editModal: {{ $errors->any() && session('user_role') === 'artist' ? 'true' : 'false' }},
        avatarPreview: null,
        commStatus: '{{ old('commission_status', $artist->commission_status ?? 'open') }}',

        previewAvatar(e) {
            const file = e.target.files[0];
            if (!file) return;
            const r = new FileReader();
            r.onload = ev => this.avatarPreview = ev.target.result;
            r.readAsDataURL(file);

            // Sync form input ke form dalam modal
            const modalInput = document.querySelector('form input[name=avatar]');
            if (modalInput) {
                const dt = new DataTransfer();
                dt.items.add(file);
                modalInput.files = dt.files;
            }
        },
        init() {
            this.isDark = document.documentElement.classList.contains('dark');
            new MutationObserver(() => {
                this.isDark = document.documentElement.classList.contains('dark');
            }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            lucide.createIcons();

            // Auto buka modal jika ada error validasi
            if (this.editModal) {
                this.$nextTick(() => lucide.createIcons());
            }
        }
    }
}
</script>
@endpush