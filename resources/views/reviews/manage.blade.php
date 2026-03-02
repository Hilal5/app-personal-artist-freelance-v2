@extends('layouts.app')
@section('title', 'Manage Reviews — ArtSpace')

@push('styles')
<style>
    .review-card { border-radius: 16px; border: 1px solid; overflow: hidden; }
    .dark .review-card { background: #2a2a3d; border-color: #3a3a50; }
    html:not(.dark) .review-card { background: white; border-color: #e5e7eb; }

    .status-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 99px; font-size: 0.7rem; font-weight: 700; }
    .s-pending  { background: rgba(249,115,22,0.12); color: #f97316; }
    .s-approved { background: rgba(34,197,94,0.12);  color: #22c55e; }
    .s-rejected { background: rgba(239,68,68,0.12);  color: #ef4444; }

    .tag-chip { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 99px; font-size: 0.68rem; font-weight: 700; }
    .tag-good { background: rgba(34,197,94,0.12); color: #22c55e; }
    .tag-bad  { background: rgba(239,68,68,0.12); color: #ef4444; }

    .filter-tab { padding: 6px 14px; border-radius: 99px; font-size: 0.75rem; font-weight: 700; cursor: pointer; transition: all 0.2s; text-decoration: none; white-space: nowrap; }
    .dark .filter-tab { color: #9ca3af; }
    html:not(.dark) .filter-tab { color: #6b7280; }
    .filter-tab.active { background: #f97316; color: white; }
    .dark .filter-tab:not(.active):hover { background: #3a3a50; color: white; }
    html:not(.dark) .filter-tab:not(.active):hover { background: #f3f4f6; }

    .action-btn { padding: 6px 14px; border-radius: 10px; font-size: 0.75rem; font-weight: 700; cursor: pointer; transition: all 0.2s; border: none; }
    .btn-approve { background: rgba(34,197,94,0.12); color: #22c55e; }
    .btn-approve:hover { background: #22c55e; color: white; }
    .btn-reject  { background: rgba(239,68,68,0.12); color: #ef4444; }
    .btn-reject:hover  { background: #ef4444; color: white; }
    .btn-delete  { background: rgba(107,114,128,0.12); color: #6b7280; }
    .btn-delete:hover  { background: #6b7280; color: white; }
</style>
@endpush

@section('content')
@php
    $badTags = ['Komunikasi kurang','Pengerjaan lambat','Kurang sesuai request','Revisi tidak ditangani','Kurang responsif','Tidak sesuai ekspektasi','Harga terlalu mahal'];
@endphp

<div x-data="managePage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Manage Reviews</h1>
            <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Moderasi review dari client</p>
        </div>
    </div>

    {{-- Filter tabs --}}
    <div class="flex gap-2 flex-wrap mb-6">
        @foreach(['all' => 'Semua', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label)
        <a href="{{ route('reviews.manage', ['status' => $key]) }}"
            class="filter-tab {{ $status === $key ? 'active' : '' }}">
            {{ $label }}
            <span class="ml-1 opacity-70">({{ $counts[$key] }})</span>
        </a>
        @endforeach
    </div>

    @if($reviews->isEmpty())
    <div class="flex flex-col items-center justify-center py-20">
        <div class="w-16 h-16 rounded-2xl bg-orange-500/10 flex items-center justify-center mb-4">
            <i data-lucide="star" class="w-8 h-8 text-orange-500"></i>
        </div>
        <p class="font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Tidak ada review</p>
    </div>
    @else
    <div class="flex flex-col gap-4">
        @foreach($reviews as $r)
        <div class="review-card" id="review-{{ $r->id }}">
            <div class="p-4 flex flex-col md:flex-row gap-4">

                {{-- Hasil karya --}}
                @if($r->result_image)
                <div class="w-full md:w-32 h-32 rounded-xl overflow-hidden shrink-0">
                    <img src="{{ Storage::url($r->result_image) }}"
                        class="w-full h-full object-cover cursor-pointer"
                        onclick="window.open(this.src)">
                </div>
                @endif

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($r->client_name) }}&background=7c3aed&color=fff"
                                class="w-8 h-8 rounded-full shrink-0">
                            <div>
                                <p class="text-xs font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">{{ $r->client_name }}</p>
                                <p class="text-xs" :class="isDark ? 'text-gray-500' : 'text-gray-400'">
                                    {{ \Carbon\Carbon::parse($r->created_at)->format('d M Y H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            {{-- Stars --}}
                            <div class="flex gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                <i data-lucide="star" class="w-3.5 h-3.5 {{ $i <= $r->rating ? 'text-orange-500' : 'text-gray-500' }}"
                                    style="{{ $i <= $r->rating ? 'fill:#f97316' : '' }}"></i>
                                @endfor
                            </div>
                            <span class="status-badge s-{{ $r->status }}" id="rbadge-{{ $r->id }}">
                                {{ ucfirst($r->status) }}
                            </span>
                        </div>
                    </div>

                    <p class="text-xs mb-2 font-bold" style="color:#f97316;">{{ $r->commission_title }}</p>

                    @if($r->comment)
                    <p class="text-sm mb-3" :class="isDark ? 'text-gray-300' : 'text-gray-600'">"{{ $r->comment }}"</p>
                    @endif

                    @if(!empty($r->quick_tags))
                    <div class="flex flex-wrap gap-1 mb-3">
                        @foreach($r->quick_tags as $tag)
                        @php $isBad = in_array($tag, $badTags); @endphp
                        <span class="tag-chip {{ $isBad ? 'tag-bad' : 'tag-good' }}">
                            {{ $isBad ? '👎' : '👍' }} {{ $tag }}
                        </span>
                        @endforeach
                    </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex flex-wrap gap-2" id="ractions-{{ $r->id }}">
                        @if($r->status !== 'approved')
                        <button onclick="approveReview({{ $r->id }})"
                            class="action-btn btn-approve flex items-center gap-1.5">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i> Approve
                        </button>
                        @endif
                        @if($r->status !== 'rejected')
                        <button onclick="openRejectModal({{ $r->id }})"
                            class="action-btn btn-reject flex items-center gap-1.5">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i> Reject
                        </button>
                        @endif
                        <button onclick="deleteReview({{ $r->id }})"
                            class="action-btn btn-delete flex items-center gap-1.5">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Modal Reject --}}
    <div x-show="rejectModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display:none;">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="rejectModal = false"></div>
        <div class="relative w-full max-w-sm rounded-2xl p-6 z-10"
            :class="isDark ? 'bg-[#2a2a3d] border border-[#3a3a50]' : 'bg-white border border-gray-200'">
            <h3 class="font-bold text-sm mb-3" :class="isDark ? 'text-white' : 'text-[#21212e]'">Reject Review</h3>
            <textarea x-model="rejectReason" rows="3"
                placeholder="Alasan penolakan (opsional)..."
                class="w-full px-4 py-2.5 rounded-xl text-sm outline-none resize-none border"
                :class="isDark ? 'bg-[#21212e] border-[#3a3a50] text-white placeholder-gray-600' : 'bg-gray-50 border-gray-200 text-[#21212e] placeholder-gray-400'">
            </textarea>
            <div class="flex gap-2 mt-4">
                <button @click="rejectModal = false"
                    class="flex-1 py-2.5 rounded-xl text-sm font-bold"
                    :class="isDark ? 'bg-[#21212e] text-gray-300' : 'bg-gray-100 text-gray-600'">
                    Batal
                </button>
                <button @click="submitReject()"
                    class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-sm font-bold hover:bg-red-600 transition-all">
                    Reject
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]')?.content;

async function approveReview(id) {
    const res  = await fetch(`/reviews/${id}/approve`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
    const data = await res.json();
    if (data.success) {
        const badge = document.getElementById('rbadge-' + id);
        badge.className = 'status-badge s-approved';
        badge.textContent = 'Approved';
        // Hapus tombol approve
        document.querySelectorAll(`#ractions-${id} [onclick*="approveReview"]`).forEach(b => b.remove());
    }
}

async function deleteReview(id) {
    if (!confirm('Hapus review ini permanen?')) return;
    const res  = await fetch(`/reviews/${id}/delete`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
    const data = await res.json();
    if (data.success) {
        const el = document.getElementById('review-' + id);
        el.style.transition = 'all 0.25s';
        el.style.opacity    = '0';
        el.style.transform  = 'scale(0.97)';
        setTimeout(() => el.remove(), 250);
    }
}

function managePage() {
    return {
        isDark: false,
        rejectModal: false,
        rejectReason: '',
        rejectId: null,
        init() {
            this.isDark = document.documentElement.classList.contains('dark');
            new MutationObserver(() => {
                this.isDark = document.documentElement.classList.contains('dark');
            }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            lucide.createIcons();
        },
        async submitReject() {
            const res  = await fetch(`/reviews/${this.rejectId}/reject`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
                body: JSON.stringify({ reason: this.rejectReason }),
            });
            const data = await res.json();
            if (data.success) {
                const badge = document.getElementById('rbadge-' + this.rejectId);
                badge.className = 'status-badge s-rejected';
                badge.textContent = 'Rejected';
                document.querySelectorAll(`#ractions-${this.rejectId} [onclick*="openRejectModal"]`).forEach(b => b.remove());
                this.rejectModal = false;
            }
        }
    }
}

function openRejectModal(id) {
    window._rejectReviewId = id;
    const page = Alpine.$data(document.querySelector('[x-data="managePage()"]'));
    page.rejectId     = id;
    page.rejectReason = '';
    page.rejectModal  = true;
}
</script>
@endpush