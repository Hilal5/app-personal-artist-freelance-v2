@extends('layouts.app')
@section('title', 'Tulis Review — ArtSpace')

@push('styles')
<style>
    .star-btn { cursor: pointer; transition: all 0.15s; }
    .form-input {
        width: 100%; padding: 10px 16px; border-radius: 12px;
        font-size: 0.875rem; outline: none; border: 1px solid; transition: all 0.2s;
    }
    .dark .form-input { background: #21212e; border-color: #3a3a50; color: white; }
    html:not(.dark) .form-input { background: #f9fafb; border-color: #e5e7eb; color: #21212e; }
    .dark .form-input:focus { border-color: #f97316; }
    html:not(.dark) .form-input:focus { border-color: #21212e; }

    .tag-chip {
        padding: 5px 12px; border-radius: 99px;
        font-size: 0.72rem; font-weight: 700;
        cursor: pointer; transition: all 0.15s;
        border: 2px solid transparent; user-select: none;
    }
    .tag-good-opt { background: rgba(34,197,94,0.08); color: #22c55e; border-color: rgba(34,197,94,0.2); }
    .tag-good-opt:hover, .tag-good-opt.selected { background: rgba(34,197,94,0.2); border-color: #22c55e; }
    .tag-bad-opt  { background: rgba(239,68,68,0.08); color: #ef4444; border-color: rgba(239,68,68,0.2); }
    .tag-bad-opt:hover, .tag-bad-opt.selected  { background: rgba(239,68,68,0.2);  border-color: #ef4444; }
</style>
@endpush

@section('content')
@php
    $goodTags = [
        'Komunikasi bagus', 'Pengerjaan cepat', 'Hasil memuaskan',
        'Sangat profesional', 'Ramah & responsif', 'Revisi ditangani dengan baik',
        'Sesuai ekspektasi', 'Recommended!', 'Detail terjaga', 'Harga worth it',
    ];
    $badTags = [
        'Komunikasi kurang', 'Pengerjaan lambat', 'Kurang sesuai request',
        'Revisi tidak ditangani', 'Kurang responsif', 'Tidak sesuai ekspektasi',
        'Harga terlalu mahal',
    ];
@endphp

<div x-data="reviewForm()" x-init="init()">
    <div class="max-w-xl mx-auto">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-xs mb-6" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
            <a href="{{ route('orders.client') }}" class="hover:text-orange-500">My Orders</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span :class="isDark ? 'text-white' : 'text-[#21212e]'">Tulis Review</span>
        </div>

        <div class="rounded-2xl p-6 border"
            :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">

            {{-- Order info --}}
            <div class="p-3 rounded-xl mb-6" :class="isDark ? 'bg-[#21212e]' : 'bg-gray-50'">
                <p class="text-xs" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Commission</p>
                <p class="font-bold text-sm" :class="isDark ? 'text-white' : 'text-[#21212e]'">{{ $order->commission_title }}</p>
                <p class="text-xs text-orange-500 font-bold mt-0.5">{{ $order->order_number }}</p>
            </div>

            <form method="POST" action="{{ route('reviews.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="order_id" value="{{ $order->id }}">
                <input type="hidden" name="rating" :value="rating">

                {{-- Rating bintang --}}
                <div class="mb-6 text-center">
                    <p class="text-sm font-bold mb-3" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                        Bagaimana pengalamanmu?
                    </p>
                    <div class="flex justify-center gap-2">
                        @for($i = 1; $i <= 5; $i++)
                        <button type="button" @click="setRating({{ $i }})"
                            class="star-btn">
                            <i data-lucide="star"
                                :style="rating >= {{ $i }} ? 'width:36px;height:36px;color:#f97316;fill:#f97316;' : 'width:36px;height:36px;color:#4b5563;'"
                                @mouseenter="hoverRating = {{ $i }}"
                                @mouseleave="hoverRating = 0">
                            </i>
                        </button>
                        @endfor
                    </div>
                    <p class="text-sm font-bold mt-2 h-5"
                        :class="isDark ? 'text-white' : 'text-[#21212e]'"
                        x-text="ratingLabel">
                    </p>
                </div>

                @if($errors->has('rating'))
                <p class="text-red-400 text-xs mb-4 text-center">{{ $errors->first('rating') }}</p>
                @endif

                {{-- Quick tags (muncul setelah pilih rating) --}}
                <div x-show="rating > 0" class="mb-5">
                    <p class="text-xs font-bold mb-3" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">
                        Pilih yang sesuai pengalamanmu:
                    </p>

                    {{-- Good tags (muncul kalau rating >= 3) --}}
                    <div x-show="rating >= 3" class="flex flex-wrap gap-2 mb-2">
                        @foreach($goodTags as $tag)
                        <span class="tag-chip tag-good-opt"
                            :class="selectedTags.includes('{{ $tag }}') ? 'selected' : ''"
                            @click="toggleTag('{{ $tag }}')">
                            👍 {{ $tag }}
                        </span>
                        @endforeach
                    </div>

                    {{-- Bad tags (muncul kalau rating <= 2) --}}
                    <div x-show="rating <= 2" class="flex flex-wrap gap-2 mb-2">
                        @foreach($badTags as $tag)
                        <span class="tag-chip tag-bad-opt"
                            :class="selectedTags.includes('{{ $tag }}') ? 'selected' : ''"
                            @click="toggleTag('{{ $tag }}')">
                            👎 {{ $tag }}
                        </span>
                        @endforeach
                    </div>

                    {{-- Hidden inputs untuk tags --}}
                    <template x-for="tag in selectedTags" :key="tag">
                        <input type="hidden" name="quick_tags[]" :value="tag">
                    </template>
                </div>

                {{-- Komentar --}}
                <div class="mb-5" x-show="rating > 0">
                    <label class="text-xs font-bold mb-2 block" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">
                        Ceritakan pengalamanmu
                    </label>
                    <textarea name="comment" rows="4"
                        placeholder="Tuliskan detail pengalamanmu dengan artist ini..."
                        class="form-input resize-none"
                        :class="isDark ? 'placeholder-gray-600' : 'placeholder-gray-400'">{{ old('comment') }}</textarea>
                </div>

                {{-- Upload foto hasil karya --}}
                <div class="mb-6" x-show="rating > 0">
                    <label class="text-xs font-bold mb-2 block" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">
                        Upload Foto Hasil Karya (opsional)
                    </label>
                    <div class="border-2 dashed rounded-xl p-5 text-center cursor-pointer transition-all"
                        :class="isDark ? 'border-[#3a3a50] hover:border-orange-500' : 'border-gray-200 hover:border-orange-500'"
                        @click="$refs.imgInput.click()">
                        <template x-if="!previewImg">
                            <div>
                                <i data-lucide="image-plus" class="w-7 h-7 text-orange-500 mx-auto mb-1"></i>
                                <p class="text-xs" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                                    Klik untuk upload (JPG, PNG, max 10MB)
                                </p>
                            </div>
                        </template>
                        <template x-if="previewImg">
                            <img :src="previewImg" class="max-h-40 mx-auto rounded-xl object-contain">
                        </template>
                    </div>
                    <input type="file" name="result_image" accept="image/*"
                        class="hidden" x-ref="imgInput"
                        @change="onImage($event)">
                </div>

                {{-- Submit --}}
                <div x-show="rating > 0" class="flex gap-3">
                    <a href="{{ route('orders.client') }}"
                        class="flex-1 py-3 rounded-xl text-sm font-bold text-center transition-all"
                        :class="isDark ? 'bg-[#21212e] text-gray-300' : 'bg-gray-100 text-gray-600'">
                        Batal
                    </a>
                    <button type="submit"
                        class="flex-1 py-3 rounded-xl bg-orange-500 text-white font-bold text-sm hover:bg-orange-600 transition-all">
                        Kirim Review
                    </button>
                </div>

                <p x-show="rating === 0" class="text-center text-xs"
                    :class="isDark ? 'text-gray-500' : 'text-gray-400'">
                    Pilih rating bintang untuk mulai menulis review
                </p>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function reviewForm() {
    return {
        isDark: false,
        rating: 0,
        hoverRating: 0,
        selectedTags: [],
        previewImg: null,
        get ratingLabel() {
            const labels = { 1: '😞 Sangat Kecewa', 2: '😕 Kurang Memuaskan', 3: '😊 Cukup Baik', 4: '😄 Bagus!', 5: '🤩 Luar Biasa!' };
            return labels[this.rating] || '';
        },
        setRating(val) {
            this.rating = val;
            this.selectedTags = []; // reset tags saat ganti rating
            this.$nextTick(() => lucide.createIcons());
        },
        toggleTag(tag) {
            if (this.selectedTags.includes(tag)) {
                this.selectedTags = this.selectedTags.filter(t => t !== tag);
            } else {
                this.selectedTags.push(tag);
            }
        },
        onImage(e) {
            const file = e.target.files[0];
            if (!file) return;
            const r = new FileReader();
            r.onload = ev => this.previewImg = ev.target.result;
            r.readAsDataURL(file);
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