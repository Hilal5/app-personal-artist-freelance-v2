@extends('layouts.app')
@section('title', 'Manage Portfolio — ArtSpace')

@section('content')
<div x-data="managePage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Manage Portfolio</h1>
            <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">{{ $portfolios->count() }} total karya</p>
        </div>
        <a href="{{ route('portfolio.create') }}"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Karya
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 rounded-xl text-sm font-bold text-green-400 border border-green-500/30 bg-green-500/10">
        ✅ {{ session('success') }}
    </div>
    @endif

    @if($portfolios->isEmpty())
    <div class="flex flex-col items-center justify-center py-20">
        <div class="w-16 h-16 rounded-2xl bg-orange-500/10 flex items-center justify-center mb-4">
            <i data-lucide="image" class="w-8 h-8 text-orange-500"></i>
        </div>
        <p class="font-bold mb-4" :class="isDark ? 'text-white' : 'text-[#21212e]'">Belum ada portfolio</p>
        <a href="{{ route('portfolio.create') }}"
            class="px-6 py-2.5 rounded-xl bg-orange-500 text-white font-bold text-sm hover:bg-orange-600 transition-all">
            Tambah Sekarang
        </a>
    </div>
    @else
    <div class="flex flex-col gap-3">
        @foreach($portfolios as $p)
        <div class="rounded-2xl border flex flex-col md:flex-row gap-4 p-4"
            :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">

            {{-- Thumbnail --}}
            <div class="w-full md:w-28 h-24 rounded-xl overflow-hidden shrink-0 bg-gray-800">
                @if($p->cover)
                    @if($p->cover->file_type === 'video')
                    <video src="{{ Storage::url($p->cover->file_path) }}" class="w-full h-full object-cover" muted></video>
                    @else
                    <img src="{{ Storage::url($p->cover->file_path) }}" class="w-full h-full object-cover">
                    @endif
                @else
                <div class="w-full h-full flex items-center justify-center"
                    style="background: linear-gradient(135deg, #f97316, #a855f7);">
                    <i data-lucide="image" class="w-6 h-6 text-white opacity-50"></i>
                </div>
                @endif
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                    <div>
                        <h3 class="font-bold text-sm" :class="isDark ? 'text-white' : 'text-[#21212e]'">{{ $p->title }}</h3>
                        <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                            {{ $p->category }}
                            @if($p->software) · {{ $p->software }} @endif
                        </p>
                    </div>
                    <button onclick="toggleStatus({{ $p->id }}, this)"
                        data-status="{{ $p->status }}"
                        class="text-xs px-3 py-1 rounded-full font-bold transition-all"
                        style="{{ $p->status === 'published'
                            ? 'background:rgba(34,197,94,0.12);color:#22c55e;'
                            : 'background:rgba(107,114,128,0.12);color:#6b7280;' }}">
                        {{ $p->status === 'published' ? '● Published' : '● Draft' }}
                    </button>
                </div>

                {{-- Stats & Tags --}}
                <div class="flex flex-wrap items-center gap-3 mb-3">
                    <span class="text-xs flex items-center gap-1" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                        <i data-lucide="eye" class="w-3 h-3 text-orange-500"></i>
                        {{ number_format($p->views) }} views
                    </span>
                    @if($p->created_date)
                    <span class="text-xs flex items-center gap-1" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                        <i data-lucide="calendar" class="w-3 h-3 text-orange-500"></i>
                        {{ \Carbon\Carbon::parse($p->created_date)->format('d M Y') }}
                    </span>
                    @endif
                    @foreach($p->tags as $tag)
                    <span class="text-xs px-2 py-0.5 rounded-full font-bold"
                        style="background:rgba(249,115,22,0.1);color:#f97316;">#{{ $tag }}</span>
                    @endforeach
                </div>

                {{-- Actions --}}
                <div class="flex gap-2">
                    <a href="{{ route('portfolio.index') }}"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                        :class="isDark ? 'bg-[#21212e] text-gray-300 hover:text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i> Preview
                    </a>
                    <a href="{{ route('portfolio.edit', $p->id) }}"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                        :class="isDark ? 'bg-[#21212e] text-orange-400 hover:bg-orange-500/10' : 'bg-orange-50 text-orange-500 hover:bg-orange-100'">
                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                    </a>
                    <button onclick="deletePortfolio({{ $p->id }}, '{{ addslashes($p->title) }}')"
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

<form id="deleteForm" method="POST" style="display:none;">@csrf</form>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]')?.content;

async function toggleStatus(id, btn) {
    const res  = await fetch(`/portfolio/${id}/toggle`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
    const data = await res.json();
    if (data.success) {
        btn.dataset.status = data.status;
        if (data.status === 'published') {
            btn.textContent    = '● Published';
            btn.style.background = 'rgba(34,197,94,0.12)';
            btn.style.color      = '#22c55e';
        } else {
            btn.textContent    = '● Draft';
            btn.style.background = 'rgba(107,114,128,0.12)';
            btn.style.color      = '#6b7280';
        }
    }
}

function deletePortfolio(id, title) {
    if (!confirm(`Hapus portfolio "${title}"?`)) return;
    const form  = document.getElementById('deleteForm');
    form.action = `/portfolio/${id}/delete`;
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