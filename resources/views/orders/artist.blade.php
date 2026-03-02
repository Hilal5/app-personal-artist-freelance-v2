@extends('layouts.app')
@section('title', 'Manage Orders — ArtSpace')

@push('styles')
<style>
    .status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 99px;
        font-size: 0.7rem; font-weight: 700;
    }
    .s-pending         { background: rgba(249,115,22,0.12); color: #f97316; }
    .s-confirmed       { background: rgba(59,130,246,0.12); color: #3b82f6; }
    .s-waiting_payment { background: rgba(168,85,247,0.12); color: #a855f7; }
    .s-paid            { background: rgba(234,179,8,0.12);  color: #eab308; }
    .s-completed       { background: rgba(34,197,94,0.12);  color: #22c55e; }
    .s-cancelled       { background: rgba(107,114,128,0.12);color: #6b7280; }
    .s-rejected        { background: rgba(239,68,68,0.12);  color: #ef4444; }

    .order-card {
        border-radius: 16px; border: 1px solid;
        transition: all 0.2s; overflow: hidden;
    }
    .dark .order-card { background: #2a2a3d; border-color: #3a3a50; }
    html:not(.dark) .order-card { background: white; border-color: #e5e7eb; }

    .filter-tab {
        padding: 6px 14px; border-radius: 99px;
        font-size: 0.75rem; font-weight: 700;
        cursor: pointer; transition: all 0.2s;
        text-decoration: none; white-space: nowrap;
    }
    .dark .filter-tab { color: #9ca3af; }
    html:not(.dark) .filter-tab { color: #6b7280; }
    .filter-tab.active { background: #f97316; color: white; }
    .dark .filter-tab:not(.active):hover { background: #3a3a50; color: white; }
    html:not(.dark) .filter-tab:not(.active):hover { background: #f3f4f6; color: #21212e; }

    .action-btn {
        padding: 6px 14px; border-radius: 10px;
        font-size: 0.75rem; font-weight: 700;
        cursor: pointer; transition: all 0.2s;
        border: none; outline: none;
    }
    .btn-confirm  { background: rgba(34,197,94,0.12);  color: #22c55e; }
    .btn-reject   { background: rgba(239,68,68,0.12);  color: #ef4444; }
    .btn-done     { background: rgba(168,85,247,0.12); color: #a855f7; }
    .btn-payment  { background: rgba(34,197,94,0.12);  color: #22c55e; }
    .btn-confirm:hover  { background: #22c55e; color: white; }
    .btn-reject:hover   { background: #ef4444; color: white; }
    .btn-done:hover     { background: #a855f7; color: white; }
    .btn-payment:hover  { background: #22c55e; color: white; }

    .payment-methods {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;
    }
    .pm-item {
        padding: 8px; border-radius: 10px; text-align: center;
        font-size: 0.7rem; font-weight: 700; cursor: pointer;
        border: 2px solid transparent; transition: all 0.2s;
    }
    .dark .pm-item { background: #21212e; color: #9ca3af; }
    html:not(.dark) .pm-item { background: #f9fafb; color: #6b7280; border-color: #e5e7eb; }
    .pm-item.selected { border-color: #f97316; color: #f97316; }
</style>
@endpush

@section('content')
@php
    $statusLabels = [
        'all'             => 'Semua',
        'pending'         => 'Pending',
        'confirmed'       => 'In Progress',
        'waiting_payment' => 'Menunggu Bayar',
        'paid'            => 'Sudah Bayar',
        'completed'       => 'Selesai',
    ];
    $paymentLabels = [
        'bri'       => 'BRI',
        'seabank'   => 'SeaBank',
        'bank_jago' => 'Bank Jago',
        'dana'      => 'DANA',
        'gopay'     => 'GoPay',
        'shopeepay' => 'ShopeePay',
    ];
@endphp

<div x-data="ordersPage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Manage Orders</h1>
            <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Kelola semua order commission masuk</p>
        </div>
    </div>

    {{-- Filter tabs --}}
    <div class="flex gap-2 flex-wrap mb-6 overflow-x-auto pb-1">
        @foreach($statusLabels as $key => $label)
        <a href="{{ route('orders.artist', ['status' => $key]) }}"
            class="filter-tab {{ $status === $key ? 'active' : '' }}">
            {{ $label }}
            <span class="ml-1 opacity-70">({{ $counts[$key] ?? 0 }})</span>
        </a>
        @endforeach
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 rounded-xl text-sm font-bold text-green-400 border border-green-500/30 bg-green-500/10">
        ✅ {{ session('success') }}
    </div>
    @endif

    @if($orders->isEmpty())
    <div class="flex flex-col items-center justify-center py-24">
        <div class="w-16 h-16 rounded-2xl bg-orange-500/10 flex items-center justify-center mb-4">
            <i data-lucide="clipboard-list" class="w-8 h-8 text-orange-500"></i>
        </div>
        <p class="font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Belum ada order</p>
        <p class="text-xs mt-1" :class="isDark ? 'text-gray-500' : 'text-gray-400'">Order masuk akan muncul di sini</p>
    </div>
    @else
    <div class="flex flex-col gap-4">
        @foreach($orders as $o)
        <div class="order-card" id="order-{{ $o->id }}">

            {{-- Header order --}}
            <div class="p-4 flex flex-wrap items-start justify-between gap-3 border-b"
                :class="isDark ? 'border-[#3a3a50]' : 'border-gray-100'">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-mono font-bold text-orange-500">{{ $o->order_number }}</span>
                        <span class="status-badge s-{{ $o->status }}" id="status-badge-{{ $o->id }}">
                            {{ ucfirst(str_replace('_', ' ', $o->status)) }}
                        </span>
                    </div>
                    <p class="font-bold text-sm" :class="isDark ? 'text-white' : 'text-[#21212e]'">{{ $o->commission_title }}</p>
                    <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                        Dari: <strong>{{ $o->client_name }}</strong> &nbsp;·&nbsp;
                        Tier: <strong class="text-orange-500">{{ ucfirst($o->tier) }}</strong> &nbsp;·&nbsp;
                        Metode: <strong>{{ $paymentLabels[$o->payment_method] }}</strong>
                    </p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-lg font-bold text-orange-500">Rp{{ number_format($o->final_price, 0, ',', '.') }}</p>
                    @if($o->discount > 0)
                    <p class="text-xs text-red-400">Diskon {{ $o->discount }}%</p>
                    @endif
                    <p class="text-xs" :class="isDark ? 'text-gray-500' : 'text-gray-400'">
                        {{ \Carbon\Carbon::parse($o->created_at)->format('d M Y') }}
                    </p>
                </div>
            </div>

            {{-- Detail --}}
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    {{-- Deskripsi request --}}
                    <div>
                        <p class="text-xs font-bold mb-1" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Deskripsi Request</p>
                        <p class="text-sm" :class="isDark ? 'text-gray-300' : 'text-gray-600'">{{ $o->description }}</p>
                        @if($o->notes)
                        <p class="text-xs mt-2 italic" :class="isDark ? 'text-gray-500' : 'text-gray-400'">📝 {{ $o->notes }}</p>
                        @endif
                        @if($o->client_deadline)
                        <p class="text-xs mt-1" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                            🗓 Deadline: <strong>{{ \Carbon\Carbon::parse($o->client_deadline)->format('d M Y') }}</strong>
                        </p>
                        @endif
                    </div>

                    {{-- Referensi gambar --}}
                    @if($o->references->isNotEmpty())
                    <div>
                        <p class="text-xs font-bold mb-2" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Referensi</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($o->references as $ref)
                            @if(str_contains($ref->file_type, 'image'))
                            <img src="{{ Storage::url($ref->file_path) }}"
                                class="w-16 h-16 rounded-xl object-cover cursor-pointer border"
                                :class="isDark ? 'border-[#3a3a50]' : 'border-gray-200'"
                                onclick="window.open(this.src)">
                            @else
                            <a href="{{ Storage::url($ref->file_path) }}" target="_blank"
                                class="w-16 h-16 rounded-xl flex flex-col items-center justify-center border text-orange-500 text-xs font-bold"
                                :class="isDark ? 'bg-[#21212e] border-[#3a3a50]' : 'bg-gray-50 border-gray-200'">
                                <i data-lucide="file" class="w-5 h-5 mb-1"></i>
                                File
                            </a>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Bukti bayar (jika sudah paid) --}}
                @if($o->payment && in_array($o->status, ['paid', 'completed']))
                <div class="p-3 rounded-xl mb-3 border"
                    :class="isDark ? 'bg-[#21212e] border-[#3a3a50]' : 'bg-yellow-50 border-yellow-200'">
                    <p class="text-xs font-bold mb-2 text-yellow-500">💳 Bukti Pembayaran</p>
                    <div class="flex items-center gap-3">
                        <a href="{{ Storage::url($o->payment->proof_path) }}" target="_blank">
                            <img src="{{ Storage::url($o->payment->proof_path) }}"
                                class="w-16 h-16 rounded-xl object-cover border border-yellow-300">
                        </a>
                        <div>
                            @if($o->payment->notes)
                            <p class="text-xs" :class="isDark ? 'text-gray-300' : 'text-gray-600'">{{ $o->payment->notes }}</p>
                            @endif
                            <p class="text-xs" :class="isDark ? 'text-gray-500' : 'text-gray-400'">
                                Dikirim: {{ \Carbon\Carbon::parse($o->payment->created_at)->format('d M Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Action buttons --}}
                <div class="flex flex-wrap gap-2">

                    {{-- Chat with client --}}
                    <a href="{{ route('chat.index', ['contact' => $o->client_id]) }}"
                        class="action-btn flex items-center gap-1.5"
                        :class="isDark ? 'bg-[#21212e] text-gray-300 hover:text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> Chat Client
                    </a>

                    @if($o->status === 'pending')
                    <button onclick="confirmOrder({{ $o->id }})"
                        class="action-btn btn-confirm flex items-center gap-1.5">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i> Konfirmasi
                    </button>
                    <button onclick="openRejectModal({{ $o->id }})"
                        class="action-btn btn-reject flex items-center gap-1.5">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i> Tolak
                    </button>
                    @endif

                    @if($o->status === 'confirmed')
                    <button onclick="markDone({{ $o->id }})"
                        class="action-btn btn-done flex items-center gap-1.5">
                        <i data-lucide="check-check" class="w-3.5 h-3.5"></i> Tandai Selesai
                    </button>
                    @endif

                    @if($o->status === 'paid')
                    <button onclick="confirmPayment({{ $o->id }})"
                        class="action-btn btn-payment flex items-center gap-1.5">
                        <i data-lucide="badge-check" class="w-3.5 h-3.5"></i> Konfirmasi Pembayaran
                    </button>
                    @endif

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
            <h3 class="font-bold text-sm mb-4" :class="isDark ? 'text-white' : 'text-[#21212e]'">Tolak Order</h3>
            <textarea x-model="rejectReason" rows="3"
                placeholder="Alasan penolakan (opsional)..."
                class="w-full px-4 py-2.5 rounded-xl text-sm outline-none resize-none border"
                :class="isDark ? 'bg-[#21212e] border-[#3a3a50] text-white placeholder-gray-600' : 'bg-gray-50 border-gray-200 text-[#21212e] placeholder-gray-400'">
            </textarea>
            <div class="flex gap-2 mt-4">
                <button @click="rejectModal = false"
                    class="flex-1 py-2.5 rounded-xl text-sm font-bold transition-all"
                    :class="isDark ? 'bg-[#21212e] text-gray-300' : 'bg-gray-100 text-gray-600'">
                    Batal
                </button>
                <button @click="submitRejectFromPage()"
                    class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-sm font-bold hover:bg-red-600 transition-all">
                    Tolak Order
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]')?.content;

const statusLabels = {
    pending: 'Pending', confirmed: 'In Progress',
    waiting_payment: 'Menunggu Bayar', paid: 'Sudah Bayar',
    completed: 'Selesai', cancelled: 'Cancelled', rejected: 'Rejected'
};
const statusClasses = {
    pending: 's-pending', confirmed: 's-confirmed',
    waiting_payment: 's-waiting_payment', paid: 's-paid',
    completed: 's-completed', cancelled: 's-cancelled', rejected: 's-rejected'
};

function updateBadge(orderId, newStatus) {
    const badge = document.getElementById('status-badge-' + orderId);
    if (badge) {
        badge.className = 'status-badge ' + (statusClasses[newStatus] || '');
        badge.textContent = statusLabels[newStatus] || newStatus;
    }
}

async function confirmOrder(id) {
    if (!confirm('Konfirmasi order ini?')) return;
    const res  = await fetch(`/orders/${id}/confirm`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
    const data = await res.json();
    if (data.success) {
        updateBadge(id, 'confirmed');
        // Sembunyikan tombol konfirmasi & tolak, tampilkan tombol selesai
        const card = document.getElementById('order-' + id);
        card.querySelectorAll('[onclick*="confirmOrder"], [onclick*="openRejectModal"]').forEach(b => b.remove());
        const actionsDiv = card.querySelector('.flex.flex-wrap.gap-2');
        const btn = document.createElement('button');
        btn.className = 'action-btn btn-done flex items-center gap-1.5';
        btn.setAttribute('onclick', `markDone(${id})`);
        btn.innerHTML = '<i data-lucide="check-check" style="width:14px;height:14px;"></i> Tandai Selesai';
        actionsDiv.appendChild(btn);
        lucide.createIcons();
    }
}

async function markDone(id) {
    if (!confirm('Tandai order ini selesai?')) return;
    const res  = await fetch(`/orders/${id}/mark-done`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
    const data = await res.json();
    if (data.success) {
        updateBadge(id, 'waiting_payment');
        const card = document.getElementById('order-' + id);
        card.querySelectorAll('[onclick*="markDone"]').forEach(b => b.remove());
        lucide.createIcons();
    }
}

async function confirmPayment(id) {
    if (!confirm('Konfirmasi pembayaran sudah diterima?')) return;
    const res  = await fetch(`/orders/${id}/confirm-payment`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
    const data = await res.json();
    if (data.success) {
        updateBadge(id, 'completed');
        const card = document.getElementById('order-' + id);
        card.querySelectorAll('[onclick*="confirmPayment"]').forEach(b => b.remove());
        lucide.createIcons();
    }
}

function ordersPage() {
    return {
        isDark: false,
        rejectModal: false,
        rejectReason: '',
        rejectOrderId: null,
        init() {
            this.isDark = document.documentElement.classList.contains('dark');
            new MutationObserver(() => {
                this.isDark = document.documentElement.classList.contains('dark');
            }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            lucide.createIcons();
        }
    }
}

function openRejectModal(id) {
    window._rejectOrderId = id;
    const page = Alpine.$data(document.querySelector('[x-data="ordersPage()"]'));
    page.rejectOrderId = id;
    page.rejectReason  = '';
    page.rejectModal   = true;
}

async function submitRejectFromPage() {
    const page = Alpine.$data(document.querySelector('[x-data="ordersPage()"]'));
    const id   = page.rejectOrderId || window._rejectOrderId;
    if (!id) { alert('Order ID tidak ditemukan'); return; }

    const res = await fetch(`/orders/${id}/reject`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
        body: JSON.stringify({ reason: page.rejectReason }),
    });
    const data = await res.json();
    if (data.success) {
        updateBadge(id, 'rejected');
        page.rejectModal = false;
        const card = document.getElementById('order-' + id);
        card.querySelectorAll('[onclick*="confirmOrder"], [onclick*="openRejectModal"]').forEach(b => b.remove());
    }
}
</script>
@endpush