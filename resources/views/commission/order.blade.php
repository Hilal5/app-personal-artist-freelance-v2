@extends('layouts.app')
@section('title', 'Order Commission — ArtSpace')

@push('styles')
<style>
    .form-input {
        width: 100%; padding: 10px 16px; border-radius: 12px;
        font-size: 0.875rem; outline: none; transition: all 0.2s;
        border: 1px solid transparent;
    }
    .dark .form-input { background: #21212e; border-color: #3a3a50; color: white; }
    html:not(.dark) .form-input { background: #f9fafb; border-color: #e5e7eb; color: #21212e; }
    .dark .form-input:focus { border-color: #f97316; }
    html:not(.dark) .form-input:focus { border-color: #21212e; }

    .form-label { font-size: 0.75rem; font-weight: 700; margin-bottom: 6px; display: block; }

    .upload-zone {
        border: 2px dashed; border-radius: 14px;
        padding: 24px; text-align: center;
        cursor: pointer; transition: all 0.2s;
    }
    .dark .upload-zone { border-color: #3a3a50; }
    html:not(.dark) .upload-zone { border-color: #d1d5db; }
    .upload-zone:hover, .upload-zone.drag { border-color: #f97316 !important; }

    .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; }
    .dark .summary-row { border-bottom: 1px solid #3a3a50; }
    html:not(.dark) .summary-row { border-bottom: 1px solid #f3f4f6; }
</style>
@endpush

@section('content')
@php
    $tier       = request('tier', 'basic');
    $payment    = request('payment', '');
    $tierLabel  = ucfirst($tier);
    $priceKey   = 'tier_' . $tier . '_price';
    $discKey    = 'tier_' . $tier . '_discount';
    $descKey    = 'tier_' . $tier . '_desc';
    $nameKey    = 'tier_' . $tier . '_name';
    $price      = $commission->$priceKey;
    $disc       = $commission->$discKey;
    $final      = $price - ($price * $disc / 100);

    $paymentLabels = [
        'bri'       => 'BRI',
        'seabank'   => 'SeaBank',
        'bank_jago' => 'Bank Jago',
        'dana'      => 'DANA',
        'gopay'     => 'GoPay',
        'shopeepay' => 'ShopeePay',
    ];
@endphp

<div x-data="orderPage()" x-init="init()">
    <div class="max-w-3xl mx-auto">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-xs mb-6" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
            <a href="{{ route('commission.index') }}" class="hover:text-orange-500">Commission</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <a href="{{ route('commission.show', $commission->id) }}" class="hover:text-orange-500">{{ $commission->title }}</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span :class="isDark ? 'text-white' : 'text-[#21212e]'">Order</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- FORM --}}
            <div class="lg:col-span-2">
                <form method="POST" action="{{ route('commission.order.store') }}" enctype="multipart/form-data" id="orderForm">
                    @csrf
                    <input type="hidden" name="commission_id" value="{{ $commission->id }}">
                    <input type="hidden" name="tier" value="{{ $tier }}">
                    <input type="hidden" name="payment_method" value="{{ $payment }}" id="paymentInput">

                    <div class="rounded-2xl p-5 border mb-4"
                        :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
                        <h2 class="font-bold text-sm mb-5" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                            Detail Order
                        </h2>

                        {{-- Deskripsi request --}}
                        <div class="mb-4">
                            <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">
                                Deskripsi Request <span class="text-red-400">*</span>
                            </label>
                            <textarea name="description" rows="4" required
                                placeholder="Jelaskan apa yang kamu inginkan, pose, ekspresi, warna, dll..."
                                class="form-input resize-none"
                                :class="isDark ? 'placeholder-gray-600' : 'placeholder-gray-400'">{{ old('description') }}</textarea>
                            @error('description')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Catatan tambahan --}}
                        <div class="mb-4">
                            <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">
                                Catatan Tambahan
                            </label>
                            <textarea name="notes" rows="2"
                                placeholder="Catatan khusus untuk artist (opsional)..."
                                class="form-input resize-none"
                                :class="isDark ? 'placeholder-gray-600' : 'placeholder-gray-400'">{{ old('notes') }}</textarea>
                        </div>

                        {{-- Deadline --}}
                        <div class="mb-4">
                            <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">
                                Deadline yang Diinginkan
                            </label>
                            <input type="date" name="client_deadline"
                                value="{{ old('client_deadline') }}"
                                min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                class="form-input">
                        </div>

                        {{-- Upload referensi --}}
                        <div>
                            <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">
                                Upload Referensi Gambar
                            </label>
                            <div class="upload-zone" :class="isDark ? 'bg-[#21212e]' : 'bg-gray-50'"
                                @click="$refs.refInput.click()"
                                @dragover.prevent="dragging = true"
                                @dragleave="dragging = false"
                                @drop.prevent="onDrop($event)"
                                :class="dragging ? 'drag' : ''">
                                <i data-lucide="upload-cloud" class="w-8 h-8 text-orange-500 mx-auto mb-2"></i>
                                <p class="text-xs font-bold" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">
                                    Klik atau drag & drop
                                </p>
                                <p class="text-xs mt-1" :class="isDark ? 'text-gray-500' : 'text-gray-400'">
                                    JPG, PNG, GIF, MP4 (max 20MB per file)
                                </p>
                            </div>
                            <input type="file" name="references[]" multiple
                                accept="image/*,video/*"
                                class="hidden" x-ref="refInput"
                                @change="onFiles($event)">

                            {{-- Preview --}}
                            <div class="flex flex-wrap gap-2 mt-3" x-show="previews.length > 0">
                                <template x-for="(p, i) in previews" :key="i">
                                    <div class="relative w-16 h-16 rounded-xl overflow-hidden border"
                                        :class="isDark ? 'border-[#3a3a50]' : 'border-gray-200'">
                                        <template x-if="p.type.startsWith('image')">
                                            <img :src="p.src" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="p.type.startsWith('video')">
                                            <div class="w-full h-full bg-gray-800 flex items-center justify-center">
                                                <i data-lucide="film" class="w-5 h-5 text-white"></i>
                                            </div>
                                        </template>
                                        <button type="button" @click="removePreview(i)"
                                            class="absolute top-0.5 right-0.5 w-4 h-4 bg-red-500 text-white rounded-full text-xs flex items-center justify-center">×</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Errors --}}
                    @if($errors->any())
                    <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs">
                        @foreach($errors->all() as $err)
                        <p>• {{ $err }}</p>
                        @endforeach
                    </div>
                    @endif

                    <button type="submit"
                        class="w-full py-3 rounded-xl bg-orange-500 text-white font-bold text-sm hover:bg-orange-600 transition-all">
                        Konfirmasi Order
                    </button>
                </form>
            </div>

            {{-- SUMMARY --}}
            <div>
                <div class="rounded-2xl p-5 border sticky top-24"
                    :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">

                    <h3 class="font-bold text-sm mb-4" :class="isDark ? 'text-white' : 'text-[#21212e]'">Ringkasan Order</h3>

                    <div class="summary-row">
                        <span class="text-xs" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Commission</span>
                        <span class="text-xs font-bold text-right max-w-[140px] truncate"
                            :class="isDark ? 'text-white' : 'text-[#21212e]'">{{ $commission->title }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-xs" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Paket</span>
                        <span class="text-xs font-bold" style="color:#f97316;">{{ $commission->$nameKey }} ({{ ucfirst($tier) }})</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-xs" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Estimasi</span>
                        <span class="text-xs font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">{{ $commission->estimated_days }} hari</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-xs" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Pembayaran</span>
                        <span class="text-xs font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                            {{ $paymentLabels[$payment] ?? '-' }}
                        </span>
                    </div>
                    @if($disc > 0)
                    <div class="summary-row">
                        <span class="text-xs" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Harga normal</span>
                        <span class="text-xs line-through" :class="isDark ? 'text-gray-500' : 'text-gray-400'">Rp{{ number_format($price, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="text-xs text-red-400">Diskon {{ $disc }}%</span>
                        <span class="text-xs text-red-400 font-bold">-Rp{{ number_format($price * $disc / 100, 0, ',', '.') }}</span>
                    </div>
                    @endif

                    <div class="pt-3 mt-1">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Total</span>
                            <span class="text-lg font-bold text-orange-500">Rp{{ number_format($final, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="mt-4 p-3 rounded-xl text-xs" style="background:rgba(249,115,22,0.08);color:#f97316;">
                        <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
                        Pembayaran dilakukan setelah kamu deal dengan artist via chat. Artist akan memberikan info rekening/akun pembayaran.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function orderPage() {
    return {
        isDark: false,
        dragging: false,
        previews: [],
        onFiles(e) {
            Array.from(e.target.files).forEach(f => {
                const r = new FileReader();
                r.onload = ev => this.previews.push({ src: ev.target.result, type: f.type });
                r.readAsDataURL(f);
            });
            this.$nextTick(() => lucide.createIcons());
        },
        onDrop(e) {
            this.dragging = false;
            const dt    = e.dataTransfer;
            const input = this.$refs.refInput;
            input.files = dt.files;
            this.onFiles({ target: input });
        },
        removePreview(i) {
            this.previews.splice(i, 1);
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