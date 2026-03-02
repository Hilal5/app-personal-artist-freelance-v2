@extends('layouts.app')
@section('title', 'My Orders — ArtSpace')

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
        overflow: hidden; transition: all 0.2s;
    }
    .dark .order-card { background: #2a2a3d; border-color: #3a3a50; }
    html:not(.dark) .order-card { background: white; border-color: #e5e7eb; }

    .timeline {
        display: flex; gap: 0; overflow-x: auto;
        padding: 12px 0; scrollbar-width: none;
    }
    .timeline::-webkit-scrollbar { display: none; }
    .timeline-step {
        display: flex; flex-direction: column; align-items: center;
        min-width: 80px; position: relative;
    }
    .timeline-step:not(:last-child)::after {
        content: ''; position: absolute;
        top: 14px; left: 50%; width: 100%; height: 2px;
        background: currentColor; opacity: 0.2;
    }
    .timeline-dot {
        width: 28px; height: 28px; border-radius: 99px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.65rem; font-weight: 800; z-index: 1;
        border: 2px solid;
    }
    .dot-done    { background: #22c55e; border-color: #22c55e; color: white; }
    .dot-current { background: #f97316; border-color: #f97316; color: white; }
    .dot-pending { background: transparent; border-color: #4b5563; color: #4b5563; }

    .form-input {
        width: 100%; padding: 10px 14px; border-radius: 12px;
        font-size: 0.8rem; outline: none; border: 1px solid; transition: all 0.2s;
    }
    .dark .form-input { background: #21212e; border-color: #3a3a50; color: white; }
    html:not(.dark) .form-input { background: #f9fafb; border-color: #e5e7eb; color: #21212e; }
    .dark .form-input:focus { border-color: #f97316; }
    html:not(.dark) .form-input:focus { border-color: #21212e; }
</style>
@endpush

@section('content')
@php
    $paymentLabels = [
        'bri'       => 'BRI',
        'seabank'   => 'SeaBank',
        'bank_jago' => 'Bank Jago',
        'dana'      => 'DANA',
        'gopay'     => 'GoPay',
        'shopeepay' => 'ShopeePay',
    ];
    $steps = ['pending', 'confirmed', 'waiting_payment', 'paid', 'completed'];
    $stepLabels = [
        'pending'         => 'Pending',
        'confirmed'       => 'In Progress',
        'waiting_payment' => 'Minta Bayar',
        'paid'            => 'Sudah Bayar',
        'completed'       => 'Selesai',
    ];
@endphp

<div x-data="clientOrders()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">My Orders</h1>
            <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Pantau status pesanan commission kamu</p>
        </div>
        <a href="{{ route('commission.index') }}"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i> Order Baru
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 rounded-xl text-sm font-bold text-green-400 border border-green-500/30 bg-green-500/10">
        ✅ {{ session('success') }}
    </div>
    @endif

    @if($orders->isEmpty())
    <div class="flex flex-col items-center justify-center py-24">
        <div class="w-16 h-16 rounded-2xl bg-orange-500/10 flex items-center justify-center mb-4">
            <i data-lucide="shopping-bag" class="w-8 h-8 text-orange-500"></i>
        </div>
        <p class="font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Belum ada order</p>
        <p class="text-xs mt-1 mb-4" :class="isDark ? 'text-gray-500' : 'text-gray-400'">Mulai order commission sekarang</p>
        <a href="{{ route('commission.index') }}"
            class="px-6 py-2.5 rounded-xl bg-orange-500 text-white font-bold text-sm hover:bg-orange-600 transition-all">
            Lihat Commission
        </a>
    </div>
    @else
    <div class="flex flex-col gap-4">
        @foreach($orders as $o)
        @php
            $currentStepIndex = array_search($o->status, $steps);
        @endphp
        <div class="order-card" id="order-{{ $o->id }}">

            {{-- Header --}}
            <div class="p-4 flex flex-wrap items-start justify-between gap-3 border-b"
                :class="isDark ? 'border-[#3a3a50]' : 'border-gray-100'">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-mono font-bold text-orange-500">{{ $o->order_number }}</span>
                        <span class="status-badge s-{{ $o->status }}">
                            {{ ucfirst(str_replace('_', ' ', $o->status)) }}
                        </span>
                    </div>
                    <p class="font-bold text-sm" :class="isDark ? 'text-white' : 'text-[#21212e]'">{{ $o->commission_title }}</p>
                    <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                        Tier: <strong class="text-orange-500">{{ ucfirst($o->tier) }}</strong> &nbsp;·&nbsp;
                        Bayar via: <strong>{{ $paymentLabels[$o->payment_method] }}</strong>
                    </p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-lg font-bold text-orange-500">Rp{{ number_format($o->final_price, 0, ',', '.') }}</p>
                    <p class="text-xs" :class="isDark ? 'text-gray-500' : 'text-gray-400'">
                        {{ \Carbon\Carbon::parse($o->created_at)->format('d M Y') }}
                    </p>
                </div>
            </div>

            {{-- Timeline --}}
            @if(!in_array($o->status, ['cancelled', 'rejected']))
            <div class="px-4 border-b" :class="isDark ? 'border-[#3a3a50]' : 'border-gray-100'">
                <div class="timeline">
                    @foreach($steps as $i => $step)
                    @php
                        $isDone    = $currentStepIndex !== false && $i < $currentStepIndex;
                        $isCurrent = $currentStepIndex !== false && $i === $currentStepIndex;
                        $dotClass  = $isDone ? 'dot-done' : ($isCurrent ? 'dot-current' : 'dot-pending');
                    @endphp
                    <div class="timeline-step">
                        <div class="timeline-dot {{ $dotClass }}">
                            @if($isDone)✓@else{{ $i + 1 }}@endif
                        </div>
                        <p class="text-center mt-1 px-1" style="font-size:0.6rem;"
                            :class="'{{ $isCurrent }}' === '1' ? 'text-orange-500 font-bold' : (isDark ? 'text-gray-500' : 'text-gray-400')">
                            {{ $stepLabels[$step] }}
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="px-4 py-2 border-b" :class="isDark ? 'border-[#3a3a50]' : 'border-gray-100'">
                <p class="text-xs font-bold {{ $o->status === 'rejected' ? 'text-red-400' : 'text-gray-400' }}">
                    {{ $o->status === 'rejected' ? '❌ Order ditolak' : '🚫 Order dibatalkan' }}
                    @if($o->status === 'rejected' && $o->reject_reason)
                    — {{ $o->reject_reason }}
                    @elseif($o->status === 'cancelled' && $o->cancel_reason)
                    — {{ $o->cancel_reason }}
                    @endif
                </p>
            </div>
            @endif

            {{-- Body --}}
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-xs font-bold mb-1" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Request Kamu</p>
                        <p class="text-sm" :class="isDark ? 'text-gray-300' : 'text-gray-600'">{{ $o->description }}</p>
                        @if($o->client_deadline)
                        <p class="text-xs mt-1" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                            🗓 Deadline: <strong>{{ \Carbon\Carbon::parse($o->client_deadline)->format('d M Y') }}</strong>
                        </p>
                        @endif
                    </div>

                    @if($o->references->isNotEmpty())
                    <div>
                        <p class="text-xs font-bold mb-2" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Referensi</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($o->references->take(4) as $ref)
                            @if(str_contains($ref->file_type, 'image'))
                            <img src="{{ Storage::url($ref->file_path) }}"
                                class="w-14 h-14 rounded-xl object-cover cursor-pointer border"
                                :class="isDark ? 'border-[#3a3a50]' : 'border-gray-200'"
                                onclick="window.open(this.src)">
                            @else
                            <a href="{{ Storage::url($ref->file_path) }}" target="_blank"
                                class="w-14 h-14 rounded-xl flex flex-col items-center justify-center border text-orange-500 text-xs font-bold"
                                :class="isDark ? 'bg-[#21212e] border-[#3a3a50]' : 'bg-gray-50 border-gray-200'">
                                <i data-lucide="file" class="w-4 h-4 mb-0.5"></i>File
                            </a>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Upload bukti bayar --}}
                @if($o->status === 'waiting_payment')
                <div class="p-4 rounded-xl mb-3 border"
                    :class="isDark ? 'bg-[#21212e] border-[#3a3a50]' : 'bg-purple-50 border-purple-200'">
                    <p class="text-xs font-bold mb-3" style="color:#a855f7;">
                        💳 Segera lakukan pembayaran & upload bukti transfer
                    </p>
                    <div class="mb-3 p-3 rounded-xl text-xs"
                        :class="isDark ? 'bg-[#2a2a3d]' : 'bg-white'">
                        <p class="font-bold mb-1" :class="isDark ? 'text-white' : 'text-[#21212e]'">Info Pembayaran:</p>
                        <p :class="isDark ? 'text-gray-400' : 'text-gray-600'">
                            Metode: <strong>{{ $paymentLabels[$o->payment_method] }}</strong>
                        </p>
                        <p class="text-orange-500 font-bold mt-1">
                            Total: Rp{{ number_format($o->final_price, 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-xs" :class="isDark ? 'text-gray-500' : 'text-gray-400'">
                            ℹ️ Nomor rekening/akun akan diberikan artist via chat
                        </p>
                    </div>

                    <form id="payForm-{{ $o->id }}" class="flex flex-col gap-2">
                        <input type="file" id="proofFile-{{ $o->id }}"
                            accept="image/*,.pdf" class="form-input text-xs py-2">
                        <textarea id="proofNote-{{ $o->id }}" rows="2"
                            placeholder="Catatan (opsional)..."
                            class="form-input resize-none"
                            :class="isDark ? 'placeholder-gray-600' : 'placeholder-gray-400'"></textarea>
                        <button type="button" onclick="uploadPayment({{ $o->id }})"
                            class="py-2 rounded-xl bg-purple-500 text-white text-xs font-bold hover:bg-purple-600 transition-all">
                            Upload Bukti Bayar
                        </button>
                    </form>
                </div>
                @endif

                {{-- Bukti yang sudah diupload --}}
                @if($o->payment && $o->status === 'paid')
                <div class="p-3 rounded-xl mb-3 border"
                    :class="isDark ? 'bg-[#21212e] border-[#3a3a50]' : 'bg-yellow-50 border-yellow-200'">
                    <p class="text-xs font-bold text-yellow-500 mb-2">⏳ Menunggu konfirmasi pembayaran dari artist</p>
                    <a href="{{ Storage::url($o->payment->proof_path) }}" target="_blank"
                        class="text-xs text-orange-500 underline">Lihat bukti bayar</a>
                </div>
                @endif

                {{-- Actions --}}
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('chat.index') }}"
                        class="action-btn flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg transition-all"
                        :class="isDark ? 'bg-[#21212e] text-gray-300 hover:text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> Chat Artist
                    </a>

                    @if(in_array($o->status, ['pending', 'confirmed']))
                    <button onclick="openCancelModal({{ $o->id }})"
                        class="action-btn flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg transition-all"
                        style="background:rgba(239,68,68,0.1);color:#ef4444;">
                        <i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Batalkan
                    </button>
                    @endif

                    {{-- Tombol review kalau sudah completed dan belum review --}}
                    @if($o->status === 'completed')
                    @php $hasReview = DB::table('reviews')->where('order_id', $o->id)->exists(); @endphp
                    @if(!$hasReview)
                    <a href="{{ route('reviews.create', $o->id) }}"
                        class="action-btn flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg transition-all"
                        style="background:rgba(249,115,22,0.1);color:#f97316;">
                        <i data-lucide="star" class="w-3.5 h-3.5"></i> Beri Review
                    </a>
                    @else
                    <span class="text-xs px-3 py-1.5 rounded-lg font-bold"
                        style="background:rgba(34,197,94,0.1);color:#22c55e;">
                        ✓ Sudah Review
                    </span>
                    @endif
                    @endif
                </div>
                
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Modal Cancel --}}
    <div x-show="cancelModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display:none;">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="cancelModal = false"></div>
        <div class="relative w-full max-w-sm rounded-2xl p-6 z-10"
            :class="isDark ? 'bg-[#2a2a3d] border border-[#3a3a50]' : 'bg-white border border-gray-200'">
            <h3 class="font-bold text-sm mb-1" :class="isDark ? 'text-white' : 'text-[#21212e]'">Batalkan Order</h3>
            <p class="text-xs mb-4" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Yakin ingin membatalkan order ini?</p>
            <textarea x-model="cancelReason" rows="3"
                placeholder="Alasan pembatalan (opsional)..."
                class="w-full px-4 py-2.5 rounded-xl text-sm outline-none resize-none border mb-4"
                :class="isDark ? 'bg-[#21212e] border-[#3a3a50] text-white placeholder-gray-600' : 'bg-gray-50 border-gray-200 text-[#21212e] placeholder-gray-400'">
            </textarea>
            <div class="flex gap-2">
                <button @click="cancelModal = false"
                    class="flex-1 py-2.5 rounded-xl text-sm font-bold transition-all"
                    :class="isDark ? 'bg-[#21212e] text-gray-300' : 'bg-gray-100 text-gray-600'">
                    Tidak
                </button>
                <button @click="submitCancel()"
                    class="flex-1 py-2.5 rounded-xl bg-red-500 text-white text-sm font-bold hover:bg-red-600 transition-all">
                    Ya, Batalkan
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]')?.content;

async function uploadPayment(orderId) {
    const fileInput = document.getElementById('proofFile-' + orderId);
    const noteInput = document.getElementById('proofNote-' + orderId);

    if (!fileInput.files[0]) {
        alert('Pilih file bukti bayar terlebih dahulu!');
        return;
    }

    const fd = new FormData();
    fd.append('proof', fileInput.files[0]);
    fd.append('notes', noteInput.value);

    const res  = await fetch(`/orders/${orderId}/upload-payment`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF },
        body: fd,
    });
    const data = await res.json();

    if (data.success) {
        alert('Bukti bayar berhasil diupload! Menunggu konfirmasi artist.');
        window.location.reload();
    } else {
        alert(data.error || 'Gagal upload.');
    }
}

function clientOrders() {
    return {
        isDark: false,
        cancelModal: false,
        cancelReason: '',
        cancelOrderId: null,
        init() {
            this.isDark = document.documentElement.classList.contains('dark');
            new MutationObserver(() => {
                this.isDark = document.documentElement.classList.contains('dark');
            }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            lucide.createIcons();
        },
        async submitCancel() {
            const orderId = this.cancelOrderId || window._cancelOrderId;
            if (!orderId) { alert('Order ID tidak ditemukan'); return; }

            const res = await fetch(`/orders/${orderId}/cancel`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
                body: JSON.stringify({ reason: this.cancelReason }),
            });
            const data = await res.json();
            if (data.success) {
                this.cancelModal = false;
                window.location.reload();
            } else {
                alert(data.error || 'Gagal membatalkan.');
            }
        }
        
    }
}

function openCancelModal(id) {
    window._cancelOrderId = id;
    const page = Alpine.$data(document.querySelector('[x-data="clientOrders()"]'));
    page.cancelOrderId = id;
    page.cancelReason  = '';
    page.cancelModal   = true;
}
</script>
@endpush