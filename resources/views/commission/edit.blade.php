@extends('layouts.app')
@section('title', 'Edit Commission — ArtSpace')

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
    .tier-section { border-radius: 14px; padding: 16px; border: 1px solid; }
    .dark .tier-section { background: #21212e; border-color: #3a3a50; }
    html:not(.dark) .tier-section { background: #f9fafb; border-color: #e5e7eb; }
</style>
@endpush

@section('content')
<div x-data="editPage()" x-init="init()">
    <div class="max-w-3xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('commission.manage') }}"
                class="w-8 h-8 rounded-lg flex items-center justify-center transition-all"
                :class="isDark ? 'text-gray-400 hover:bg-[#2a2a3d]' : 'text-gray-400 hover:bg-gray-100'">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Edit Commission</h1>
                <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">{{ $commission->title }}</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs">
            @foreach($errors->all() as $err)<p>• {{ $err }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('commission.update', $commission->id) }}" enctype="multipart/form-data">
            @csrf

            {{-- Info dasar --}}
            <div class="rounded-2xl p-5 border mb-4"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
                <h2 class="font-bold text-sm mb-4" :class="isDark ? 'text-white' : 'text-[#21212e]'">Informasi Dasar</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Judul</label>
                        <input type="text" name="title" value="{{ old('title', $commission->title) }}" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Kategori</label>
                        <select name="category" class="form-input">
                            @foreach(['Ilustrasi', 'Concept Art', 'Chibi', 'Anime OC', 'Character Design', 'Portrait', 'Fanart', 'Lainnya'] as $cat)
                            <option value="{{ $cat }}" {{ old('category', $commission->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Status</label>
                        <select name="status" class="form-input">
                            <option value="open" {{ old('status', $commission->status) === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="closed" {{ old('status', $commission->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Estimasi (hari)</label>
                        <input type="number" name="estimated_days" value="{{ old('estimated_days', $commission->estimated_days) }}" min="1" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Max Slot</label>
                        <input type="number" name="max_slots" value="{{ old('max_slots', $commission->max_slots) }}" min="1" required class="form-input">
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Deskripsi</label>
                        <textarea name="description" rows="4" required class="form-input resize-none">{{ old('description', $commission->description) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Tier pricing --}}
            <div class="rounded-2xl p-5 border mb-4"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
                <h2 class="font-bold text-sm mb-4" :class="isDark ? 'text-white' : 'text-[#21212e]'">Harga per Tier</h2>
                <div class="flex flex-col gap-4">
                    @foreach([
                        ['key' => 'basic',    'label' => 'Basic',    'color' => 'text-green-500'],
                        ['key' => 'standard', 'label' => 'Standard', 'color' => 'text-orange-500'],
                        ['key' => 'premium',  'label' => 'Premium',  'color' => 'text-purple-500'],
                    ] as $tier)
                    <div class="tier-section">
                        <p class="font-bold text-sm {{ $tier['color'] }} mb-3">{{ $tier['label'] }}</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Nama Tier</label>
                                <input type="text" name="tier_{{ $tier['key'] }}_name"
                                    value="{{ old('tier_' . $tier['key'] . '_name', $commission->{'tier_' . $tier['key'] . '_name'}) }}"
                                    class="form-input">
                            </div>
                            <div>
                                <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Harga (Rp)</label>
                                <input type="number" name="tier_{{ $tier['key'] }}_price"
                                    value="{{ old('tier_' . $tier['key'] . '_price', $commission->{'tier_' . $tier['key'] . '_price'}) }}"
                                    min="0" required class="form-input">
                            </div>
                            <div>
                                <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Diskon (%)</label>
                                <input type="number" name="tier_{{ $tier['key'] }}_discount"
                                    value="{{ old('tier_' . $tier['key'] . '_discount', $commission->{'tier_' . $tier['key'] . '_discount'}) }}"
                                    min="0" max="100" class="form-input">
                            </div>
                            <div>
                                <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Deskripsi Tier</label>
                                <input type="text" name="tier_{{ $tier['key'] }}_desc"
                                    value="{{ old('tier_' . $tier['key'] . '_desc', $commission->{'tier_' . $tier['key'] . '_desc'}) }}"
                                    class="form-input">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Media existing --}}
            @if($commission->media->isNotEmpty())
            <div class="rounded-2xl p-5 border mb-4"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
                <h2 class="font-bold text-sm mb-4" :class="isDark ? 'text-white' : 'text-[#21212e]'">Media Saat Ini</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($commission->media as $media)
                    <div class="relative group w-20 h-20 rounded-xl overflow-hidden border"
                        :class="isDark ? 'border-[#3a3a50]' : 'border-gray-200'">
                        @if($media->file_type === 'video')
                        <div class="w-full h-full bg-gray-800 flex items-center justify-center">
                            <i data-lucide="film" class="w-6 h-6 text-white"></i>
                        </div>
                        @else
                        <img src="{{ Storage::url($media->file_path) }}" class="w-full h-full object-cover">
                        @endif
                        <label class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                            <input type="checkbox" name="delete_media[]" value="{{ $media->id }}" class="hidden" x-ref="cb{{ $media->id }}"
                                @change="toggleDelete($event, {{ $media->id }})">
                            <span class="text-white text-xs font-bold" id="del-label-{{ $media->id }}">Hapus?</span>
                        </label>
                        <div class="absolute top-1 right-1 w-4 h-4 bg-red-500 rounded-full hidden items-center justify-center" id="del-mark-{{ $media->id }}">
                            <i data-lucide="x" style="width:10px;height:10px;color:white;"></i>
                        </div>
                    </div>
                    @endforeach
                </div>
                <p class="text-xs mt-2" :class="isDark ? 'text-gray-500' : 'text-gray-400'">Hover gambar lalu klik untuk menandai hapus</p>
            </div>
            @endif

            {{-- Upload media baru --}}
            <div class="rounded-2xl p-5 border mb-6"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
                <h2 class="font-bold text-sm mb-4" :class="isDark ? 'text-white' : 'text-[#21212e]'">Tambah Media Baru</h2>
                <div class="border-2 dashed rounded-xl p-6 text-center cursor-pointer transition-all"
                    :class="isDark ? 'border-[#3a3a50] hover:border-orange-500' : 'border-gray-200 hover:border-orange-500'"
                    @click="$refs.newMedia.click()">
                    <i data-lucide="upload-cloud" class="w-7 h-7 text-orange-500 mx-auto mb-2"></i>
                    <p class="text-xs" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Klik untuk upload media baru</p>
                </div>
                <input type="file" name="media[]" multiple accept="image/*,video/*" class="hidden" x-ref="newMedia"
                    @change="onNewMedia($event)">
                <div class="flex flex-wrap gap-2 mt-3" x-show="newPreviews.length > 0">
                    <template x-for="(p, i) in newPreviews" :key="i">
                        <div class="w-20 h-20 rounded-xl overflow-hidden border"
                            :class="isDark ? 'border-[#3a3a50]' : 'border-gray-200'">
                            <template x-if="p.type.startsWith('image')">
                                <img :src="p.src" class="w-full h-full object-cover">
                            </template>
                            <template x-if="p.type.startsWith('video')">
                                <div class="w-full h-full bg-gray-800 flex items-center justify-center">
                                    <i data-lucide="film" class="w-5 h-5 text-white"></i>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('commission.manage') }}"
                    class="flex-1 py-3 rounded-xl text-sm font-bold text-center transition-all"
                    :class="isDark ? 'bg-[#2a2a3d] text-gray-300 hover:bg-[#3a3a50]' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                    Batal
                </a>
                <button type="submit"
                    class="flex-1 py-3 rounded-xl bg-orange-500 text-white font-bold text-sm hover:bg-orange-600 transition-all">
                    Update Commission
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editPage() {
    return {
        isDark: false,
        newPreviews: [],
        markedDelete: new Set(),
        toggleDelete(e, id) {
            const mark = document.getElementById('del-mark-' + id);
            if (e.target.checked) {
                this.markedDelete.add(id);
                if (mark) { mark.style.display = 'flex'; }
            } else {
                this.markedDelete.delete(id);
                if (mark) { mark.style.display = 'none'; }
            }
        },
        onNewMedia(e) {
            Array.from(e.target.files).forEach(f => {
                const r = new FileReader();
                r.onload = ev => this.newPreviews.push({ src: ev.target.result, type: f.type });
                r.readAsDataURL(f);
            });
            this.$nextTick(() => lucide.createIcons());
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