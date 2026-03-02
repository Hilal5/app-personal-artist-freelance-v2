@extends('layouts.app')
@section('title', 'Manage FAQ — ArtSpace')

@push('styles')
<style>
    .faq-row { border-radius: 14px; border: 1px solid; padding: 16px; margin-bottom: 8px; transition: all 0.2s; }
    .dark .faq-row { background: #2a2a3d; border-color: #3a3a50; }
    html:not(.dark) .faq-row { background: white; border-color: #e5e7eb; }
    .form-input { width:100%; padding:10px 14px; border-radius:12px; font-size:0.8rem; outline:none; border:1px solid; transition:all 0.2s; }
    .dark .form-input { background:#21212e; border-color:#3a3a50; color:white; }
    html:not(.dark) .form-input { background:#f9fafb; border-color:#e5e7eb; color:#21212e; }
    .dark .form-input:focus { border-color:#f97316; }
    html:not(.dark) .form-input:focus { border-color:#21212e; }
</style>
@endpush

@section('content')
<div x-data="manageFaq()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold" :class="isDark ? 'text-white' : 'text-[#21212e]'">Manage FAQ</h1>
            <p class="text-xs mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-500'">Kelola pertanyaan yang sering ditanyakan</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('faq.index') }}"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all"
                :class="isDark ? 'bg-[#2a2a3d] text-gray-300' : 'bg-gray-100 text-gray-600'">
                <i data-lucide="eye" class="w-4 h-4"></i> Preview
            </a>
            <button @click="addModal = true"
                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah FAQ
            </button>
        </div>
    </div>

    {{-- FAQ grouped --}}
    @foreach($faqs as $category => $items)
    <div class="mb-6">
        <h2 class="font-bold text-sm mb-3 flex items-center gap-2"
            :class="isDark ? 'text-white' : 'text-[#21212e]'">
            <span class="w-1 h-4 bg-orange-500 rounded-full"></span>
            {{ $category }}
            <span class="text-xs font-normal px-2 py-0.5 rounded-full"
                :class="isDark ? 'bg-[#3a3a50] text-gray-400' : 'bg-gray-100 text-gray-500'">
                {{ $items->count() }}
            </span>
        </h2>

        @foreach($items as $faq)
        <div class="faq-row" id="faqrow-{{ $faq->id }}"
            :class="!isDark ? 'bg-white' : ''">
            <div class="flex items-start gap-3">
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-sm mb-1" :class="isDark ? 'text-white' : 'text-[#21212e]'">
                        {{ $faq->question }}
                    </p>
                    <p class="text-xs leading-relaxed line-clamp-2" :class="isDark ? 'text-gray-400' : 'text-gray-500'">
                        {{ $faq->answer }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    {{-- Toggle active --}}
                    <button onclick="toggleFaq({{ $faq->id }}, this)"
                        data-active="{{ $faq->is_active ? '1' : '0' }}"
                        class="text-xs px-2.5 py-1 rounded-full font-bold transition-all"
                        style="{{ $faq->is_active
                            ? 'background:rgba(34,197,94,0.12);color:#22c55e;'
                            : 'background:rgba(107,114,128,0.12);color:#6b7280;' }}">
                        {{ $faq->is_active ? 'Aktif' : 'Nonaktif' }}
                    </button>
                    {{-- Edit --}}
                    <button onclick="openEdit({{ $faq->id }}, '{{ addslashes($faq->category) }}', '{{ addslashes($faq->question) }}', '{{ addslashes($faq->answer) }}')"
                        class="w-7 h-7 rounded-lg flex items-center justify-center transition-all"
                        :class="isDark ? 'text-orange-400 hover:bg-orange-500/10' : 'text-orange-500 hover:bg-orange-50'">
                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                    </button>
                    {{-- Hapus --}}
                    <button onclick="deleteFaq({{ $faq->id }})"
                        class="w-7 h-7 rounded-lg flex items-center justify-center transition-all"
                        :class="isDark ? 'text-red-400 hover:bg-red-500/10' : 'text-red-500 hover:bg-red-50'">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach

    @if($faqs->isEmpty())
    <div class="flex flex-col items-center justify-center py-20">
        <div class="w-16 h-16 rounded-2xl bg-orange-500/10 flex items-center justify-center mb-4">
            <i data-lucide="help-circle" class="w-8 h-8 text-orange-500"></i>
        </div>
        <p class="font-bold mb-4" :class="isDark ? 'text-white' : 'text-[#21212e]'">Belum ada FAQ</p>
        <button @click="addModal = true"
            class="px-6 py-2.5 rounded-xl bg-orange-500 text-white font-bold text-sm hover:bg-orange-600 transition-all">
            Tambah Sekarang
        </button>
    </div>
    @endif

    {{-- Modal Tambah --}}
    <div x-show="addModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display:none;">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="addModal = false"></div>
        <div class="relative w-full max-w-lg rounded-2xl z-10"
            :class="isDark ? 'bg-[#2a2a3d] border border-[#3a3a50]' : 'bg-white border border-gray-200'">
            <div class="flex items-center justify-between p-5 border-b"
                :class="isDark ? 'border-[#3a3a50]' : 'border-gray-100'">
                <h3 class="font-bold text-sm" :class="isDark ? 'text-white' : 'text-[#21212e]'">Tambah FAQ</h3>
                <button @click="addModal = false" class="w-7 h-7 rounded-lg flex items-center justify-center"
                    :class="isDark ? 'hover:bg-[#21212e] text-gray-400' : 'hover:bg-gray-100 text-gray-400'">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="p-5 flex flex-col gap-4">
                <div>
                    <label class="text-xs font-bold mb-1.5 block" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Kategori</label>
                    <input type="text" x-model="form.category" list="catList"
                        placeholder="Pilih atau ketik kategori baru"
                        class="form-input" :class="isDark ? 'placeholder-gray-600' : 'placeholder-gray-400'">
                    <datalist id="catList">
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="text-xs font-bold mb-1.5 block" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Pertanyaan</label>
                    <input type="text" x-model="form.question" placeholder="Tulis pertanyaan..."
                        class="form-input" :class="isDark ? 'placeholder-gray-600' : 'placeholder-gray-400'">
                </div>
                <div>
                    <label class="text-xs font-bold mb-1.5 block" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Jawaban</label>
                    <textarea x-model="form.answer" rows="4" placeholder="Tulis jawaban lengkap..."
                        class="form-input resize-none" :class="isDark ? 'placeholder-gray-600' : 'placeholder-gray-400'"></textarea>
                </div>
                <p class="text-xs text-red-400 font-bold" x-show="formError" x-text="formError"></p>
            </div>
            <div class="flex gap-3 p-5 pt-0">
                <button @click="addModal = false"
                    class="flex-1 py-2.5 rounded-xl text-sm font-bold"
                    :class="isDark ? 'bg-[#21212e] text-gray-300' : 'bg-gray-100 text-gray-600'">Batal</button>
                <button @click="submitAdd()"
                    class="flex-1 py-2.5 rounded-xl bg-orange-500 text-white font-bold text-sm hover:bg-orange-600 transition-all">
                    Simpan
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div x-show="editModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display:none;">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="editModal = false"></div>
        <div class="relative w-full max-w-lg rounded-2xl z-10"
            :class="isDark ? 'bg-[#2a2a3d] border border-[#3a3a50]' : 'bg-white border border-gray-200'">
            <div class="flex items-center justify-between p-5 border-b"
                :class="isDark ? 'border-[#3a3a50]' : 'border-gray-100'">
                <h3 class="font-bold text-sm" :class="isDark ? 'text-white' : 'text-[#21212e]'">Edit FAQ</h3>
                <button @click="editModal = false" class="w-7 h-7 rounded-lg flex items-center justify-center"
                    :class="isDark ? 'hover:bg-[#21212e] text-gray-400' : 'hover:bg-gray-100 text-gray-400'">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="p-5 flex flex-col gap-4">
                <div>
                    <label class="text-xs font-bold mb-1.5 block" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Kategori</label>
                    <input type="text" x-model="editForm.category" list="catList"
                        class="form-input">
                </div>
                <div>
                    <label class="text-xs font-bold mb-1.5 block" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Pertanyaan</label>
                    <input type="text" x-model="editForm.question" class="form-input">
                </div>
                <div>
                    <label class="text-xs font-bold mb-1.5 block" :class="isDark ? 'text-gray-300' : 'text-[#21212e]'">Jawaban</label>
                    <textarea x-model="editForm.answer" rows="4" class="form-input resize-none"></textarea>
                </div>
                <p class="text-xs text-red-400 font-bold" x-show="editError" x-text="editError"></p>
            </div>
            <div class="flex gap-3 p-5 pt-0">
                <button @click="editModal = false"
                    class="flex-1 py-2.5 rounded-xl text-sm font-bold"
                    :class="isDark ? 'bg-[#21212e] text-gray-300' : 'bg-gray-100 text-gray-600'">Batal</button>
                <button @click="submitEdit()"
                    class="flex-1 py-2.5 rounded-xl bg-orange-500 text-white font-bold text-sm hover:bg-orange-600 transition-all">
                    Update
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]')?.content;

async function toggleFaq(id, btn) {
    const res  = await fetch(`/faq/${id}/toggle`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
    const data = await res.json();
    if (data.success) {
        btn.dataset.active = data.is_active ? '1' : '0';
        if (data.is_active) {
            btn.textContent    = 'Aktif';
            btn.style.background = 'rgba(34,197,94,0.12)';
            btn.style.color      = '#22c55e';
        } else {
            btn.textContent    = 'Nonaktif';
            btn.style.background = 'rgba(107,114,128,0.12)';
            btn.style.color      = '#6b7280';
        }
    }
}

async function deleteFaq(id) {
    if (!confirm('Hapus FAQ ini?')) return;
    const res  = await fetch(`/faq/${id}/delete`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
    const data = await res.json();
    if (data.success) {
        const el = document.getElementById('faqrow-' + id);
        el.style.opacity   = '0';
        el.style.transform = 'scale(0.97)';
        el.style.transition = 'all 0.25s';
        setTimeout(() => el.remove(), 250);
    }
}

function openEdit(id, category, question, answer) {
    const page = Alpine.$data(document.querySelector('[x-data="manageFaq()"]'));
    page.editForm  = { id, category, question, answer };
    page.editModal = true;
}

function manageFaq() {
    return {
        isDark: false,
        addModal: false,
        editModal: false,
        form: { category: '', question: '', answer: '' },
        editForm: { id: null, category: '', question: '', answer: '' },
        formError: '',
        editError: '',

        async submitAdd() {
            this.formError = '';
            if (!this.form.category || !this.form.question || !this.form.answer) {
                this.formError = 'Semua field wajib diisi.';
                return;
            }
            const res  = await fetch('/faq', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
                body: JSON.stringify(this.form),
            });
            const data = await res.json();
            if (data.success) {
                this.addModal = false;
                this.form     = { category: '', question: '', answer: '' };
                window.location.reload();
            }
        },

        async submitEdit() {
            this.editError = '';
            if (!this.editForm.category || !this.editForm.question || !this.editForm.answer) {
                this.editError = 'Semua field wajib diisi.';
                return;
            }
            const res  = await fetch(`/faq/${this.editForm.id}/update`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
                body: JSON.stringify(this.editForm),
            });
            const data = await res.json();
            if (data.success) {
                this.editModal = false;
                window.location.reload();
            }
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