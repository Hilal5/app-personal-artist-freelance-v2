@extends('layouts.app')
@section('title', 'Buat Commission — ArtSpace')

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

    .tier-section {
        border-radius: 14px; padding: 16px; border: 1px solid;
    }
    .dark .tier-section { background: #21212e; border-color: #3a3a50; }
    html:not(.dark) .tier-section { background: #f9fafb; border-color: #e5e7eb; }

    .upload-zone {
        border: 2px dashed; border-radius: 14px; padding: 24px;
        text-align: center; cursor: pointer; transition: all 0.2s;
    }
    .dark .upload-zone { border-color: #3a3a50; }
    html:not(.dark) .upload-zone { border-color: #d1d5db; }
    .upload-zone:hover { border-color: #f97316; }
</style>
@endpush

@section('content')
<div x-data="createPage()" x-init="init()">
    <div class="max-w-3xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('commission.manage') }}"
                class="w-8 h-8 rounded-lg flex items-center justify-center transition-all"
                :class="isDark ? 'text-gray-400 hover:bg-[#2a2a3d]' : 'text-gray-400 hover:bg-gray-100'">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Buat Commission</h1>
                <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Isi detail commission kamu</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs">
            @foreach($errors->all() as $err)<p>• {{ $err }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('commission.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- Info dasar --}}
            <div class="rounded-2xl p-5 border mb-4"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
                <h2 class="font-bold text-sm mb-4" :class="isDark ? 'text-white' : 'text-[#21212e]'">Informasi Dasar</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Judul <span class="text-red-400">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                            placeholder="Contoh: Character Illustration Commission"
                            class="form-input" :class="isDark ? 'placeholder-gray-600' : 'placeholder-gray-400'">
                    </div>

                    <div>
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Kategori <span class="text-red-400">*</span></label>
                        <select name="category" class="form-input" required>
                            <option value="">Pilih kategori</option>
                            @foreach(['Ilustrasi', 'Concept Art', 'Chibi', 'Anime OC', 'Character Design', 'Portrait', 'Fanart', 'Lainnya'] as $cat)
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Status</label>
                        <select name="status" class="form-input">
                            <option value="open" {{ old('status', 'open') === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Estimasi Pengerjaan (hari) <span class="text-red-400">*</span></label>
                        <input type="number" name="estimated_days" value="{{ old('estimated_days', 7) }}" min="1" required class="form-input">
                    </div>

                    <div>
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Max Slot <span class="text-red-400">*</span></label>
                        <input type="number" name="max_slots" value="{{ old('max_slots', 5) }}" min="1" required class="form-input">
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Deskripsi <span class="text-red-400">*</span></label>
                        <textarea name="description" rows="4" required
                            placeholder="Jelaskan tentang commission ini, apa yang kamu tawarkan, aturan, dll..."
                            class="form-input resize-none"
                            :class="isDark ? 'placeholder-gray-600' : 'placeholder-gray-400'">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Tier pricing --}}
            <div class="rounded-2xl p-5 border mb-4"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
                <h2 class="font-bold text-sm mb-4" :class="isDark ? 'text-white' : 'text-[#21212e]'">Harga per Tier</h2>

                <div class="flex flex-col gap-4">
                    @foreach([
                        ['key' => 'basic',    'label' => 'Basic',    'color' => 'text-green-500',  'default' => 'Basic'],
                        ['key' => 'standard', 'label' => 'Standard', 'color' => 'text-orange-500', 'default' => 'Standard'],
                        ['key' => 'premium',  'label' => 'Premium',  'color' => 'text-purple-500', 'default' => 'Premium'],
                    ] as $tier)
                    <div class="tier-section">
                        <p class="font-bold text-sm {{ $tier['color'] }} mb-3">{{ $tier['label'] }}</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Nama Tier</label>
                                <input type="text" name="tier_{{ $tier['key'] }}_name"
                                    value="{{ old('tier_' . $tier['key'] . '_name', $tier['default']) }}"
                                    class="form-input">
                            </div>
                            <div>
                                <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Harga (Rp) <span class="text-red-400">*</span></label>
                                <input type="number" name="tier_{{ $tier['key'] }}_price"
                                    value="{{ old('tier_' . $tier['key'] . '_price') }}"
                                    min="0" required class="form-input" placeholder="50000">
                            </div>
                            <div>
                                <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Diskon (%)</label>
                                <input type="number" name="tier_{{ $tier['key'] }}_discount"
                                    value="{{ old('tier_' . $tier['key'] . '_discount', 0) }}"
                                    min="0" max="100" class="form-input" placeholder="0">
                            </div>
                            <div>
                                <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Deskripsi Tier</label>
                                <input type="text" name="tier_{{ $tier['key'] }}_desc"
                                    value="{{ old('tier_' . $tier['key'] . '_desc') }}"
                                    class="form-input" placeholder="Apa yang didapat di tier ini...">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Upload media --}}
            <div class="rounded-2xl p-5 border mb-6"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
                <h2 class="font-bold text-sm mb-4" :class="isDark ? 'text-white' : 'text-[#21212e]'">Contoh Karya</h2>

                <div class="upload-zone" :class="isDark ? 'bg-[#21212e]' : 'bg-gray-50'"
                    @click="$refs.mediaInput.click()">
                    <i data-lucide="upload-cloud" class="w-8 h-8 text-orange-500 mx-auto mb-2"></i>
                    <p class="text-xs font-bold" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Upload contoh karya</p>
                    <p class="text-xs mt-1" :class="isDark ? 'text-gray-500' : 'text-gray-400'">JPG, PNG, GIF, MP4, MOV (max 50MB)</p>
                </div>
                <input type="file" name="media[]" multiple
                    accept="image/*,video/*"
                    class="hidden" x-ref="mediaInput"
                    @change="onMedia($event)">

                <div class="flex flex-wrap gap-2 mt-3" x-show="mediaPreviews.length > 0">
                    <template x-for="(p, i) in mediaPreviews" :key="i">
                        <div class="relative w-20 h-20 rounded-xl overflow-hidden border"
                            :class="isDark ? 'border-[#3a3a50]' : 'border-gray-200'">
                            <template x-if="p.type.startsWith('image')">
                                <img :src="p.src" class="w-full h-full object-cover">
                            </template>
                            <template x-if="p.type.startsWith('video')">
                                <div class="w-full h-full bg-gray-800 flex items-center justify-center">
                                    <i data-lucide="film" class="w-6 h-6 text-white"></i>
                                </div>
                            </template>
                            <div class="absolute bottom-0 left-0 right-0 text-center px-1 pb-0.5"
                                style="font-size:8px;background:rgba(0,0,0,0.5);color:white;"
                                x-text="p.name.length > 10 ? p.name.substring(0,10)+'...' : p.name"></div>
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
                    Simpan Commission
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function createPage() {
    return {
        isDark: false,
        mediaPreviews: [],
        onMedia(e) {
            Array.from(e.target.files).forEach(f => {
                const r = new FileReader();
                r.onload = ev => this.mediaPreviews.push({ src: ev.target.result, type: f.type, name: f.name });
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