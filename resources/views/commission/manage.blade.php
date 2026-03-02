@extends('layouts.app')
@section('title', 'Manage Commission — ArtSpace')

@section('content')
<div x-data="managePage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Manage Commission</h1>
            <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Kelola semua paket commission kamu</p>
        </div>
        <a href="{{ route('commission.create') }}"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i> Buat Baru
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 rounded-xl text-sm font-bold text-green-400 border border-green-500/30 bg-green-500/10">
        ✅ {{ session('success') }}
    </div>
    @endif

    @if($commissions->isEmpty())
    <div class="flex flex-col items-center justify-center py-24">
        <div class="w-16 h-16 rounded-2xl bg-orange-500/10 flex items-center justify-center mb-4">
            <i data-lucide="brush" class="w-8 h-8 text-orange-500"></i>
        </div>
        <p class="font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Belum ada commission</p>
        <p class="text-xs mt-1 mb-4" :class="isDark ? 'text-gray-500' : 'text-gray-400'">Buat commission pertamamu</p>
        <a href="{{ route('commission.create') }}"
            class="px-6 py-2.5 rounded-xl bg-orange-500 text-white font-bold text-sm hover:bg-orange-600 transition-all">
            Buat Commission
        </a>
    </div>
    @else
    <div class="flex flex-col gap-3">
        @foreach($commissions as $c)
        @php
            $slotPercent = $c->max_slots > 0 ? ($c->used_slots / $c->max_slots) * 100 : 0;
        @endphp
        <div class="rounded-2xl p-4 border flex flex-col md:flex-row gap-4"
            :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">

            {{-- Thumbnail --}}
            <div class="w-full md:w-32 h-24 rounded-xl overflow-hidden bg-gray-800 shrink-0">
                @if($c->media->isNotEmpty())
                    @if($c->media->first()->file_type === 'video')
                    <video src="{{ Storage::url($c->media->first()->file_path) }}"
                        class="w-full h-full object-cover" muted></video>
                    @else
                    <img src="{{ Storage::url($c->media->first()->file_path) }}"
                        class="w-full h-full object-cover">
                    @endif
                @else
                <div class="w-full h-full flex items-center justify-center">
                    <i data-lucide="image" class="w-6 h-6 text-gray-600"></i>
                </div>
                @endif
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                    <div>
                        <h3 class="font-bold text-sm" :class="isDark ? 'text-white' : 'text-[#21212e]'">{{ $c->title }}</h3>
                        <span class="text-xs" :class="isDark ? 'text-gray-400' : 'text-gray-500'">{{ $c->category }}</span>
                    </div>

                    {{-- Toggle status --}}
                    <button onclick="toggleStatus({{ $c->id }}, this)"
                        data-status="{{ $c->status }}"
                        class="text-xs px-3 py-1 rounded-full font-bold transition-all"
                        style="{{ $c->status === 'open'
                            ? 'background:rgba(34,197,94,0.12);color:#22c55e;'
                            : 'background:rgba(239,68,68,0.12);color:#ef4444;' }}">
                        {{ $c->status === 'open' ? '● Open' : '● Closed' }}
                    </button>
                </div>

                {{-- Slot bar --}}
                <div class="mb-2">
                    <div class="flex justify-between text-xs mb-1">
                        <span :class="isDark ? 'text-gray-400' : 'text-gray-500'">Slot terpakai</span>
                        <span :class="isDark ? 'text-white' : 'text-[#21212e]'" class="font-bold">{{ $c->used_slots }} / {{ $c->max_slots }}</span>
                    </div>
                    <div class="h-1.5 rounded-full overflow-hidden" :class="isDark ? 'bg-[#21212e]' : 'bg-gray-100'">
                        <div class="h-full bg-orange-500 rounded-full" style="width: {{ $slotPercent }}%"></div>
                    </div>
                </div>

                {{-- Tier prices --}}
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach(['basic', 'standard', 'premium'] as $tier)
                    @php
                        $p = $c->{'tier_' . $tier . '_price'};
                        $d = $c->{'tier_' . $tier . '_discount'};
                        $f = $p - ($p * $d / 100);
                        $colors = ['basic' => 'text-green-500', 'standard' => 'text-orange-500', 'premium' => 'text-purple-500'];
                    @endphp
                    <span class="text-xs px-2 py-1 rounded-lg font-bold {{ $colors[$tier] }}"
                        :class="isDark ? 'bg-[#21212e]' : 'bg-gray-50'">
                        {{ ucfirst($tier) }}: Rp{{ number_format($f, 0, ',', '.') }}
                        @if($d > 0)<span class="text-red-400"> (-{{ $d }}%)</span>@endif
                    </span>
                    @endforeach
                </div>

                {{-- Actions --}}
                <div class="flex gap-2">
                    <a href="{{ route('commission.show', $c->id) }}"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                        :class="isDark ? 'bg-[#21212e] text-gray-300 hover:text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i> Preview
                    </a>
                    <a href="{{ route('commission.edit', $c->id) }}"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                        :class="isDark ? 'bg-[#21212e] text-orange-400 hover:bg-orange-500/10' : 'bg-orange-50 text-orange-500 hover:bg-orange-100'">
                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                    </a>
                    <button onclick="deleteCommission({{ $c->id }}, '{{ addslashes($c->title) }}')"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                        :class="isDark ? 'bg-[#21212e] text-red-400 hover:bg-red-500/10' : 'bg-red-50 text-red-500 hover:bg-red-100'">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<form id="deleteForm" method="POST" style="display:none;">
    @csrf
</form>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]')?.content;

async function toggleStatus(id, btn) {
    const res  = await fetch(`/commission/${id}/toggle`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF }
    });
    const data = await res.json();
    if (data.success) {
        btn.dataset.status = data.status;
        if (data.status === 'open') {
            btn.textContent    = '● Open';
            btn.style.background = 'rgba(34,197,94,0.12)';
            btn.style.color      = '#22c55e';
        } else {
            btn.textContent    = '● Closed';
            btn.style.background = 'rgba(239,68,68,0.12)';
            btn.style.color      = '#ef4444';
        }
    }
}

function deleteCommission(id, title) {
    if (!confirm(`Hapus commission "${title}"? Semua data terkait akan ikut terhapus.`)) return;
    const form   = document.getElementById('deleteForm');
    form.action  = `/commission/${id}/delete`;
    form.submit();
}

function managePage() {
    return {
        isDark: false,
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