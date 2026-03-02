@extends('layouts.app')
@section('title', 'Commission — ArtSpace')

@push('styles')
<style>
    .commission-card {
        border-radius: 18px;
        transition: all 0.2s;
        overflow: hidden;
    }
    .commission-card:hover {
        transform: translateY(-3px);
    }
    .dark .commission-card { background: #2a2a3d; border: 1px solid #3a3a50; }
    html:not(.dark) .commission-card { background: white; border: 1px solid #e5e7eb; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }

    .tier-badge {
        display: inline-flex; align-items: center;
        padding: 3px 10px; border-radius: 99px;
        font-size: 0.7rem; font-weight: 700;
    }
    .tier-basic    { background: rgba(34,197,94,0.12);  color: #22c55e; }
    .tier-standard { background: rgba(249,115,22,0.12); color: #f97316; }
    .tier-premium  { background: rgba(168,85,247,0.12); color: #a855f7; }

    .status-open   { background: rgba(34,197,94,0.12);  color: #22c55e; }
    .status-closed { background: rgba(239,68,68,0.12);  color: #ef4444; }

    .slot-bar { height: 4px; border-radius: 99px; background: #3a3a50; overflow: hidden; }
    .slot-fill { height: 100%; border-radius: 99px; background: #f97316; transition: width 0.5s; }

    .filter-btn {
        padding: 6px 16px; border-radius: 99px;
        font-size: 0.75rem; font-weight: 700;
        cursor: pointer; transition: all 0.2s; border: none;
    }
    .dark .filter-btn { background: #2a2a3d; color: #9ca3af; }
    html:not(.dark) .filter-btn { background: #f3f4f6; color: #6b7280; }
    .filter-btn.active,
    .filter-btn:hover { background: #f97316 !important; color: white !important; }
</style>
@endpush

@section('content')
<div x-data="commissionPage()" x-init="init()">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Commission</h1>
            <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Pilih paket commission yang sesuai kebutuhanmu</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            {{-- Search --}}
            <div class="flex items-center gap-2 px-3 py-2 rounded-xl"
                :class="isDark ? 'bg-[#2a2a3d]' : 'bg-gray-100'">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 shrink-0"></i>
                <input type="text" placeholder="Cari commission..." x-model="search"
                    class="bg-transparent text-xs outline-none w-36"
                    :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#21212e] placeholder-gray-400'">
            </div>

            {{-- Artist: tombol create --}}
            @if(session('user_role') === 'artist')
            <a href="{{ route('commission.create') }}"
                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i> Buat Commission
            </a>
            <a href="{{ route('commission.manage') }}"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all"
                :class="isDark ? 'bg-[#2a2a3d] text-white hover:bg-[#3a3a50]' : 'bg-gray-100 text-[#21212e] hover:bg-gray-200'">
                <i data-lucide="settings" class="w-4 h-4"></i> Manage
            </a>
            @endif
        </div>
    </div>

    {{-- Filter kategori --}}
    <div class="flex gap-2 flex-wrap mb-6">
        <button class="filter-btn" :class="activeCategory === '' ? 'active' : ''" @click="activeCategory = ''">Semua</button>
        @php
            $categories = $commissions->pluck('category')->unique()->values();
        @endphp
        @foreach($categories as $cat)
        <button class="filter-btn" :class="activeCategory === '{{ $cat }}' ? 'active' : ''" @click="activeCategory = '{{ $cat }}'">{{ $cat }}</button>
        @endforeach
    </div>

    {{-- Alert success --}}
    @if(session('success'))
    <div class="mb-4 p-3 rounded-xl text-sm font-bold text-green-400 border border-green-500/30 bg-green-500/10">
        ✅ {{ session('success') }}
    </div>
    @endif

    {{-- Grid commission --}}
    @if($commissions->isEmpty())
    <div class="flex flex-col items-center justify-center py-24">
        <div class="w-16 h-16 rounded-2xl bg-orange-500/10 flex items-center justify-center mb-4">
            <i data-lucide="brush" class="w-8 h-8 text-orange-500"></i>
        </div>
        <p class="font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Belum ada commission</p>
        <p class="text-xs mt-1" :class="isDark ? 'text-gray-500' : 'text-gray-400'">Artist belum membuka commission</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($commissions as $c)
        @php
            $slotPercent = $c->max_slots > 0 ? ($c->used_slots / $c->max_slots) * 100 : 0;
            $minPrice = min($c->tier_basic_price, $c->tier_standard_price, $c->tier_premium_price);
        @endphp
        <div class="commission-card"
            x-show="(activeCategory === '' || activeCategory === '{{ $c->category }}')
                && (search === '' || '{{ strtolower($c->title) }}'.includes(search.toLowerCase()))">

            {{-- Media preview --}}
            <div class="w-full h-44 overflow-hidden relative bg-gray-800">
                @if($c->media->isNotEmpty())
                    @if($c->media->first()->file_type === 'video')
                    <video src="{{ Storage::url($c->media->first()->file_path) }}"
                        class="w-full h-full object-cover" muted autoplay loop playsinline></video>
                    @else
                    <img src="{{ Storage::url($c->media->first()->file_path) }}"
                        class="w-full h-full object-cover">
                    @endif
                @else
                <div class="w-full h-full flex items-center justify-center">
                    <i data-lucide="image" class="w-10 h-10 text-gray-600"></i>
                </div>
                @endif

                {{-- Status badge --}}
                <div class="absolute top-3 left-3">
                    <span class="tier-badge {{ $c->status === 'open' ? 'status-open' : 'status-closed' }}">
                        {{ $c->status === 'open' ? '● Open' : '● Closed' }}
                    </span>
                </div>

                {{-- Category badge --}}
                <div class="absolute top-3 right-3">
                    <span class="tier-badge" style="background:rgba(0,0,0,0.5);color:white;">
                        {{ $c->category }}
                    </span>
                </div>
            </div>

            {{-- Content --}}
            <div class="p-4">
                <h3 class="font-bold text-sm mb-1" :class="isDark ? 'text-white' : 'text-[#21212e]'">{{ $c->title }}</h3>
                <p class="text-xs mb-3 line-clamp-2" :class="isDark ? 'text-gray-400' : 'text-gray-500'">{{ $c->description }}</p>

                {{-- Slot --}}
                <div class="mb-3">
                    <div class="flex justify-between text-xs mb-1">
                        <span :class="isDark ? 'text-gray-400' : 'text-gray-500'">Slot</span>
                        <span class="font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                            {{ $c->used_slots }} / {{ $c->max_slots }}
                        </span>
                    </div>
                    <div class="slot-bar">
                        <div class="slot-fill" style="width: {{ $slotPercent }}%"></div>
                    </div>
                </div>

                {{-- Tier prices --}}
                <div class="flex gap-2 mb-4">
                    @foreach(['basic' => 'tier-basic', 'standard' => 'tier-standard', 'premium' => 'tier-premium'] as $tier => $cls)
                    @php
                        $price    = $c->{'tier_' . $tier . '_price'};
                        $discount = $c->{'tier_' . $tier . '_discount'};
                        $final    = $price - ($price * $discount / 100);
                    @endphp
                    <div class="flex-1 rounded-xl p-2 text-center" :class="isDark ? 'bg-[#21212e]' : 'bg-gray-50'">
                        <p class="text-xs font-bold {{ $cls }} inline-flex px-2 py-0.5 rounded-full mb-1">{{ ucfirst($tier) }}</p>
                        @if($discount > 0)
                        <p class="text-xs line-through" :class="isDark ? 'text-gray-600' : 'text-gray-400'">
                            Rp{{ number_format($price, 0, ',', '.') }}
                        </p>
                        <p class="text-xs font-bold text-orange-500">Rp{{ number_format($final, 0, ',', '.') }}</p>
                        @else
                        <p class="text-xs font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                            Rp{{ number_format($price, 0, ',', '.') }}
                        </p>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- Estimasi & CTA --}}
                <div class="flex items-center justify-between">
                    <span class="text-xs flex items-center gap-1" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                        <i data-lucide="clock" class="w-3 h-3"></i>
                        {{ $c->estimated_days }} hari
                    </span>
                    <a href="{{ route('commission.show', $c->id) }}"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all
                        {{ $c->status === 'open' ? 'bg-orange-500 text-white hover:bg-orange-600' : 'bg-gray-400 text-white cursor-not-allowed' }}">
                        {{ $c->status === 'open' ? 'Lihat Detail' : 'Tutup' }}
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function commissionPage() {
    return {
        isDark: false,
        search: '',
        activeCategory: '',
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