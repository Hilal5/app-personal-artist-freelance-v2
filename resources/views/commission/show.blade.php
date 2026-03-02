@extends('layouts.app')
@section('title', $commission->title . ' — ArtSpace')

@push('styles')
<style>
    .lightbox {
    position: fixed; inset: 0; z-index: 200;
    background: rgba(0,0,0,0.95);
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
    }
    .lightbox img {
        max-width: 100%; max-height: 90vh;
        object-fit: contain; border-radius: 12px;
    }
.lb-nav {
    position: absolute; top: 50%; transform: translateY(-50%);
    width: 44px; height: 44px; border-radius: 99px;
    background: rgba(0,0,0,0.5);
    border: 2px solid rgba(255,255,255,0.3);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: white;
    transition: all 0.2s;
    /* Ini kuncinya — shadow berlapis supaya keliatan di background apapun */
    box-shadow:
        0 0 0 1px rgb(61, 61, 61),
    /* Text shadow pada icon */
    filter: drop-shadow(0 1px 3px rgba(0,0,0,0.8));
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}
    .lb-nav:hover { background: rgba(249,115,22,0.7); }
    .tier-card {
        border-radius: 16px; padding: 20px;
        cursor: pointer; transition: all 0.2s;
        border: 2px solid transparent;
    }
    .dark .tier-card { background: #21212e; }
    html:not(.dark) .tier-card { background: #f9fafb; border-color: #e5e7eb; }
    .tier-card.selected { border-color: #f97316 !important; }
    .dark .tier-card:hover { border-color: #f97316; }
    html:not(.dark) .tier-card:hover { border-color: #f97316; }

    .tier-basic    { color: #22c55e; }
    .tier-standard { color: #f97316; }
    .tier-premium  { color: #a855f7; }

    .media-thumb {
        width: 80px; height: 80px; border-radius: 12px;
        object-fit: cover; cursor: pointer;
        border: 2px solid transparent; transition: all 0.2s;
    }
    .media-thumb:hover, .media-thumb.active { border-color: #f97316; }

    .payment-btn {
        border-radius: 12px; padding: 10px 14px;
        display: flex; align-items: center; gap: 8px;
        cursor: pointer; transition: all 0.2s;
        border: 2px solid transparent;
        font-size: 0.8rem; font-weight: 700;
    }
    .dark .payment-btn { background: #21212e; color: #9ca3af; }
    html:not(.dark) .payment-btn { background: #f9fafb; color: #6b7280; border-color: #e5e7eb; }
    .payment-btn.selected { border-color: #f97316 !important; color: #f97316 !important; }
    .payment-btn:hover { border-color: #f97316; color: #f97316; }
</style>
@endpush

@section('content')
@php
    $isArtist = session('user_role') === 'artist';
    $tiers = [
        'basic'    => ['label' => $commission->tier_basic_name,    'desc' => $commission->tier_basic_desc,    'price' => $commission->tier_basic_price,    'disc' => $commission->tier_basic_discount,    'color' => 'tier-basic',    'icon' => 'star'],
        'standard' => ['label' => $commission->tier_standard_name, 'desc' => $commission->tier_standard_desc, 'price' => $commission->tier_standard_price, 'disc' => $commission->tier_standard_discount, 'color' => 'tier-standard', 'icon' => 'star'],
        'premium'  => ['label' => $commission->tier_premium_name,  'desc' => $commission->tier_premium_desc,  'price' => $commission->tier_premium_price,  'disc' => $commission->tier_premium_discount,  'color' => 'tier-premium',  'icon' => 'crown'],
    ];
    $payments = [
        'bri'       => ['label' => 'BRI',        'icon' => 'building-2',    'color' => '#1e40af'],
        'seabank'   => ['label' => 'SeaBank',     'icon' => 'waves',         'color' => '#0ea5e9'],
        'bank_jago' => ['label' => 'Bank Jago',   'icon' => 'building',      'color' => '#22c55e'],
        'dana'      => ['label' => 'DANA',        'icon' => 'wallet',        'color' => '#3b82f6'],
        'gopay'     => ['label' => 'GoPay',       'icon' => 'smartphone',    'color' => '#22c55e'],
        'shopeepay' => ['label' => 'ShopeePay',   'icon' => 'shopping-bag',  'color' => '#f97316'],
    ];
@endphp

<div x-data="showPage()" x-init="init()">
    {{-- Lightbox --}}
<div x-show="lightbox"
    class="lightbox"
    @click.self="lightbox = false"
    @keydown.escape.window="lightbox = false"
    style="display:none;">

    {{-- Close --}}
    <button @click="lightbox = false"
        class="absolute top-4 right-4 w-10 h-10 rounded-full flex items-center justify-center text-white transition-all"
        style="background:rgba(255,255,255,0.15);">
        <i data-lucide="x" class="w-5 h-5"></i>
    </button>

    {{-- Counter --}}
    <div class="absolute top-4 left-1/2 -translate-x-1/2 text-white text-xs font-bold px-3 py-1 rounded-full"
        style="background:rgba(0,0,0,0.5);">
        <span x-text="lbIndex + 1"></span> / {{ $commission->media->count() }}
    </div>

    {{-- Gambar --}}
    @foreach($commission->media as $i => $media)
    @if($media->file_type === 'image')
    <img x-show="lbIndex === {{ $i }}"
        src="{{ Storage::url($media->file_path) }}"
        @click.stop>
    @endif
    @endforeach

    {{-- Nav prev --}}
    @if($commission->media->count() > 1)
    <button class="lb-nav" style="left: 16px;" @click.stop="lbPrev()" x-show="lbIndex > 0">
        <i data-lucide="chevron-left" class="w-5 h-5"></i>
    </button>

    {{-- Nav next --}}
    <button class="lb-nav" style="right: 16px;" @click.stop="lbNext()" x-show="lbIndex < lbMax - 1">
        <i data-lucide="chevron-right" class="w-5 h-5"></i>
    </button>
    @endif
</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- KIRI: Media & Info --}}
        <div class="lg:col-span-2 flex flex-col gap-4">

            {{-- Media viewer --}}
            <div class="rounded-2xl overflow-hidden border"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">

                {{-- Main media --}}
                <div class="w-full h-72 md:h-96 bg-gray-800 relative overflow-hidden">
                    @if($commission->media->isNotEmpty())
                        @foreach($commission->media as $i => $media)
                        <div x-show="activeMedia === {{ $i }}" class="w-full h-full">
                            @if($media->file_type === 'video')
                            <video src="{{ Storage::url($media->file_path) }}"
                                controls class="w-full h-full object-contain"></video>
                            @else
                            <img src="{{ Storage::url($media->file_path) }}"
                                class="w-full h-full object-contain cursor-zoom-in"
                                @click="openLightbox({{ $i }})">
                            @endif
                        </div>
                        @endforeach

                        {{-- Zoom hint --}}
                        <div class="absolute bottom-3 right-3 flex items-center gap-1 px-2 py-1 rounded-lg text-xs text-white font-bold"
                            style="background:rgba(0,0,0,0.5);">
                            <i data-lucide="zoom-in" style="width:12px;height:12px;"></i> Klik untuk perbesar
                        </div>
                    @else
                    <div class="w-full h-full flex items-center justify-center">
                        <i data-lucide="image" class="w-16 h-16 text-gray-600"></i>
                    </div>
                    @endif
                </div>

                {{-- Thumbnails --}}
                @if($commission->media->count() > 1)
                <div class="p-4 flex gap-2 overflow-x-auto">
                    @foreach($commission->media as $i => $media)
                    @if($media->file_type === 'video')
                    <div @click="activeMedia = {{ $i }}"
                        class="media-thumb flex-shrink-0 flex items-center justify-center bg-gray-800 rounded-xl cursor-pointer"
                        :class="activeMedia === {{ $i }} ? 'active' : ''">
                        <i data-lucide="play-circle" class="w-6 h-6 text-white"></i>
                    </div>
                    @else
                    <img src="{{ Storage::url($media->file_path) }}"
                        @click="activeMedia = {{ $i }}"
                        class="media-thumb flex-shrink-0"
                        :class="activeMedia === {{ $i }} ? 'active' : ''">
                    @endif
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Info commission --}}
            <div class="rounded-2xl p-5 border"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <span class="text-xs px-3 py-1 rounded-full font-bold mb-2 inline-block"
                            style="background:rgba(249,115,22,0.12);color:#f97316;">
                            {{ $commission->category }}
                        </span>
                        <h1 class="text-lg font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                            {{ $commission->title }}
                        </h1>
                    </div>
                    <span class="text-xs px-3 py-1 rounded-full font-bold shrink-0"
                        style="{{ $commission->status === 'open' ? 'background:rgba(34,197,94,0.12);color:#22c55e;' : 'background:rgba(239,68,68,0.12);color:#ef4444;' }}">
                        {{ $commission->status === 'open' ? '● Open' : '● Closed' }}
                    </span>
                </div>

                <p class="text-sm leading-relaxed mb-4" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
                    {!! nl2br(e($commission->description)) !!}
                </p>

                <div class="flex flex-wrap gap-4 text-xs">
                    <div class="flex items-center gap-1.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                        <i data-lucide="clock" class="w-4 h-4 text-orange-500"></i>
                        Estimasi <strong class="ml-1" :class="isDark ? 'text-white' : 'text-[#21212e]'">{{ $commission->estimated_days }} hari</strong>
                    </div>
                    <div class="flex items-center gap-1.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                        <i data-lucide="users" class="w-4 h-4 text-orange-500"></i>
                        Slot <strong class="ml-1" :class="isDark ? 'text-white' : 'text-[#21212e]'">{{ $commission->used_slots }} / {{ $commission->max_slots }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- KANAN: Order panel --}}
        <div class="flex flex-col gap-4">

            {{-- Pilih Tier --}}
            <div class="rounded-2xl p-5 border"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
                <h3 class="font-bold text-sm mb-4" :class="isDark ? 'text-white' : 'text-[#21212e]'">Pilih Paket</h3>

                <div class="flex flex-col gap-3">
                    @foreach($tiers as $key => $tier)
                    @php
                        $final = $tier['price'] - ($tier['price'] * $tier['disc'] / 100);
                    @endphp
                    <div class="tier-card" :class="selectedTier === '{{ $key }}' ? 'selected' : ''"
                        @click="selectedTier = '{{ $key }}'">
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <i data-lucide="{{ $tier['icon'] }}" class="w-4 h-4 {{ $tier['color'] }}"></i>
                                <span class="font-bold text-sm {{ $tier['color'] }}">{{ $tier['label'] }}</span>
                            </div>
                            <div class="text-right">
                                @if($tier['disc'] > 0)
                                <p class="text-xs line-through" :class="isDark ? 'text-gray-600' : 'text-gray-400'">
                                    Rp{{ number_format($tier['price'], 0, ',', '.') }}
                                </p>
                                <p class="text-sm font-bold text-orange-500">Rp{{ number_format($final, 0, ',', '.') }}</p>
                                <span class="text-xs bg-red-500 text-white px-1.5 py-0.5 rounded-full">-{{ $tier['disc'] }}%</span>
                                @else
                                <p class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                                    Rp{{ number_format($tier['price'], 0, ',', '.') }}
                                </p>
                                @endif
                            </div>
                        </div>
                        @if($tier['desc'])
                        <p class="text-xs mt-1" :class="isDark ? 'text-gray-500' : 'text-gray-400'">{{ $tier['desc'] }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Pilih Pembayaran --}}
            <div class="rounded-2xl p-5 border"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
                <h3 class="font-bold text-sm mb-4" :class="isDark ? 'text-white' : 'text-[#21212e]'">Metode Pembayaran</h3>

                <div class="grid grid-cols-2 gap-2">
                    @foreach($payments as $key => $pay)
                    <div class="payment-btn" :class="selectedPayment === '{{ $key }}' ? 'selected' : ''"
                        @click="selectedPayment = '{{ $key }}'">
                        <i data-lucide="{{ $pay['icon'] }}" class="w-4 h-4 shrink-0"></i>
                        {{ $pay['label'] }}
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Tombol Order --}}
            @if($commission->status === 'open' && !$isArtist)
            <button @click="goOrder({{ $commission->id }})"
                :disabled="!selectedTier || !selectedPayment"
                class="w-full py-3 rounded-xl font-bold text-sm transition-all"
                :class="selectedTier && selectedPayment
                    ? 'bg-orange-500 text-white hover:bg-orange-600'
                    : 'bg-gray-400 text-white cursor-not-allowed opacity-60'">
                Order Sekarang
            </button>

            <p class="text-xs text-center" :class="isDark ? 'text-gray-500' : 'text-gray-400'">
                <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
                Pembayaran dilakukan setelah deal dengan artist via chat
            </p>
            @elseif($isArtist)
            <div class="p-3 rounded-xl text-xs text-center font-bold"
                style="background:rgba(249,115,22,0.1);color:#f97316;">
                Kamu adalah artist — tidak bisa order
            </div>
            @else
            <div class="p-3 rounded-xl text-xs text-center font-bold"
                style="background:rgba(239,68,68,0.1);color:#ef4444;">
                Commission sedang tutup
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showPage() {
    return {
        isDark: false,
        activeMedia: 0,
        selectedTier: '',
        selectedPayment: '',
        lightbox: false,
        lbIndex: 0,
        lbMax: {{ $commission->media->count() }},

        openLightbox(index) {
            this.lbIndex = index;
            this.lightbox = true;
            // sync thumbnail
            this.activeMedia = index;
        },
        lbPrev() {
            if (this.lbIndex > 0) {
                this.lbIndex--;
                this.activeMedia = this.lbIndex;
            }
        },
        lbNext() {
            if (this.lbIndex < this.lbMax - 1) {
                this.lbIndex++;
                this.activeMedia = this.lbIndex;
            }
        },
        init() {
            this.isDark = document.documentElement.classList.contains('dark');
            new MutationObserver(() => {
                this.isDark = document.documentElement.classList.contains('dark');
            }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            lucide.createIcons();
        },
        goOrder(commissionId) {
            if (!this.selectedTier || !this.selectedPayment) return;
            @if(!session('user_id'))
                alert('Login dulu untuk melakukan order!');
                return;
            @endif
            window.location.href = `/commission/${commissionId}/order?tier=${this.selectedTier}&payment=${this.selectedPayment}`;
        }
    }
}
</script>
@endpush