@extends('layouts.app')
@section('title', 'Edit Portfolio — ArtSpace')

@push('styles')
<style>
    .form-input { width:100%; padding:10px 16px; border-radius:12px; font-size:0.875rem; outline:none; transition:all 0.2s; border:1px solid; }
    .dark .form-input { background:#21212e; border-color:#3a3a50; color:white; }
    html:not(.dark) .form-input { background:#f9fafb; border-color:#e5e7eb; color:#21212e; }
    .dark .form-input:focus { border-color:#f97316; }
    html:not(.dark) .form-input:focus { border-color:#21212e; }
    .form-label { font-size:0.75rem; font-weight:700; margin-bottom:6px; display:block; }
</style>
@endpush

@section('content')
<div x-data="editPortfolio()" x-init="init()">
    <div class="max-w-3xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('portfolio.manage') }}"
                class="w-8 h-8 rounded-lg flex items-center justify-center transition-all"
                :class="isDark ? 'text-gray-400 hover:bg-[#2a2a3d]' : 'text-gray-400 hover:bg-gray-100'">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Edit Portfolio</h1>
                <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">{{ $portfolio->title }}</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs">
            @foreach($errors->all() as $err)<p>• {{ $err }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('portfolio.update', $portfolio->id) }}" enctype="multipart/form-data">
            @csrf

            <div class="rounded-2xl p-5 border mb-4"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
                <h2 class="font-bold text-sm mb-4" :class="isDark ? 'text-white' : 'text-[#21212e]'">Informasi Karya</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Judul</label>
                        <input type="text" name="title" value="{{ old('title', $portfolio->title) }}" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Kategori</label>
                        <select name="category" class="form-input">
                            @foreach(['Ilustrasi','Concept Art','Chibi','Anime OC','Character Design','Portrait','Fanart','Lainnya'] as $cat)
                            <option value="{{ $cat }}" {{ old('category', $portfolio->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Software</label>
                        <select name="software" class="form-input">
                            <option value="">Pilih software</option>
                            @foreach(['Procreate','Photoshop','Clip Studio Paint','Ibis Paint','Krita','Medibang','Adobe Illustrator','Lainnya'] as $sw)
                            <option value="{{ $sw }}" {{ old('software', $portfolio->software) === $sw ? 'selected' : '' }}>{{ $sw }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Client Name</label>
                        <input type="text" name="client_name" value="{{ old('client_name', $portfolio->client_name) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Tanggal Dibuat</label>
                        <input type="date" name="created_date" value="{{ old('created_date', $portfolio->created_date) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Status</label>
                        <select name="status" class="form-input">
                            <option value="published" {{ old('status', $portfolio->status) === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="draft" {{ old('status', $portfolio->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Tags</label>
                        <div x-data="tagInput('{{ old('tags', $portfolio->tags->implode(', ')) }}')">

                            <div class="flex flex-wrap gap-1.5 p-2 rounded-xl border min-h-[44px] cursor-text transition-all"
                                :class="[
                                    isDark ? 'bg-[#21212e] border-[#3a3a50]' : 'bg-gray-50 border-gray-200',
                                    focused ? (isDark ? 'border-orange-500' : 'border-[#21212e]') : ''
                                ]"
                                @click="$refs.tagInput.focus()">

                                <template x-for="(tag, i) in tags" :key="i">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold"
                                        style="background:rgba(249,115,22,0.15);color:#f97316;">
                                        <span x-text="tag"></span>
                                        <button type="button" @click.stop="removeTag(i)"
                                            class="w-3.5 h-3.5 rounded-full flex items-center justify-center hover:bg-orange-500 hover:text-white transition-all"
                                            style="color:#f97316;">✕</button>
                                    </span>
                                </template>

                                <input type="text"
                                    x-ref="tagInput"
                                    x-model="inputVal"
                                    @keydown.enter.prevent="addTag()"
                                    @keydown.comma.prevent="addTag()"
                                    @keydown.backspace="inputVal === '' ? removeTag(tags.length - 1) : null"
                                    @focus="focused = true"
                                    @blur="focused = false; addTag()"
                                    placeholder="Ketik lalu Enter..."
                                    class="flex-1 bg-transparent outline-none text-xs py-1"
                                    :class="isDark ? 'text-white placeholder-gray-600' : 'text-[#21212e] placeholder-gray-400'"
                                    style="min-width:80px;">
                            </div>

                            <div class="flex flex-wrap gap-1.5 mt-2" x-show="suggestions.length > 0">
                                <span class="text-xs" :class="isDark ? 'text-gray-500' : 'text-gray-400'">Saran:</span>
                                <template x-for="s in suggestions" :key="s">
                                    <button type="button"
                                        @click="addSuggestion(s)"
                                        x-show="!tags.includes(s)"
                                        class="text-xs px-2 py-0.5 rounded-full border transition-all"
                                        :class="isDark
                                            ? 'border-[#3a3a50] text-gray-400 hover:border-orange-500 hover:text-orange-500'
                                            : 'border-gray-200 text-gray-400 hover:border-orange-500 hover:text-orange-500'">
                                        + <span x-text="s"></span>
                                    </button>
                                </template>
                            </div>

                            <input type="hidden" name="tags" :value="tags.join(', ')">
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Deskripsi</label>
                        <textarea name="description" rows="3" class="form-input resize-none">{{ old('description', $portfolio->description) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Media existing --}}
            @if($portfolio->media->isNotEmpty())
            <div class="rounded-2xl p-5 border mb-4"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
                <h2 class="font-bold text-sm mb-1" :class="isDark ? 'text-white' : 'text-[#21212e]'">Media Saat Ini</h2>
                <p class="text-xs mb-4" :class="isDark ? 'text-gray-500' : 'text-gray-400'">
                    Klik untuk set cover · Hover & centang untuk hapus
                </p>
                <div class="grid grid-cols-4 gap-2">
                    @foreach($portfolio->media as $m)
                    <div class="relative group rounded-xl overflow-hidden" style="aspect-ratio:1;"
                        :class="coverMediaId === {{ $m->id }} ? 'ring-2 ring-orange-500' : ''">
                        @if($m->file_type === 'video')
                        <div class="w-full h-full bg-gray-800 flex items-center justify-center cursor-pointer"
                            @click="coverMediaId = {{ $m->id }}">
                            <i data-lucide="film" class="w-6 h-6 text-white"></i>
                        </div>
                        @else
                        <img src="{{ Storage::url($m->file_path) }}"
                            class="w-full h-full object-cover cursor-pointer"
                            @click="coverMediaId = {{ $m->id }}">
                        @endif

                        <div x-show="coverMediaId === {{ $m->id }}"
                            class="absolute top-1 left-1 bg-orange-500 text-white rounded-full px-1.5 py-0.5"
                            style="font-size:0.6rem;font-weight:800;">COVER</div>

                        {{-- Hapus checkbox --}}
                        <label class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                            <input type="checkbox" name="delete_media[]" value="{{ $m->id }}" class="hidden"
                                @change="toggleDeleteMedia($event, {{ $m->id }})">
                            <div class="w-5 h-5 rounded-full bg-red-500 flex items-center justify-center"
                                :class="deleteMedia.includes({{ $m->id }}) ? 'opacity-100' : 'opacity-70'">
                                <i data-lucide="x" style="width:10px;height:10px;color:white;"></i>
                            </div>
                        </label>

                        <div x-show="deleteMedia.includes({{ $m->id }})"
                            class="absolute inset-0 bg-red-500/30 flex items-center justify-center">
                            <span class="text-white text-xs font-bold">Hapus</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                <input type="hidden" name="cover_media_id" :value="coverMediaId">
            </div>
            @endif

            {{-- Upload baru --}}
            <div class="rounded-2xl p-5 border mb-6"
                :class="isDark ? 'bg-[#2a2a3d] border-[#3a3a50]' : 'bg-white border-gray-200'">
                <h2 class="font-bold text-sm mb-4" :class="isDark ? 'text-white' : 'text-[#21212e]'">Tambah Media Baru</h2>
                <div class="border-2 dashed rounded-xl p-6 text-center cursor-pointer transition-all"
                    :class="isDark ? 'border-[#3a3a50] hover:border-orange-500' : 'border-gray-200 hover:border-orange-500'"
                    @click="$refs.newMedia.click()">
                    <i data-lucide="upload-cloud" class="w-7 h-7 text-orange-500 mx-auto mb-2"></i>
                    <p class="text-xs" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Upload media baru</p>
                </div>
                <input type="file" name="media[]" multiple accept="image/*,video/*" class="hidden" x-ref="newMedia"
                    @change="onNewMedia($event)">
                <div class="grid grid-cols-4 gap-2 mt-3" x-show="newPreviews.length > 0">
                    <template x-for="(p, i) in newPreviews" :key="i">
                        <div class="rounded-xl overflow-hidden" style="aspect-ratio:1;">
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
                <a href="{{ route('portfolio.manage') }}"
                    class="flex-1 py-3 rounded-xl text-sm font-bold text-center"
                    :class="isDark ? 'bg-[#2a2a3d] text-gray-300' : 'bg-gray-100 text-gray-600'">Batal</a>
                <button type="submit"
                    class="flex-1 py-3 rounded-xl bg-orange-500 text-white font-bold text-sm hover:bg-orange-600 transition-all">
                    Update Portfolio
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editPortfolio() {
    return {
        isDark: false,
        newPreviews: [],
        deleteMedia: [],
        coverMediaId: {{ $portfolio->media->where('is_cover', true)->first()?->id ?? $portfolio->media->first()?->id ?? 'null' }},
        toggleDeleteMedia(e, id) {
            if (e.target.checked) {
                this.deleteMedia.push(id);
            } else {
                this.deleteMedia = this.deleteMedia.filter(m => m !== id);
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

function tagInput(initial) {
    return {
        tags: [],
        inputVal: '',
        focused: false,
        suggestions: ['anime', 'oc', 'fanart', 'illustration', 'character', 'fantasy',
                      'portrait', 'chibi', 'semi-realis', 'concept art', 'digital art',
                      'procreate', 'photoshop', 'clip studio', 'original', 'commission'],

        init() {
            if (initial && initial.trim() !== '') {
                this.tags = initial.split(',')
                    .map(t => t.trim())
                    .filter(t => t !== '');
            }
        },

        addTag() {
            const val = this.inputVal.trim().replace(/,$/, '').toLowerCase();
            if (val && !this.tags.includes(val) && this.tags.length < 15) {
                this.tags.push(val);
            }
            this.inputVal = '';
        },

        addSuggestion(tag) {
            if (!this.tags.includes(tag) && this.tags.length < 15) {
                this.tags.push(tag);
            }
            this.$refs.tagInput.focus();
        },

        removeTag(index) {
            if (index >= 0) this.tags.splice(index, 1);
        }
    }
}
</script>
@endpush